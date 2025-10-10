<?php

namespace RonasIT\Support\Exceptions;

use Exception;
use Illuminate\Support\Str;

class ResourceAlreadyExistsException extends Exception
{
    public function __construct(
        protected string $filePath,
    ) {
        $entity = $this->getEntity();

        parent::__construct("Cannot create {$entity} cause it already exists. Remove {$this->$filePath} and run command again.");
    }

    protected function getEntity(): string
    {
        $fileName = Str::afterLast($this->filePath, '/');

        return Str::before($fileName, '.php');
    }
}
