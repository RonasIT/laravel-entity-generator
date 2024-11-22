<?php

namespace RonasIT\Support\Generators;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RonasIT\Support\Exceptions\ModelFactoryNotFound;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ModelFactoryNotFoundedException;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class FactoryGenerator extends EntityGenerator
{
    const array FAKERS_METHODS = [
        'integer' => 'randomNumber()',
        'boolean' => 'boolean',
        'string' => 'word',
        'float' => 'randomFloat(2, 0, 10000)',
        'timestamp' => 'dateTime',
    ];

    const array CUSTOM_METHODS = [
        'json' => '[]',
    ];

    protected function generateSeparateClass(): string
    {
        if (!$this->classExists('models', $this->model)) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Factory cause {$this->model} Model does not exists.",
                "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'."
            );
        }

        if ($this->classExists('factory', "{$this->model}Factory")) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model}Factory cause {$this->model}Factory already exists.",
                "Remove {$this->model}Factory."
            );
        }

        $factoryContent = $this->getStub('factory', [
            'namespace' => $this->getOrCreateNamespace('factory'),
            'entity' => $this->model,
            'fields' => $this->prepareFields()
        ]);

        $this->saveClass('factory', "{$this->model}Factory", $factoryContent);

        return "Created a new Factory: {$this->model}Factory";
    }

    protected function generateToGenericClass(): string
    {
        if (!file_exists($this->paths['factory'])) {
            $this->prepareEmptyFactory();
        }

        if (!$this->checkExistModelFactory() && $this->checkExistRelatedModelsFactories()) {
            $stubPath = config("entity-generator.stubs.legacy_factory");

            $content = view($stubPath)->with([
                'entity' => $this->model,
                'fields' => $this->prepareFields(),
                'modelsNamespace' => $this->getOrCreateNamespace('models')
            ])->render();

            $content = "\n\n" . $content;

            $createMessage = "Created a new Test factory for {$this->model} model in '{$this->paths['factory']}'";

            file_put_contents($this->paths['factory'], $content, FILE_APPEND);

            $this->prepareRelatedFactories();
        } else {
            $createMessage = "Factory for {$this->model} model has already created, so new factory not necessary create.";
        }

        return $createMessage;
    }

    public function generate(): void
    {
        $createMessage = (version_compare(app()->version(), '8', '>='))
            ? $this->generateSeparateClass()
            : $this->generateToGenericClass();

        event(new SuccessCreateMessage($createMessage));
    }

    protected function prepareEmptyFactory(): void
    {
        $stubPath = config('entity-generator.stubs.legacy_empty_factory');
        $content = "<?php \n\n" . view($stubPath, [
            'modelsNamespace' => $this->getOrCreateNamespace('models')
        ])->render();

        list($basePath, $databaseFactoryDir) = extract_last_part(config('entity-generator.paths.factory'), '/');

        if (!is_dir($databaseFactoryDir)) {
            mkdir($databaseFactoryDir);
        }

        file_put_contents($this->paths['factory'], $content);
    }

    protected function checkExistRelatedModelsFactories(): bool
    {
        $modelFactoryContent = file_get_contents($this->paths['factory']);
        $relatedModels = $this->getRelatedModels($this->model);
        $modelNamespace = $this->getOrCreateNamespace('models');

        foreach ($relatedModels as $relatedModel) {
            $relatedFactoryClass = "{$modelNamespace}\\$relatedModel::class";
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

    protected static function getFakerMethod($field): string
    {
        if (Arr::has(self::FAKERS_METHODS, $field['type'])) {
            return "\$faker->" . self::FAKERS_METHODS[$field['type']];
        }

        return self::getCustomMethod($field);
    }

    protected static function getCustomMethod($field): string
    {
        if (Arr::has(self::CUSTOM_METHODS, $field['type'])) {
            return self::CUSTOM_METHODS[$field['type']];
        }

        $message = $field['type'] . 'not found in CUSTOM_METHODS variable CUSTOM_METHODS = ' . self::CUSTOM_METHODS;
        throw new Exception($message);
    }

    protected function prepareRelatedFactories(): void
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

    public static function getFactoryFieldsContent($field): string
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

    protected function checkExistModelFactory(): int
    {
        $modelFactoryContent = file_get_contents($this->paths['factory']);
        $modelNamespace = $this->getOrCreateNamespace('models');
        $factoryClass = "{$modelNamespace}\\$this->model::class";

        return strpos($modelFactoryContent, $factoryClass);
    }

    protected function prepareFields(): array
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

    protected function getFactoryPattern($model): string
    {
        $modelNamespace = "App\\\\Models\\\\" . $model;
        $return = "return \\[";

        return "/{$modelNamespace}.*{$return}/sU";
    }

    protected function getModelClass($model): string
    {
        $modelNamespace = $this->getOrCreateNamespace('models');

        return "{$modelNamespace}\\{$model}";
    }

    protected function getRelatedModels(string $model): array
    {
        $class = $this->getModelClass($model);

        if (!class_exists($class)) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$model} Model cause {$model} Model does not exists.",
                "Create a {$model} Model by himself or run command 'php artisan make:entity {$model} --only-model'."
            );
        }

        $instance = new $class();

        $publicMethods = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = array_filter($publicMethods, fn ($method) => $method->class === $class && !$method->getParameters());

        $relatedModels = [];

        DB::beginTransaction();

        foreach ($methods as $method) {
            try {
                $methodName = $method->getName();

                $methodReturn = $instance->$methodName();

                if (!$methodReturn instanceof BelongsTo) {
                    continue;
                }
            } catch (Throwable) {
                continue;
            }

            $relationModel = get_class($methodReturn->getRelated());

            $relatedModels[] = class_basename($relationModel);
        }

        DB::rollBack();

        return $relatedModels;
    }
}
