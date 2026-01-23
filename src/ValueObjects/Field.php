<?php

namespace RonasIT\Support\ValueObjects;

use Illuminate\Support\Arr;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Exceptions\UnknownFieldModifierException;

final readonly class Field
{
    public function __construct(
        public string $name,
        public FieldTypeEnum $type,
        public array $modifiers = [],
    ) {
        $this->validateModifiers();
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

    protected function validateModifiers(): void
    {
        foreach ($this->modifiers as $modifier) {
            if (!in_array($modifier?->value ?? [], FieldModifierEnum::values())) {
                throw new UnknownFieldModifierException($modifier, $this->name);
            }
        }
    }
}
