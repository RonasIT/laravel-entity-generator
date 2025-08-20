<?php

namespace RonasIT\Support\Generators;

use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\CircularRelationsFoundedException;

abstract class AbstractTestsGenerator extends EntityGenerator
{
    protected array $fakerProperties = [];
    protected array $getFields = [];
    protected bool $withAuth = false;

    const array FIXTURE_TYPES = [
        'create' => ['request', 'response'],
        'update' => ['request'],
    ];

    const string EMPTY_GUARDED_FIELD = '*';
    const string UPDATED_AT = 'updated_at';
    const string CREATED_AT = 'created_at';

    public function generate(): void
    {
        if ($this->canGenerateUserData()) {
            $this->withAuth = true;
        }

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
        if (!$this->isStubExists('dump')) {
            return;
        }

        $content = $this->getStub('dump', [
            'inserts' => $this->getInserts()
        ]);

        $this->createFixtureFolder();

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

        if ($this->withAuth) {
            array_unshift($arrayModels, 'User');
        }

        return array_map(function ($model) {
            return [
                'name' => $this->getTableName($model),
                'items' => [
                    [
                        'fields' => $this->getModelFields($model, $this->isEntity($model)),
                        'values' => $this->getDumpValuesList($model)
                    ]
                ]
            ];
        }, $this->buildRelationsTree($arrayModels));
    }

    protected function isFactoryExists(string $modelName): bool
    {
        return $this->classExists('factories', "{$modelName}Factory");
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
                $value = "{$value->format('Y-m-d h:i:s')}";
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
        $modelFields = $this->getModelFields($model, $this->isEntity($model));
        $mockEntity = $this->getMockModel($model);

        $result = [];

        foreach ($modelFields as $field) {
            $value = Arr::get($mockEntity, $field, 1);

            $result[$field] = $value;
        }

        return $result;
    }

    protected function getModelFields(string $model, string $configPath = 'models'): array
    {
        $modelClass = $this->getModelClass($model, $configPath);

        return $this->filterBadModelField($modelClass::getFields());
    }

    protected function getMockModel($model): array
    {
        $hasFactory = $this->isFactoryExists($model);

        if (!$hasFactory) {
            return [];
        }

        $factoryNamespace = "{$this->getOrCreateNamespace('factories')}\\{$model}Factory";
        $factory = $factoryNamespace::new();

        return $factory
            ->make()
            ->toArray();
    }

    protected function generateFixtures(): void
    {
        $object = $this->getFixtureValuesList($this->model);
        $entity = Str::snake($this->model);

        $this->createFixtureFolder();

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
            $relations = $this->getRelatedModels($model, $this->getTestClassName(), $this->isEntity($model));
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

    protected function canGenerateUserData(): bool
    {
        return $this->classExists('models', 'User')
            && $this->isMethodExists('User', 'getFields');
    }

    protected function createFixtureFolder(): void
    {
        $fixturePath = $this->getFixturesPath();

        if (!file_exists($fixturePath)) {
            mkdir($fixturePath, 0777, true);
        }
    }

    abstract protected function getTestClassName(): string;

    abstract protected function isFixtureNeeded($type): bool;

    abstract protected function generateTests(): void;

    private function filterBadModelField($fields): array
    {
        return array_diff($fields, [
            self::EMPTY_GUARDED_FIELD,
            self::CREATED_AT,
            self::UPDATED_AT,
        ]);
    }

    private function isEntity(string $model): string
    {
        return $model === $this->model ? 'model_entity' : 'models';
    }
}
