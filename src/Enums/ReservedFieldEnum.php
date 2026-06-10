<?php

namespace RonasIT\EntityGenerator\Enums;

use RonasIT\EntityGenerator\Support\Fields\Field;

enum ReservedFieldEnum: string
{
    case Id = 'id';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';

    public function toField(): Field
    {
        return match ($this) {
            self::Id => new Field('id', FieldTypeEnum::Integer),
            self::CreatedAt => new Field('created_at', FieldTypeEnum::Timestamp),
            self::UpdatedAt => new Field('updated_at', FieldTypeEnum::Timestamp),
        };
    }

    public static function isReserved(string $name): bool
    {
        return in_array(strtolower(trim($name)), array_column(self::cases(), 'value'));
    }
}