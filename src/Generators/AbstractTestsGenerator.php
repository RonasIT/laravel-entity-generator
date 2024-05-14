<?php

namespace RonasIT\Support\Generators;

use DateTime;
use Illuminate\Database\Eloquent\Factory as LegacyFactories;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\CircularRelationsFoundedException;
use RonasIT\Support\Exceptions\ClassNotExistsException;

abstract class AbstractTestsGenerator extends EntityGenerator
{
    protected $fakerProperties = [];
    protected $getFields = [];
    protected $withAuth = false;

    const FIXTURE_TYPES = [
        'create' => ['request', 'response'],
        'update' => ['request'],
    ];

    const EMPTY_GUARDED_FIELD = '*';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    public function generate(): void
    {
        $this->createDump();
        $this->generateFixtures();
        $this->generateTests();
    }

    protected function getFixturesPath($fileName = null): string
    {
        $path = base_path("{$this->paths['tests']}/fixtures/{$this->getTestClassName()}");

        if (empty($fileName)) {
            return $path;
        }

        return "{$path}/{$fileName}";
    }

    protected function createDump(): void
    {
        $content = $this->getStub('dump', [
            'inserts' => $this->getInserts()
        ]);

        $fixturePath = $this->getFixturesPath();

        if (!file_exists($fixturePath)) {
            mkdir_recursively($fixturePath);
        }

        $dumpName = $this->getDumpName();

        file_put_contents($this->getFixturesPath($dumpName), $content);

        event(new SuccessCreateMessage("Created a new Test dump on path: "
            . "{$this->paths['tests']}/fixtures/{$this->getTestClassName()}/{$dumpName}"));
    }

    protected function getDumpName(): string
    {
        return 'dump.sql';
    }

    protected function getInserts(): array
    {
        $arrayModels = [$this->model];

        if ($this->canGenerateUserData()) {
            array_unshift($arrayModels, 'User');
            $this->withAuth = true;
        }

        return array_map(function ($model) {
            return [
                'name' => $this->getTableName($model),
                'items' => [
                    [
                        'fields' => $this->getModelFields($model),
                        'values' => $this->getDumpValuesList($model)
                    ]
                ]
            ];
        }, $this->buildRelationsTree($arrayModels));
    }

    protected function isFactoryExists($modelName): bool
    {
        $factory = app(LegacyFactories::class);
        $modelClass = $this->getModelClass($modelName);

        $isNewStyleFactoryExists = $this->classExists('factory', "{$modelName}Factory") && method_exists($modelClass, 'factory');

        return $isNewStyleFactoryExists || !empty($factory[$this->getModelClass($modelName)]);
    }

    protected function isMethodExists($modelName, $method): bool
    {
        $modelClass = $this->getModelClass($modelName);

        return method_exists($modelClass, $method);
    }

    protected function getModelsWithFactories($models): array
    {
        return array_filter($models, function ($model) {
            return $this->isFactoryExists($model);
        });
    }

    protected function getDumpValuesList($model): array
    {
        $values = $this->buildEntityObject($model);

        array_walk($values, function (&$value) {
            if ($value instanceof DateTime) {
                $value = "'{$value->format('Y-m-d h:i:s')}'";
            } elseif (is_bool($value)) {
                $value = ($value) ? 'true' : 'false';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            }

            $value = (is_string($value)) ? "'{$value}'" : $value;
        });

        return $values;
    }

    protected function getFixtureValuesList($model): array
    {
        $values = $this->buildEntityObject($model);

        array_walk($values, function (&$value) {
            if ($value instanceof DateTime) {
                $value = "{$value->format('Y-m-d h:i:s')}";
            }
        });

        return $values;
    }

    protected function buildEntityObject($model): array
    {
        $modelFields = $this->getModelFields($model);
        $mockEntity = $this->getMockModel($model);

        $result = [];

        foreach ($modelFields as $field) {
            $value = Arr::get($mockEntity, $field, 1);

            $result[$field] = $value;
        }

        return $result;
    }

    protected function getModelClass($model): string
    {
        return "App\\Models\\{$model}";
    }

    protected function getModelFields($model): array
    {
        $modelClass = $this->getModelClass($model);

        return $this->filterBadModelField($modelClass::getFields());
    }

    protected function getMockModel($model): array
    {
        if (!$this->isFactoryExists($model)) {
            return [];
        }

        $modelClass = $this->getModelClass($model);
        $hasFactory = method_exists($modelClass, 'factory') && $this->classExists('factory', "{$model}Factory");
        $factory = ($hasFactory) ? $modelClass::factory() : factory($modelClass);

        return $factory
            ->make()
            ->toArray();
    }

    protected function generateFixtures(): void
    {
        $object = $this->getFixtureValuesList($this->model);
        $entity = Str::snake($this->model);

        foreach (self::FIXTURE_TYPES as $type => $modifications) {
            if ($this->isFixtureNeeded($type)) {
                foreach ($modifications as $modification) {
                    $excepts = ($modification === 'request') ? ['id'] : [];

                    $this->generateFixture("{$type}_{$entity}_{$modification}.json", Arr::except($object, $excepts));
                }
            }
        }
    }

    protected function generateFixture($fixtureName, $data): void
    {
        $fixturePath = $this->getFixturesPath($fixtureName);
        $content = json_encode($data, JSON_PRETTY_PRINT);
        $fixtureRelativePath = "{$this->paths['tests']}/fixtures/{$this->getTestClassName()}/{$fixtureName}";

        file_put_contents($fixturePath, $content);

        event(new SuccessCreateMessage("Created a new Test fixture on path: {$fixtureRelativePath}"));
    }

    protected function buildRelationsTree($models): array
    {
        foreach ($models as $model) {
            $relations = $this->getRelatedModels($model);
            $relationsWithFactories = $this->getModelsWithFactories($relations);

            if (empty($relationsWithFactories)) {
                continue;
            }

            if (in_array($model, $relationsWithFactories)) {
                $this->throwFailureException(
                    CircularRelationsFoundedException::class,
                    'Circular relations founded.',
                    'Please resolve you relations in models, factories and database.'
                );
            }

            $relatedModels = $this->buildRelationsTree($relationsWithFactories);

            $models = array_merge($relatedModels, $models);
        }

        return array_unique($models);
    }

    protected function getRelatedModels($model)
    {
        $content = $this->getModelClassContent($model);

        preg_match_all('/(?<=belongsTo\().*(?=::class)/', $content, $matches);

        return head($matches);
    }

    protected function getModelClassContent($model): string
    {
        $path = base_path("{$this->paths['models']}/{$model}.php");

        if (!$this->classExists('models', $model)) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$model} Model cause {$model} Model does not exists.",
                "Create a {$model} Model by himself or run command 'php artisan make:entity {$model} --only-model'."
            );
        }

        return file_get_contents($path);
    }

    protected function canGenerateUserData(): bool
    {
        return $this->classExists('models', 'User')
            && $this->isFactoryExists('User')
            && $this->isMethodExists('User', 'getFields');
    }

    abstract protected function getTestClassName(): string;

    abstract protected function isFixtureNeeded($type): bool;

    abstract protected function generateTests(): void;

    private function filterBadModelField($fields): array
    {
        return array_diff($fields, [
            self::EMPTY_GUARDED_FIELD,
            self::CREATED_AT,
            self::UPDATED_AT
        ]);
    }
}
