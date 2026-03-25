<?php

namespace RonasIT\Support\Support\Fields;

use RonasIT\Support\Enums\FieldTypeEnum;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct(FieldTypeEnum $type, Field $field)
    {
        $this->isRequired = $field->isRequired();
        $this->type = $type->value;
        $this->name = $field->name;
    }
}
