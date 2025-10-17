<?php

namespace RonasIT\Support\Generators;

use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RonasIT\Support\Exceptions\FakerMethodNotFoundException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

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

    public function generate(): void
    {
        if (!$this->classExists('models', $this->model, $this->modelSubFolder)) {
            // TODO: pass $this->modelSubfolder to Exception after refactoring in https://github.com/RonasIT/laravel-entity-generator/issues/179
            $this->throwFailureException(
                exceptionClass: ClassNotExistsException::class,
                failureMessage: "Cannot create {$this->model}Factory cause {$this->model} Model does not exists.",
                recommendedMessage: "Create a {$this->model} Model by itself or run command 'php artisan make:entity {$this->model} --only-model'.",
            );
        }

        if ($this->classExists('factories', "{$this->model}Factory")) {
            $this->throwFailureException(
                exceptionClass: ClassAlreadyExistsException::class,
                failureMessage: "Cannot create {$this->model}Factory cause {$this->model}Factory already exists.",
                recommendedMessage: "Remove {$this->model}Factory.",
            );
        }

        if (!$this->isStubExists('factory')) {
            return;
        }

        $this->createNamespace('factories');

        $factoryContent = $this->getStub('factory', [
            'namespace' => $this->generateNamespace($this->paths['factories']),
            'entity' => $this->model,
            'fields' => $this->prepareFields(),
            'modelNamespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
        ]);

        $this->saveClass('factories', "{$this->model}Factory", $factoryContent);

        event(new SuccessCreateMessage("Created a new Factory: {$this->model}Factory"));
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

        $message = "Cannot generate fake data for unsupported {$field['type']} field type. "
            . "Supported custom field types are " . implode(', ', array_keys(self::CUSTOM_METHODS));

        throw new FakerMethodNotFoundException($message);
    }

    public static function getFactoryFieldsContent($field): string
    {
        /** @var Faker $faker */
        $faker = app(Faker::class);

        if (preg_match('/_id$/', $field['name']) || ($field['name'] == 'id')) {
            return 1;
        }

        try {
            $faker->{$field['name']};
            $hasFormatter = true;
        } catch (InvalidArgumentException $e) {
            $hasFormatter = false;
        }

        if ($hasFormatter) {
            return "\$faker->{$field['name']}";
        }

        return self::getFakerMethod($field);
    }

    protected function prepareFields(): array
    {
        $result = [];

        foreach ($this->fields as $type => $fields) {
            foreach ($fields as $field) {
                $result[] = [
                    'name' => $field,
                    'type' => Str::before($type, '-'),
                ];
            }
        }

        return $result;
    }
}