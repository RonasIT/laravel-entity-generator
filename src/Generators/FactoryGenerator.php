<?php

namespace RonasIT\Support\Generators;

use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\FakerMethodNotFoundException;

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
        $this->checkResourceNotExists('models', "{$this->model}Factory", $this->model, $this->modelSubFolder);

        $this->checkResourceExists('factories', "{$this->model}Factory");

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
            return '$faker->' . self::FAKERS_METHODS[$field['type']];
        }

        return self::getCustomMethod($field);
    }

    protected static function getCustomMethod($field): string
    {
        if (Arr::has(self::CUSTOM_METHODS, $field['type'])) {
            return self::CUSTOM_METHODS[$field['type']];
        }

        $message = "Cannot generate fake data for unsupported {$field['type']} field type. "
            . 'Supported custom field types are ' . implode(', ', array_keys(self::CUSTOM_METHODS));

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
                    'name' => $field['name'],
                    'type' => $type,
                ];
            }
        }

        return $result;
    }
}
