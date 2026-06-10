<?php

namespace RonasIT\EntityGenerator\Exceptions;

use Exception;

class ReservedFieldException extends Exception
{
    public function __construct(array $fieldNames)
    {
        $fields = implode(', ', $fieldNames);

        parent::__construct("Fields '{$fields}' are reserved and cannot be set manually. See: https://github.com/RonasIT/laravel-entity-generator#reserved-field-names");
    }
}
