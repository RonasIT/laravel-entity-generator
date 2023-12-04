<?php

namespace RonasIT\Support\Support;

use Illuminate\Support\Str;

class CommandLineNovaField extends BaseNovaField
{
    protected function setIsRequired($field): void
    {
        $this->isRequired = Str::contains($field['type'], 'required');
    }

    protected function setType($field): void
    {
        $this->type = $field['type'];
    }

    protected function setName($field): void
    {
        $this->name = $field['name'];
    }
}
