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
        $formattedResourceType = $this->formatResourceType($resourceType);

        $entityPlaceholder = $entityNamespace
            ? "{$entityNamespace}\\{$entityName}{$formattedResourceType}"
            : $resourceType->value;

        parent::__construct("Cannot create {$entityPlaceholder} cause it already exists. Remove {$entityName}{$formattedResourceType} and run command again.");
    }

    protected function formatResourceType(ResourceTypeEnum $resourceType): string
    {
        switch ($resourceType) {
            case ResourceTypeEnum::Model:
                return " {$resourceType->value}";

            case ResourceTypeEnum::NovaResource:
                return Str::ucfirst(ResourceTypeEnum::Resource->value);

            case ResourceTypeEnum::NovaTest:
                return 'Test';
        }

        return Str::studly($resourceType->value);
    }
}