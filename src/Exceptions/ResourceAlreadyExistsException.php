<?php

namespace RonasIT\Support\Exceptions;

use Exception;
use Illuminate\Support\Str;

class ResourceAlreadyExistsException extends Exception
{
    public function __construct(
        protected string $filePath,
    ) {
        $entity = Str::afterLast($this->filePath, '/');
        $entity = Str::before($entity, '.php');

        parent::__construct("Cannot create {$entity} cause it already exists. Remove {$this->filePath} and run command again.");
    }
}
