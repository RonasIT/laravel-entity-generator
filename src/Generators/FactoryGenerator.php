<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use RonasIT\Support\Exceptions\CircularRelationsFoundedException;
use RonasIT\Support\Exceptions\ModelFactoryNotFound;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ModelFactoryNotFoundedException;
use RonasIT\Support\Events\SuccessCreateMessage;
use Exception;

class FactoryGenerator extends EntityGenerator
{
    public function generate()
    {
        if (!$this->checkExistModelFactory() && $this->checkExistRelatedModelsFactories()) {
            $stubPath = config("entity-generator.stubs.factory");

            $content = view($stubPath)->with([
                'entity' => $this->model,
                'fields' => $this->prepareFields()
            ])->render();

            $content = "\n\n" . $content;

            $createMessage = "Created a new Test factory for {$this->model} model in '{$this->paths['factory']}'";

            file_put_contents($this->paths['factory'], $content, FILE_APPEND);

            $this->prepareRelatedFactories();
        } else {
            $createMessage = "Factory for {$this->model} model has already created, so new factory not necessary create.";
        }

        event(new SuccessCreateMessage($createMessage));
    }

    protected function checkExistRelatedModelsFactories()
    {
        $modelFactoryContent = file_get_contents($this->paths['factory']);
        $relatedModels = $this->getRelatedModels($this->model);

        foreach ($relatedModels as $relatedModel) {
            $relatedFactoryClass = "App\\Models\\$relatedModel::class";
            $existModelFactory = strpos($modelFactoryContent, $relatedFactoryClass);

            if (!$existModelFactory) {
                $this->throwFailureException(
                    ModelFactoryNotFoundedException::class,
                    "Not found $relatedModel factory for $relatedModel model in '{$this->paths['factory']}",
                    "Please declare a factory for $relatedModel model on '{$this->paths['factory']}' path and run your command with option '--only-tests'."
                );
            }
        }

        return true;
    }

    protected static function getFakerMethod($field) {

        $fakerMethods = [
            'integer' => 'randomNumber()',
            'boolean' => 'boolean',
            'string' => 'word',
            'float' => 'randomFloat()',
            'timestamp' => 'dateTime',
        ];

        if (array_has($fakerMethods, $field['type'])) {
            return "\$faker->{$fakerMethods[$field['type']]}";
        }

        return self::getCustomMethod($field);
    }

    protected static function getCustomMethod($field)
    {
        $customMethods = [
            'json' => '[]'
        ];

        if (array_has($customMethods, $field['type'])) {
            return $customMethods[$field['type']];
        }

        throw new Exception("{$field['type']} not found in customMethods variable customMethods = {{$customMethods}}");
    }

    protected function prepareRelatedFactories()
    {
        $relations = array_merge(
            $this->relations['hasOne'],
            $this->relations['hasMany']
        );

        foreach ($relations as $relation) {
            $modelFactoryContent = file_get_contents($this->paths['factory']);

            if (!str_contains($modelFactoryContent, $this->getModelClass($relation))) {
                $this->throwFailureException(
                    ModelFactoryNotFound::class,
                    "Model factory for mode {$relation} not found.",
                    "Please create it and after thar you can run this command with flag '--only-tests'."
                );
            }

            $matches = [];

            preg_match($this->getFactoryPattern($relation), $modelFactoryContent, $matches);

            foreach ($matches as $match) {
                $field = snake_case($this->model) . '_id';

                $newField = "\n        \"{$field}\" => 1,";

                $modelFactoryContent = str_replace($match, $match . $newField, $modelFactoryContent);
            }

            file_put_contents($this->paths['factory'], $modelFactoryContent);
        }
    }

    public static function getFactoryFieldsContent($field)
    {
        /** @var Faker $faker */
        $faker = app(Faker::class);

        if (preg_match('/_id$/', $field['name']) || ($field['name'] == 'id')) {
            return 1;
        }

        if (property_exists($faker, $field['name'])) {
            return "\$faker-\>{$field['name']}";
        }

        if (method_exists($faker, $field['name'])) {
            return "\$faker-\>{$field['name']}()";
        }

        return self::getFakerMethod($field);
    }

    protected function checkExistModelFactory()
    {
        $modelFactoryContent = file_get_contents($this->paths['factory']);
        $factoryClass = "App\\Models\\$this->model::class";

        return strpos($modelFactoryContent, $factoryClass);
    }

    protected function prepareFields()
    {
        $result = [];

        foreach ($this->fields as $type => $fields) {
            foreach ($fields as $field) {
                $explodedType = explode('-', $type);

                $result[] = [
                    'name' => $field,
                    'type' => head($explodedType)
                ];
            }
        }

        return $result;
    }

    protected function getFactoryPattern($model)
    {
        $modelNamespace = "App\\\\Models\\\\" . $model;
        $return = "return \\[";

        return "/{$modelNamespace}.*{$return}/sU";
    }

    protected function getModelClass($model)
    {
        return "App\\Models\\{$model}";
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