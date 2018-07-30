<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use RonasIT\Support\Exceptions\CircularRelationsFoundedException;
use RonasIT\Support\Exceptions\ModelFactoryNotFound;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ModelFactoryNotFoundedException;
use RonasIT\Support\Events\SuccessCreateMessage;

class TestsGenerator extends EntityGenerator
{
    protected $fakerProperties = [];
    protected $getFields = [];
    protected $withAuth = false;

    const FIXTURE_TYPES = ['create', 'update'];

    public function generate()
    {
        $this->createDump();
        $this->createTests();
    }

    protected function createDump()
    {
        $content = $this->getStub('dump', [
            'inserts' => $this->getInserts()
        ]);
        $createMessage = "Created a new Test dump on path: {$this->paths['tests']}/fixtures/{$this->getTestClassName()}/dump.sql";

        mkdir_recursively($this->getFixturesPath());

        file_put_contents($this->getFixturesPath('dump.sql'), $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function createTests()
    {
        $this->generateExistedEntityFixture();
        $this->generateTest();
    }

    protected function getInserts()
    {
        $arrayModels = [$this->model];

        if ($this->classExists('models', 'User')) {
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

    protected function getDumpValuesList($model)
    {
        $values = $this->buildEntityObject($model);

        return array_associate($values, function ($value, $key) {
            if ($value instanceof \DateTime) {
                return [
                    'key' => $key,
                    'value' => "'{$value->format('Y-m-d h:i:s')}'"
                ];
            }

            if (is_bool($value)) {
                return [
                    'key' => $key,
                    'value' => $value ? 'true' : 'false'
                ];
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            return [
                'key' => $key,
                'value' => is_string($value) ? "'{$value}'" : $value
            ];
        });
    }

    protected function getFixtureValuesList($model)
    {
        $values = $this->buildEntityObject($model);

        return array_associate($values, function ($value, $key) {
            if ($value instanceof \DateTime) {
                return [
                    'key' => $key,
                    'value' => "'{$value->format('Y-m-d h:i:s')}'"
                ];
            }

            return [
                'key' => $key,
                'value' => $value
            ];
        });
    }

    protected function buildEntityObject($model)
    {
        $modelFields = $this->getModelFields($model);
        $mockEntity = $this->getMockModel($model);

        $result = [];

        foreach ($modelFields as $field) {
            $value = array_get($mockEntity, $field, 1);

            $result[$field] = $value;
        }

        return $result;
    }

    protected function getModelClass($model)
    {
        return "App\\Models\\{$model}";
    }

    protected function getModelFields($model)
    {
        $modelClass = $this->getModelClass($model);

        return $modelClass::getFields();
    }

    protected function getMockModel($model)
    {
        $modelClass = $this->getModelClass($model);

        return factory($modelClass)
            ->make()
            ->toArray();
    }

    public function getFixturesPath($fileName = null)
    {
        $path = base_path("{$this->paths['tests']}/fixtures/{$this->getTestClassName()}");

        if (empty($fileName)) {
            return $path;
        }

        return "{$path}/{$fileName}";
    }

    public function getTestClassName()
    {
        return "{$this->model}Test";
    }

    protected function generateExistedEntityFixture()
    {
        $object = $this->getFixtureValuesList($this->model);
        $entity = snake_case($this->model);

        foreach (self::FIXTURE_TYPES as $type) {
            $this->generateFixture(
                "{$type}_{$entity}.json",
                $object
            );
        }
    }

    protected function generateFixture($fixtureName, $data)
    {
        $fixturePath = $this->getFixturesPath($fixtureName);
        $content = json_encode($data, JSON_PRETTY_PRINT);
        $fixtureRelativePath = "{$this->paths['tests']}/fixtures/{$this->getTestClassName()}/{$fixtureName}";
        $createMessage = "Created a new Test fixture on path: {$fixtureRelativePath}";

        file_put_contents($fixturePath, $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function generateTest()
    {
        $content = $this->getStub('test', [
            'entity' => $this->model,
            'entities' => $this->getTableName($this->model),
            'withAuth' => $this->withAuth
        ]);

        $testName = $this->getTestClassName();
        $createMessage = "Created a new Test: {$testName}";

        $this->saveClass('tests', $testName, $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function buildRelationsTree($models)
    {
        foreach ($models as $model) {
            $relations = $this->getRelatedModels($model);

            if (empty($relations)) {
                continue;
            }

            if (in_array($this->model, $relations)) {
                $this->throwFailureException(
                    CircularRelationsFoundedException::class,
                    "Circular relations founded.",
                    "Please resolve you relations in models, factories and database."
                );
            }

            $relatedModels = $this->buildRelationsTree($relations);

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

    protected function getModelClassContent($model)
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
}