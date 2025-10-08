<?php

namespace RonasIT\Support\Exceptions;

use Exception;
use RonasIT\Support\Enums\ResourceTypeEnum;

class ResourceAlreadyExistsException extends Exception
{
    public function __construct(
        protected string $entityName,
        protected ResourceTypeEnum $resourceType,
        protected string $entityNamespace = '',
    ) {
        $formattedEntityName = $this->formatEntityName($resourceType, $entityName);

        $entityPlaceholder = empty($entityNamespace)
            ? $formattedEntityName
            : "{$entityNamespace}\\{$formattedEntityName}";

        parent::__construct("Cannot create {$entityPlaceholder} cause it already exists. Remove {$entityPlaceholder} and run command again.");
    }

    protected function formatEntityName(ResourceTypeEnum $resourceType, string $entityName): string
    {
        return match ($resourceType) {
            ResourceTypeEnum::Model => "{$entityName} {$resourceType->value}",
            ResourceTypeEnum::NovaTest => "Nova{$entityName}Test",
            default => $entityName . $resourceType->name,
        };
    }
}
