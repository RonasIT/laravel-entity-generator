<?php

namespace RonasIT\EntityGenerator\Enums;

use RonasIT\EntityGenerator\Support\Fields\Field;
use RonasIT\Support\Traits\EnumTrait;

enum ReservedFieldEnum: string
{
    use EnumTrait;

    case Id = 'id';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';

    public function toField(): Field
    {
        return match ($this) {
            self::Id => new Field('id', FieldTypeEnum::Integer, FieldModifierEnum::Required),
            self::CreatedAt => new Field('created_at', FieldTypeEnum::Timestamp),
            self::UpdatedAt => new Field('updated_at', FieldTypeEnum::Timestamp),
        };
    }

}
