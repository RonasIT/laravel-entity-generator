<?php

namespace RonasIT\Support\Support\Fields;

use Doctrine\DBAL\Schema\Column;

class DatabaseNovaField extends AbstractNovaField
{
    public function __construct(Column $field)
    {
        $this->isRequired = $field->getNotNull();
        $this->type = strtolower($field->getType()->getBindingType()->name);
        $this->name = $field->getName();
    }
}
