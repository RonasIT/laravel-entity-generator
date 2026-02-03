<?php

namespace RonasIT\Support\Support\Fields;

use Illuminate\Support\Arr;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Exceptions\UnknownFieldModifierException;

final class FieldsParser
{
    public function parse(array $options): FieldsCollection
    {
        $result = [];

        foreach ($options as $type => $fields) {
            foreach ($fields as $field) {
                $result[] = $this->createField($field, $type);
            }
        }

        return new FieldsCollection($result);
    }

    protected function createField(string $field, string $type): Field
    {
        list($name, $modifiers) = $this->splitField($field);

        return new Field(
            name: $name,
            type: FieldTypeEnum::from($type),
            modifiers: $this->prepareModifiers($modifiers, $name),
        );
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
