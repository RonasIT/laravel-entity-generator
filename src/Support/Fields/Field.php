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

    public function replaceModifier(FieldModifierEnum $originalModifier, FieldModifierEnum $newModifier): self
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
}
