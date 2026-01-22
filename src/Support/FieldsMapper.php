<?php

namespace RonasIT\Support\Support;

use RonasIT\Support\Collections\FieldsCollection;
use RonasIT\Support\DTO\FieldsDTO;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\ValueObjects\Field;

class FieldsMapper
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
