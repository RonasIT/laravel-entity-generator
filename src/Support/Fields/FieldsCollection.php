<?php

namespace RonasIT\EntityGenerator\Support\Fields;

use ArrayIterator;
use Illuminate\Support\Arr;
use IteratorAggregate;
use RonasIT\EntityGenerator\Enums\FieldTypeEnum;
use Traversable;

final class FieldsCollection implements IteratorAggregate
{
    private array $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    public function filterByType(FieldTypeEnum ...$types): array
    {
        return Arr::where($this->fields, fn (Field $field) => in_array($field->type, $types));
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
        return !empty($this->filterByType(FieldTypeEnum::Timestamp));
    }
}
