<?php

namespace RonasIT\EntityGenerator\Support\Fields;

use Illuminate\Support\Arr;
use RonasIT\EntityGenerator\Enums\FieldModifierEnum;
use RonasIT\EntityGenerator\Enums\FieldTypeEnum;

final readonly class Field
{
    public function __construct(
        public string $name,
        public FieldTypeEnum $type,
        public array $modifiers = [],
    ) {
    }

    public function removeModifier(FieldModifierEnum $removeModifier): self
    {
        return new self(
            name: $this->name,
            type: $this->type,
            modifiers: Arr::reject($this->modifiers, fn ($modifier) => $removeModifier === $modifier),
        );
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
