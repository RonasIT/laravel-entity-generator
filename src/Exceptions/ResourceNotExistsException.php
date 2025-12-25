<?php

namespace RonasIT\Support\Exceptions;

class ResourceNotExistsException extends AbstractResourceException
{
    public function __construct(
        string $creatableResource,
        string $requiredFilePath,
    ) {
        $resource = $this->getEntity($requiredFilePath);

        parent::__construct("Cannot create {$creatableResource} cause {$resource} does not exist. Create {$requiredFilePath} and run command again.");
    }
}
