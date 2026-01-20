<?php

namespace RonasIT\Support\Collections;

use Countable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use IteratorAggregate;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\ValueObjects\Field;
use Traversable;

final readonly class FieldsCollection implements Countable, IteratorAggregate
{
    private Collection $fields;

    public function __construct(iterable $fields = [])
    {
        $this->fields = collect($fields);
    }

    public function getIterator(): Traversable
    {
        return $this->fields->getIterator();
    }

    public function count(): int
    {
        return $this->fields->count();
    }

    public function merge(array $newFields): self
    {
        return new self($this->fields->concat($newFields));
    }

    public function getFields(): array
    {
        return $this->fields->all();
    }

    public function replaceFieldModifier(
        FieldTypeEnum $type,
        FieldModifierEnum $originalModifier,
        FieldModifierEnum $newModifier,
    ): self {
        $replaceModifierCallback = fn (array $modifiers) => Arr::map(
            array: $modifiers,
            callback: fn ($modifier) => ($modifier === $originalModifier) ? $newModifier : $modifier,
        );

        $fields = $this->updateModifiersByFieldType($type, $replaceModifierCallback);

        return new self($fields);
    }

    public function removeFieldModifier(FieldTypeEnum $type, FieldModifierEnum $removeModifier): self
    {
        $removeModifierCallback = fn (array $modifiers) => Arr::reject(
            array: $modifiers,
            callback: fn ($modifier) => $removeModifier === $modifier,
        );

        $fields = $this->updateModifiersByFieldType($type, $removeModifierCallback);

        return new self($fields);
    }

    public function removeFieldsByType(FieldTypeEnum $type): self
    {
        $fields = $this->fields->filter(fn (Field $field) => $field->type !== $type);

        return new self($fields);
    }

    public function getFieldsByType(FieldTypeEnum $type): array
    {
        return $this->fields->where('type', $type)->all();
    }

    protected function updateModifiersByFieldType(FieldTypeEnum $type, callable $callback): Collection
    {
        return $this
            ->fields
            ->map(fn (Field $field) => ($field->type === $type) ? $field->updateModifiers($callback) : $field);
    }
}
