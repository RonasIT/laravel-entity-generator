<?php

namespace RonasIT\Support\Support;

use Illuminate\Support\Str;

class CommandLineNovaField extends AbstractNovaField
{
    public function __construct(string $type, string $name)
    {
        $this->isRequired = Str::contains($type, 'required');
        $this->type = $type;
        $this->name = $name;
    }
}
