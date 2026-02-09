<?php

namespace RonasIT\Support\Support\Fields;

use Illuminate\Support\Arr;
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

    public function replaceModifier(FieldModifierEnum $originalModifier, FieldModifierEnum|string $newModifier): self
    {
        return new self(
            name: $this->name,
            type: $this->type,
            modifiers: Arr::map(
                array: $this->modifiers,
                callback: fn ($modifier) => ($modifier === $originalModifier) ? $newModifier : $modifier,
            ),
        );
    }

    public function removeModifier(FieldModifierEnum $removeModifier): self
    {
        return new self(
            name: $this->name,
            type: $this->type,
            modifiers: Arr::reject($this->modifiers, fn ($modifier) => $removeModifier === $modifier),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'modifiers' => $this->modifiers,
        ];
    }

    public function isRequired(): bool
    {
        return in_array(FieldModifierEnum::Required, $this->modifiers);
    }

    public function isJSON(): bool
    {
        return $this->type === FieldTypeEnum::Json;
    }
}
