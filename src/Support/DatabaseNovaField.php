<?php

namespace RonasIT\Support\Support;

use Doctrine\DBAL\Schema\Column;

class DatabaseNovaField extends AbstractNovaField
{
    public function __construct(Column $field)
    {
        $this->isRequired = $field->getNotNull();
        $this->type = $field->getType()->getName();
        $this->name = $field->getName();
    }
}
