<?php

namespace RonasIT\EntityGenerator\Exceptions;

class ClassNotExistsException extends EntityCreateException
{
    public function __construct(string $creatableClass, string $className)
    {
        parent::__construct("Cannot create {$creatableClass} cause {$className} does not exist.\nCreate {$className}.");
    }
}
