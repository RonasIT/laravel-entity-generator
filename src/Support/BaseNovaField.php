<?php

namespace RonasIT\Support\Support;

abstract class BaseNovaField
{
    public $type;
    public $name;
    public $isRequired;

    public function __construct($field)
    {
        $this->setIsRequired($field);
        $this->setType($field);
        $this->setName($field);
    }

    abstract protected function setIsRequired($field): void;
    abstract protected function setType($field): void;
    abstract protected function setName($field): void;
}
