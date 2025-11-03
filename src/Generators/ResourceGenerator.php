<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;

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

        if ($this->classExists('resources', "{$pluralName}CollectionResource")) {
            $path = $this->getClassPath('resources', "{$pluralName}CollectionResource");

            throw new ResourceAlreadyExistsException($path);
        }

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
        if ($this->classExists('resources', "{$this->model}Resource")) {
            $path = $this->getClassPath('resources', "{$this->model}Resource");

            throw new ResourceAlreadyExistsException($path);
        }

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model,
            'namespace' => $this->generateNamespace($this->paths['resources']),
            'model_namespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
            'fields' => when($this->fields, fn () => Arr::flatten($this->fields)),
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }
}
