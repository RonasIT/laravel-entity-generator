<?php

namespace RonasIT\EntityGenerator\Support\Fields;

use Illuminate\Support\Arr;
use RonasIT\EntityGenerator\Enums\FieldModifierEnum;
use RonasIT\EntityGenerator\Enums\FieldTypeEnum;
use RonasIT\EntityGenerator\Enums\ReservedFieldEnum;
use RonasIT\EntityGenerator\Exceptions\ReservedFieldException;
use RonasIT\EntityGenerator\Exceptions\UnknownFieldModifierException;

final class FieldsParser
{
    public function parse(array $options): FieldsCollection
    {
        $result = new FieldsCollection();

        foreach ($options as $type => $fields) {
            foreach ($fields as $field) {
                $result->add($this->createField($field, $type));
            }
        }

        return $result;
    }

    protected function createField(string $field, string $type): Field
    {
        list($name, $modifiers) = $this->splitField($field);

        if (ReservedFieldEnum::tryFrom($name) !== null) {
            throw new ReservedFieldException($name);
        }

        return new Field($name, FieldTypeEnum::from($type), ...$this->prepareModifiers($modifiers, $name));
    }

    protected function splitField(string $field): array
    {
        $parts = explode(':', $field);

        $fieldName = array_shift($parts);

        return [$fieldName, Arr::first($parts)];
    }

    protected function prepareModifiers(?string $modifiers, string $fieldName): array
    {
        if (empty($modifiers)) {
            return [];
        }

        $modifiers = explode(',', $modifiers);

        return Arr::map($modifiers, fn (string $modifier) => $this->prepareModifier($modifier, $fieldName));
    }

    protected function prepareModifier(string $modifier, string $fieldName): FieldModifierEnum
    {
        $modifierEnum = FieldModifierEnum::tryFromAlias($modifier) ?? FieldModifierEnum::tryFrom($modifier);

        if (is_null($modifierEnum)) {
            throw new UnknownFieldModifierException($modifier, $fieldName);
        }

        return $modifierEnum;
    }
}
