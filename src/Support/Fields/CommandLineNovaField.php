<?php

namespace RonasIT\Support\Support\Fields;

use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\ValueObjects\Field;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct(FieldTypeEnum $type, Field $field)
    {
        $this->isRequired = in_array(FieldModifierEnum::Required, $field->modifiers);
        $this->type = $type->value;
        $this->name = $field->name;
    }
}
