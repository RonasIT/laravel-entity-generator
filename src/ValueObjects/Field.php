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

    public function updateModifiers(callable $callback): self
    {
        return new self(
            $this->name,
            $this->type,
            $callback($this->modifiers),
        );
    }

    protected function validateModifiers(): void
    {
        $diff = array_udiff($this->modifiers, FieldModifierEnum::cases(), fn ($a, $b) => $a <=> $b);

        if (!empty($diff)) {
            throw new UnknownFieldModifierException(Arr::first($diff), $this->name);
        }
    }
}
