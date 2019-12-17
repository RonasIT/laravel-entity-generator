<?php

namespace RonasIT\Support\Generators;

use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Exceptions\ModelFactoryNotFound;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ModelFactoryNotFoundedException;
use RonasIT\Support\Events\SuccessCreateMessage;
use Exception;

class FactoryGenerator extends EntityGenerator
{
    const FAKERS_METHODS = [
        'integer' => 'randomNumber()',
        'boolean' => 'boolean',
        'string' => 'word',
        'float' => 'randomFloat()',
        'timestamp' => 'dateTime',
    ];

    const CUSTOM_METHODS = [
        'json' => '[]'
    ];

    public function generate()
    {
        if (!file_exists($this->paths['factory'])) {
            $this->prepareEmptyFactory();
        }

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

    protected function prepareEmptyFactory()
    {
        $stubPath = config('entity-generator.stubs.empty_factory');
        $content = "<?php \n\n" . view($stubPath)->render();
        file_put_contents($this->paths['factory'], $content);
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

    protected static function getFakerMethod($field)
    {
        if (Arr::has(self::FAKERS_METHODS, $field['type'])) {
            return "\$faker->" . self::FAKERS_METHODS[$field['type']];
        }

        return self::getCustomMethod($field);
    }

    protected static function getCustomMethod($field)
    {
        if (Arr::has(self::CUSTOM_METHODS, $field['type'])) {
            return self::CUSTOM_METHODS[$field['type']];
        }

        $message = $field['type'] . 'not found in CUSTOM_METHODS variable CUSTOM_METHODS = ' . self::CUSTOM_METHODS;
        throw new Exception($message);
    }

    protected function prepareRelatedFactories()
    {
        $relations = array_merge(
            $this->relations['hasOne'],
            $this->relations['hasMany']
        );

        foreach ($relations as $relation) {
            $modelFactoryContent = file_get_contents($this->paths['factory']);

            if (!Str::contains($modelFactoryContent, $this->getModelClass($relation))) {
                $this->throwFailureException(
                    ModelFactoryNotFound::class,
                    "Model factory for mode {$relation} not found.",
                    "Please create it and after thar you can run this command with flag '--only-tests'."
                );
            }

            $matches = [];

            preg_match($this->getFactoryPattern($relation), $modelFactoryContent, $matches);

            foreach ($matches as $match) {
                $field = Str::snake($this->model) . '_id';

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
