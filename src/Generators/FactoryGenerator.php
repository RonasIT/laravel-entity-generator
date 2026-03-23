<?php

namespace RonasIT\Support\Generators;

use Faker\Generator as Faker;
use InvalidArgumentException;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\Fields\Field;

class FactoryGenerator extends EntityGenerator
{
    const array FAKERS_METHODS_MAP = [
        FieldTypeEnum::Integer->value => 'randomNumber()',
        FieldTypeEnum::Boolean->value => 'boolean',
        FieldTypeEnum::String->value => 'word',
        FieldTypeEnum::Float->value => 'randomFloat(2, 0, 10000)',
        FieldTypeEnum::Timestamp->value => 'dateTime',
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

    protected function getFakerCallLine(Field $field): string
    {
        /** @var Faker $faker */
        $faker = app(Faker::class);

        // Try to find the special faker formatter like name, city, email, etc.
        try {
            $faker->{$field->name};

            return "\$faker->{$field->name}";
        } catch (InvalidArgumentException $e) {
            return '$faker->' . self::FAKERS_METHODS_MAP[$field->type->value];
        }
    }

    public function getFakeValueGenerationLine(Field $field): string
    {
        if ($field->isKeyField()) {
            return 1;
        }

        if ($field->isJSON()) {
            return '[]';
        }

        return $this->getFakerCallLine($field);
    }

    protected function prepareFields(): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            $result[$field->name] = $this->getFakeValueGenerationLine($field);
        }

        return $result;
    }
}
