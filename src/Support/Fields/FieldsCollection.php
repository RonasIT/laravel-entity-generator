<?php

namespace RonasIT\EntityGenerator\Support\Fields;

use Closure;
use Illuminate\Support\Arr;
use RonasIT\EntityGenerator\Enums\FieldTypeEnum;

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

    public function toNamedMap(Closure $callback): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (Field $field) => [$field->name => $callback($field)])
            ->filter()
            ->toArray();
    }

    public function getNames(): array
    {
        return Arr::pluck($this->fields, 'name');
    }

    public function isEmpty(): bool
    {
        return empty($this->fields);
    }
}
