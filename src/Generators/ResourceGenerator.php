<?php

namespace RonasIT\EntityGenerator\Generators;

use RonasIT\EntityGenerator\Enums\ReservedFieldEnum;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;

class ResourceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if ($this->isStubExists('resource')) {
            $this->createNamespace('resources');

            $this->generateResource();

            if ($this->isStubExists('collection_resource')) {
                $this->generateCollectionResource();
            }
        }
    }

    public function generateCollectionResource(): void
    {
        $pluralName = $this->getPluralName($this->model);

        $this->checkResourceExists('resources', "{$this->model}/{$pluralName}CollectionResource");

        $collectionResourceContent = $this->getStub('collection_resource', [
            'singular_name' => $this->model,
            'plural_name' => $pluralName,
            'namespace' => $this->generateNamespace($this->paths['resources']),
        ]);

        $this->saveClass('resources', "{$pluralName}CollectionResource", $collectionResourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new CollectionResource: {$pluralName}CollectionResource"));
    }

    public function generateResource(): void
    {
        $this->checkResourceExists('resources', "{$this->model}/{$this->model}Resource");

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model,
            'namespace' => $this->generateNamespace($this->paths['resources']),
            'model_namespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
            'fields' => $this->getResourceFields(),
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }

    protected function getResourceFields(): ?array
    {
        $fields = when($this->fields, fn () => $this->fields->getNames());

        if (empty($fields)) {
            return $fields;
        }

        $reservedFields = array_map(fn (ReservedFieldEnum $f) => $f->value, ReservedFieldEnum::resourceAutoFields());

        return array_merge($reservedFields, $fields);
    }
}
