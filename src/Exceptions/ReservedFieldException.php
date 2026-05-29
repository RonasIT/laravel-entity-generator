<?php

namespace RonasIT\EntityGenerator\Exceptions;

use Exception;

class ReservedFieldException extends Exception
{
    public function __construct(
        string $fieldName,
    ) {
        parent::__construct("Field '{$fieldName}' is reserved and cannot be set manually. See: https://github.com/RonasIT/laravel-entity-generator#reserved-field-names");
    }
}
