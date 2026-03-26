<?php

namespace RonasIT\EntityGenerator\Support\Fields;

use RonasIT\EntityGenerator\Enums\FieldTypeEnum;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct(FieldTypeEnum $type, Field $field)
    {
        $this->isRequired = $field->isRequired();
        $this->type = $type->value;
        $this->name = $field->name;
    }
}
