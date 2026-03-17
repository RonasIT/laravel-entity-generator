<?php

namespace RonasIT\Support\Support\Fields;

use ArrayIterator;
use Illuminate\Support\Arr;
use IteratorAggregate;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use Traversable;

final class FieldsCollection implements IteratorAggregate
{
    private array $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    public function replaceModifier(
        FieldTypeEnum $type,
        FieldModifierEnum $originalModifier,
        FieldModifierEnum|string $newModifier,
    ): self {
        $fields = Arr::map(
            array: $this->fields,
            callback: fn (Field $field) => ($field->type === $type)
                ? $field->replaceModifier($originalModifier, $newModifier)
                : $field,
        );

        return new self(...$fields);
    }

    public function removeModifier(FieldTypeEnum $type, FieldModifierEnum $removeModifier): self
    {
        $fields = Arr::map(
            array: $this->fields,
            callback: fn (Field $field) => ($field->type === $type)
                ? $field->removeModifier($removeModifier)
                : $field,
        );

        return new self(...$fields);
    }

    public function remove(FieldTypeEnum $type): self
    {
        $fields = Arr::reject($this->fields, fn (Field $field) => $field->type === $type);

        return new self(...$fields);
    }

    public function whereType(FieldTypeEnum $type): self
    {
        return new self(...Arr::where($this->fields, fn (Field $field) => $field->type === $type));
    }

    public function whereTypeIn(array $types): self
    {
        return new self(...Arr::where($this->fields, fn (Field $field) => in_array($field->type, $types)));
    }

    public function add(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function pluck(string $value): array
    {
        return Arr::pluck($this->fields, $value);
    }

    public function merge(array $fields): self
    {
        return new self(...$this->fields, ...$fields);
    }

    public function get(): array
    {
        return $this->fields;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }
}
