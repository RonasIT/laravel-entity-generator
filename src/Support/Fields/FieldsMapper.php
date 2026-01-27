<?php

namespace RonasIT\Support\Support\Fields;

use RonasIT\Support\DTO\FieldsDTO;
use RonasIT\Support\Enums\FieldTypeEnum;

final class FieldsMapper
{
    public function mapDTOtoCollection(FieldsDTO $fields): FieldsCollection
    {
        $newFields = [];

        foreach ($fields as $fieldType => $typedFields) {
            foreach ($typedFields as $field) {
                $newFields[] = new Field(
                    name: $field->name,
                    type: FieldTypeEnum::from($fieldType),
                    modifiers: $field->modifiers,
                );
            }
        }

        return new FieldsCollection($newFields);
    }
}
