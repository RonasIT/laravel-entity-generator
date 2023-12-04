<?php

namespace RonasIT\Support\Generators;

use Exception;
use Illuminate\Database\Eloquent\Factory as LegacyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Exceptions\CircularRelationsFoundedException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;
use DateTime;

class TestsGenerator extends EntityGenerator
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
        $this->createTests();
    }

    protected function createDump(): void
    {
        $content = $this->getStub('dump', [
            'inserts' => $this->getInserts()
        ]);
        $createMessage = "Created a new Test dump on path: {$this->paths['tests']}/fixtures/{$this->getTestClassName()}/dump.sql";

        mkdir_recursively($this->getFixturesPath());

        file_put_contents($this->getFixturesPath('dump.sql'), $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function createTests(): void
    {
        $this->generateExistedEntityFixture();
        $this->generateTest();
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
        return $this->isLegacyFactoryExists($modelName) || $this->isNewStyleFactoryExists($modelName);
    }

    protected function isLegacyFactoryExists($modelName): bool
    {
        $legacyFactory = app(LegacyFactory::class);
        $modelClass = $this->getModelClass($modelName);

        return !empty($legacyFactory[$modelClass]);
    }

    protected function isNewStyleFactoryExists($modelName): bool
    {
        $modelClass = $this->getModelClass($modelName);

        return $this->classExists('factory', "{$modelName}Factory")
            && method_exists($modelClass, 'factory')
            && class_exists(Factory::resolveFactoryName($modelClass));
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
        $modelNamespace = $this->getOrCreateNamespace('models');

        return "{$modelNamespace}\\{$model}";
    }

    protected function getModelFields($model): array
    {
        $modelClass = $this->getModelClass($model);

        return $this->filterBadModelField($modelClass::getFields());
    }

    protected function getMockModel($model): array
    {
        $modelClass = $this->getModelClass($model);

        if ($this->isNewStyleFactoryExists($model)) {
            $factory = $modelClass::factory();
        } else if ($this->isLegacyFactoryExists($model)) {
            $factory = factory($modelClass);
        } else {
            throw new Exception('You should set up legacy or new model class factory.');
        }

        return $factory
            ->make()
            ->toArray();
    }

    public function getFixturesPath($fileName = null): string
    {
        $path = base_path("{$this->paths['tests']}/fixtures/{$this->getTestClassName()}");

        if (empty($fileName)) {
            return $path;
        }

        return "{$path}/{$fileName}";
    }

    public function getTestClassName(): string
    {
        return "{$this->model}Test";
    }

    protected function generateExistedEntityFixture(): void
    {
        $object = $this->getFixtureValuesList($this->model);
        $entity = Str::snake($this->model);

        foreach (self::FIXTURE_TYPES as $type => $modifications) {
            if ($this->isFixtureNeeded($type)) {
                foreach ($modifications as $modification) {
                    $excepts = [];
                    if ($modification === 'request') {
                        $excepts = ['id'];
                    }
                    $this->generateFixture("{$type}_{$entity}_{$modification}.json", Arr::except($object, $excepts));
                }
            }
        }
    }

    protected function isFixtureNeeded($type): bool
    {
        $firstLetter = strtoupper($type[0]);

        return in_array($firstLetter, $this->crudOptions);
    }

    protected function generateFixture($fixtureName, $data): void
    {
        $fixturePath = $this->getFixturesPath($fixtureName);
        $content = json_encode($data, JSON_PRETTY_PRINT);
        $fixtureRelativePath = "{$this->paths['tests']}/fixtures/{$this->getTestClassName()}/{$fixtureName}";
        $createMessage = "Created a new Test fixture on path: {$fixtureRelativePath}";

        file_put_contents($fixturePath, $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function generateTest(): void
    {
        $content = $this->getStub('test', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'modelsNamespace' => $this->getOrCreateNamespace('models')
        ]);

        $testName = $this->getTestClassName();
        $createMessage = "Created a new Test: {$testName}";

        $this->saveClass('tests', $testName, $content);

        event(new SuccessCreateMessage($createMessage));
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

    protected function getRelatedModels($model): array
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

    private function filterBadModelField($fields): array
    {
        return array_diff($fields, [
            self::EMPTY_GUARDED_FIELD,
            self::CREATED_AT,
            self::UPDATED_AT
        ]);
    }
}
