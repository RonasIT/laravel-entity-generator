<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;

class ResourceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if ($this->isStubExists('resource')) {
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
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$pluralName}CollectionResource cause {$pluralName}CollectionResource already exists.",
                "Remove {$pluralName}CollectionResource."
            );
        }

        $collectionResourceContent = $this->getStub('collection_resource', [
            'singular_name' => $this->model,
            'plural_name' => $pluralName,
            'namespace' => $this->getOrCreateNamespace('resources')
        ]);

        $this->saveClass('resources', "{$pluralName}CollectionResource", $collectionResourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new CollectionResource: {$pluralName}CollectionResource"));
    }

    public function generateResource(): void
    {
        if ($this->classExists('resources', "{$this->model}Resource")) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model}Resource cause {$this->model}Resource already exists.",
                "Remove {$this->model}Resource."
            );
        }

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model,
            'namespace' => $this->getOrCreateNamespace('resources'),
            'model_namespace' => $this->getOrCreateNamespace('models', $this->modelSubFolder),
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }
}
