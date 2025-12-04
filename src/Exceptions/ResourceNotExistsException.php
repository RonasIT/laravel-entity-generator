<?php

namespace RonasIT\Support\Exceptions;

class ResourceNotExistsException extends AbstractResourceException
{
    public function __construct(
        string $createableResource,
        string $requiredFilePath,
    ) {
        $resource = $this->getEntity($requiredFilePath);

        parent::__construct("Cannot create {$createableResource} cause {$resource} does not exist. Create {$requiredFilePath} and run command again.");
    }
}
