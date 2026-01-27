<?php

namespace RonasIT\Support\Support\Fields;

use Illuminate\Support\Collection;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;

final class FieldsCollection extends Collection
{
    public function replaceModifier(
        FieldTypeEnum $type,
        FieldModifierEnum $originalModifier,
        FieldModifierEnum $newModifier,
    ): self {
        $fields = $this->map(
            fn (Field $field) => ($field->type === $type)
                ? $field->replaceModifier($originalModifier, $newModifier)
                : $field,
        );

        return new self($fields);
    }

    public function removeModifier(FieldTypeEnum $type, FieldModifierEnum $removeModifier): self
    {
        $fields = $this->map(
            fn (Field $field) => ($field->type === $type)
                ? $field->removeModifier($removeModifier)
                : $field,
        );

        return new self($fields);
    }

    public function remove(FieldTypeEnum $type): self
    {
        $fields = $this->reject(fn (Field $field) => $field->type === $type);

        return new self($fields);
    }

    public function whereType(FieldTypeEnum $type): Collection
    {
        return $this->where('type', $type);
    }

    public function whereTypeIn(array $types): Collection
    {
        return $this->whereIn('type', $types);
    }
}
