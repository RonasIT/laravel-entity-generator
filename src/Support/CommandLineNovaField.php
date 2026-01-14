<?php

namespace RonasIT\Support\Support;

use RonasIT\Support\Enums\FieldModifiersEnum;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct(string $type, array $field)
    {
        $this->isRequired = in_array(FieldModifiersEnum::Required->value, $field['modifiers']);
        $this->type = $type;
        $this->name = $field['name'];
    }
}
