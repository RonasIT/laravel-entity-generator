<?php

namespace RonasIT\Support\Exceptions;

class ResourceAlreadyExistsException extends AbstractResourceException
{
    public function __construct(
        string $filePath,
    ) {
        $entity = $this->getEntity($filePath);

        parent::__construct("Cannot create {$entity} cause it already exists. Remove {$filePath} and run command again.");
    }
}
