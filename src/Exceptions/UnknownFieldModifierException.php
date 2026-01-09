<?php

namespace RonasIT\Support\Exceptions;

use Exception;

class UnknownFieldModifierException extends Exception
{
    public function __construct(
        protected string $modifierName,
        protected string $fieldName,
    ) {
        parent::__construct("Unknown field modifier {$this->modifierName} in {$this->fieldName} type.");
    }
}
