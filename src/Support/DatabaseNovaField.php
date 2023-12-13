<?php

namespace RonasIT\Support\Support;

class DatabaseNovaField extends AbstractNovaField
{
    public function __construct($field)
    {
        $this->isRequired = $field->getNotNull();
        $this->type = $field->getType()->getName();
        $this->name = $field->getName();
    }
}
