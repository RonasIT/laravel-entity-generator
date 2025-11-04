<?php

namespace RonasIT\Support\Exceptions;

class ResourceNotExistsException extends AbstractResourceException
{
    public function __construct(
        string $entity,
        string $filePath,
    ) {
        $resource = $this->getEntity($filePath);

        parent::__construct("Cannot create {$entity} cause {$resource} does not exist. Create {$filePath} and run command again.");
    }
}
