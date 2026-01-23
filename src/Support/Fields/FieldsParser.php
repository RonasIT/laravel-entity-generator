<?php

namespace RonasIT\Support\Support\Fields;

use Illuminate\Support\Arr;
use RonasIT\Support\DTO\FieldDTO;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Exceptions\UnknownFieldModifierException;

class FieldsParser
{
    public function parse(array $options): array
    {
        $result = [];

        foreach ($options as $type => $fields) {
            foreach ($fields as $field) {
                $parts = explode(':', $field);

                $result[$type][] = new FieldDTO(
                    name: $parts[0],
                    modifiers: $this->prepareModifiers($parts[1] ?? '', $parts[0]),
                );
            }
        }

        return $result;
    }

    protected function convertModifiersShortOptions(array $modifiers): array
    {
        $modifiersMap = [
            'r' => FieldModifierEnum::Required->value,
        ];

        return Arr::map($modifiers, fn ($modifier) => $modifiersMap[$modifier] ?? $modifier);
    }

    protected function prepareModifiers(string $modifiers, string $fieldName): array
    {
        if (empty($modifiers)) {
            return [];
        }

        $modifiers = $this->convertModifiersShortOptions(explode(',', $modifiers));

        return Arr::map(
            array: $modifiers,
            callback: fn ($modifier) => FieldModifierEnum::tryFrom($modifier) ?? throw new UnknownFieldModifierException($modifier, $fieldName),
        );
    }
}
