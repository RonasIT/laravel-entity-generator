<?php

namespace RonasIT\Support\Support;

use Illuminate\Support\Str;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct($field)
    {
        $this->isRequired = Str::contains($field['type'], 'required');
        $this->type = $field['type'];
        $this->name = $field['name'];
    }
}
