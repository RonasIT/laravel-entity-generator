<?php

namespace RonasIT\Support\Exceptions;

use Exception;

class UnknownFieldTypeException extends Exception
{
    public function __construct(
        protected string $typeName,
        protected string $generatorName,
    ) {
        parent::__construct("Unknown field type {$this->typeName} in {$this->generatorName}.");
    }
}
