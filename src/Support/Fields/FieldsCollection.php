<?php

namespace RonasIT\Support\Support\Fields;

use Closure;
use Illuminate\Support\Arr;
use RonasIT\Support\Enums\FieldTypeEnum;

final class FieldsCollection
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

    public function map(Closure $callback, bool $withKeys = true): array
    {
        return ($withKeys)
            ? Arr::mapWithKeys($this->fields, $callback)
            : Arr::map($this->fields, $callback);
    }

    public function getNames(): array
    {
        return Arr::pluck($this->fields, 'name');
    }

    public function hasTimestamps(): bool
    {
        return !empty($this->filterByType(FieldTypeEnum::Timestamp));
    }
}
