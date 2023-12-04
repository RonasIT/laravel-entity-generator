<?php

namespace RonasIT\Support\Support;

class DatabaseNovaField extends BaseNovaField
{
    protected function setIsRequired($field): void
    {
        $this->isRequired = $field->getNotNull();
    }

    protected function setType($field): void
    {
        $this->type = $field->getType()->getName();
    }

    protected function setName($field): void
    {
        $this->name = $field->getName();
    }
}
