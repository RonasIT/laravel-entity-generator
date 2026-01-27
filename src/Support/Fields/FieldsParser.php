<?php

namespace RonasIT\Support\Support\Fields;

use Illuminate\Support\Arr;
use RonasIT\Support\DTO\FieldDTO;
use RonasIT\Support\DTO\FieldsDTO;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Exceptions\UnknownFieldModifierException;

final class FieldsParser
{
    public function parse(array $options): FieldsDTO
    {
        $result = [];

        foreach ($options as $type => $fields) {
            foreach ($fields as $field) {
                $result[$type][] = $this->createFieldDTO($field);
            }
        }

        return new FieldsDTO(...$result);
    }

    protected function createFieldDTO(string $field): FieldDTO
    {
        list($name, $modifiers) = $this->splitField($field);

        return new FieldDTO(
            name: $name,
            modifiers: $this->prepareModifiers($modifiers, $name),
        );
    }

    protected function splitField(string $field): array
    {
        $parts = explode(':', $field);

        return [$parts[0], $parts[1] ?? ''];
    }

    protected function prepareModifiers(string $modifiers, string $fieldName): array
    {
        if (empty($modifiers)) {
            return [];
        }

        $modifiers = explode(',', $modifiers);

        return Arr::map($modifiers, fn ($modifier) => $this->prepareModifier($modifier, $fieldName));
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
