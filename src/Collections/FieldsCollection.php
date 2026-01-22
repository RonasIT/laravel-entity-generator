<?php

namespace RonasIT\Support\Collections;

use Countable;
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

    public function replaceModifier(
        FieldTypeEnum $type,
        FieldModifierEnum $originalModifier,
        FieldModifierEnum $newModifier,
    ): self {
        $fields = $this->fields->map(
            fn (Field $field) => ($field->type === $type)
                ? $field->replaceModifier($originalModifier, $newModifier)
                : $field,
        );

        return new self($fields);
    }

    public function removeModifier(FieldTypeEnum $type, FieldModifierEnum $removeModifier): self
    {
        $fields = $this->fields->map(
            fn (Field $field) => ($field->type === $type)
                ? $field->removeModifier($removeModifier)
                : $field,
        );

        return new self($fields);
    }

    public function remove(FieldTypeEnum $type): self
    {
        $fields = $this->fields->reject(fn (Field $field) => $field->type === $type);

        return new self($fields);
    }

    public function whereType(FieldTypeEnum $type): Collection
    {
        return $this->fields->where('type', $type);
    }

    public function whereTypeIn(array $types): Collection
    {
        return $this->fields->whereIn('type', $types);
    }

    public function pluck(string $string): Collection
    {
        return $this->fields->pluck($string);
    }
}
