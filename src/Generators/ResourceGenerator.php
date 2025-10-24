<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;

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

        $this->throwIfResourceExists('resources', "{$this->model}/{$pluralName}CollectionResource");

        $collectionResourceContent = $this->getStub('collection_resource', [
            'singular_name' => $this->model,
            'plural_name' => $pluralName,
            'namespace' => $this->getNamespace('resources')
        ]);

        $this->saveClass('resources', "{$pluralName}CollectionResource", $collectionResourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new CollectionResource: {$pluralName}CollectionResource"));
    }

    public function generateResource(): void
    {
        $this->throwIfResourceExists('resources', "{$this->model}/{$this->model}Resource");

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model,
            'namespace' => $this->getNamespace('resources'),
            'model_namespace' => $this->getNamespace('models', $this->modelSubFolder),
            'fields' => when($this->fields, fn () => Arr::flatten($this->fields)),
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }
}
