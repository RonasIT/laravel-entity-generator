<?php

namespace RonasIT\Support\Generators;

use Faker\Generator as Faker;
use InvalidArgumentException;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\Fields\Field;

class FactoryGenerator extends EntityGenerator
{
    const array FAKERS_METHODS_MAP = [
        'integer' => 'randomNumber()',
        'boolean' => 'boolean',
        'string' => 'word',
        'float' => 'randomFloat(2, 0, 10000)',
        'timestamp' => 'dateTime',
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

    protected function getFakerMethod(Field $field): string
    {
        return '$faker->' . self::FAKERS_METHODS_MAP[$field->type->value];
    }

    public function getFakeValueGenerationLine(Field $field): string
    {
        /** @var Faker $faker */
        $faker = app(Faker::class);

        if ($field->isKeyField()) {
            return 1;
        }

        if ($field->isJSON()) {
            return '[]';
        }

        try {
            $faker->{$field->name};
            $hasFormatter = true;
        } catch (InvalidArgumentException $e) {
            $hasFormatter = false;
        }

        if ($hasFormatter) {
            return "\$faker->{$field->name}";
        }

        return $this->getFakerMethod($field);
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
