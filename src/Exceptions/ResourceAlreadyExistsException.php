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

        $filePath = $this->getFilePath();

        parent::__construct("Cannot create {$entity} cause it already exists. Remove {$filePath} and run command again.");
    }

    protected function getEntity(): string
    {
        $entity = Str::afterLast($this->filePath, '/');

        return Str::before($entity, '.php');
    }

    protected function getFilePath(): string
    {
        $filePath = realpath($this->filePath);

        return empty($filePath) ? $this->filePath : "{$filePath}:1";
    }
}
