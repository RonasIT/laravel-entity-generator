<?php

namespace RonasIT\Support\Exceptions;

use Exception;
use RonasIT\Support\Enums\ResourceTypeEnum;

class ResourceAlreadyExistsException extends Exception
{
    public function __construct(
        protected string $entityName,
        protected ResourceTypeEnum $resourceType,
        protected ?string $entityNamespace = null,
    ) {
        parent::__construct("Cannot create {$entityNamespace}{$resourceType->value} cause it already exists. Remove {$entityName} {$resourceType->value} and run command again.");
    }
}