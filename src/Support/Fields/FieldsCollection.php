<?php

namespace RonasIT\Support\Support\Fields;

use ArrayIterator;
use Illuminate\Support\Arr;
use IteratorAggregate;
use RonasIT\Support\Enums\FieldTypeEnum;
use Traversable;

final class FieldsCollection implements IteratorAggregate
{
    private array $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    public function whereType(FieldTypeEnum $type): self
    {
        return new self(...Arr::where($this->fields, fn (Field $field) => $field->type === $type));
    }

    public function add(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function getNames(): array
    {
        return Arr::pluck($this->fields, 'name');
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }

    public function toArray(): array
    {
        return $this->fields;
    }

    public function hasTimestamps(): bool
    {
        return !empty($this->whereType(FieldTypeEnum::Timestamp)->toArray());
    }
}
