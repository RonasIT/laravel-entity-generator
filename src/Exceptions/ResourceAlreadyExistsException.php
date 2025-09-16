<?php

namespace RonasIT\Support\Exceptions;

use Exception;
use Illuminate\Support\Str;
use RonasIT\Support\Enums\ResourceTypeEnum;

class ResourceAlreadyExistsException extends Exception
{
    public function __construct(
        protected string $entityName,
        protected ResourceTypeEnum $resourceType,
        protected ?string $entityNamespace = '',
    ) {
        $formattedResourceType = $this->formatEntityName($resourceType, $entityName);

        $entityPlaceholder = $entityNamespace
            ? "{$entityNamespace}\\{$formattedResourceType}"
            : $formattedResourceType;

        parent::__construct("Cannot create {$entityPlaceholder} cause it already exists. Remove {$entityPlaceholder} and run command again.");
    }

    protected function formatEntityName(ResourceTypeEnum $resourceType, string $entityName): string
    {
        switch ($resourceType) {
            case ResourceTypeEnum::Model:
                return "{$entityName} {$resourceType->value}";

            case ResourceTypeEnum::NovaResource:
                return $entityName . Str::ucfirst(ResourceTypeEnum::Resource->value);

            case ResourceTypeEnum::NovaTest:
                return "Nova{$entityName}Test";
        }

        return $entityName . Str::studly($resourceType->value);
    }
}