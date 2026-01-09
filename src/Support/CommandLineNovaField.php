<?php

namespace RonasIT\Support\Support;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct(string $type, array $field)
    {
        $this->isRequired = in_array('required', $field['modifiers']);
        $this->type = $type;
        $this->name = $field['name'];
    }
}
