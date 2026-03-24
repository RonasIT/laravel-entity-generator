<?php

namespace RonasIT\Support\Support\Fields;

use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;

final readonly class Field
{
    public function __construct(
        public string $name,
        public FieldTypeEnum $type,
        public array $modifiers = [],
    ) {
    }

    public function isRequired(): bool
    {
        return in_array(FieldModifierEnum::Required, $this->modifiers);
    }

    public function isJSON(): bool
    {
        return $this->type === FieldTypeEnum::Json;
    }

    public function isTimestamp(): bool
    {
        return $this->type === FieldTypeEnum::Timestamp;
    }

    public function isBoolean(): bool
    {
        return $this->type === FieldTypeEnum::Boolean;
    }

    public function isKeyField(): bool
    {
        return str_ends_with($this->name, '_id') || ($this->name === 'id');
    }
}
