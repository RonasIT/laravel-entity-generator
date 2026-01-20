<?php

namespace RonasIT\Support\Support;

use Illuminate\Support\Arr;
use RonasIT\Support\DTO\FieldDTO;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Exceptions\UnknownFieldTypeException;

class FieldsParser
{
    public function parse(array $options): array
    {
        $result = [];

        foreach ($options as $type => $fields) {
            if (is_null(FieldTypeEnum::tryFrom($type))) {
                throw new UnknownFieldTypeException($type, 'Entity Generator');
            }

            foreach ($fields as $field) {
                $parts = explode(':', $field);

                $result[$type][] = new FieldDTO(
                    name: $parts[0],
                    modifiers: $this->prepareModifiers($parts[1] ?? ''),
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

    protected function prepareModifiers(string $modifiers): array
    {
        if (empty($modifiers)) {
            return [];
        }

        $modifiers = $this->convertModifiersShortOptions(explode(',', $modifiers));

        return Arr::map($modifiers, fn ($modifier) => FieldModifierEnum::tryFrom($modifier) ?? $modifier);
    }
}