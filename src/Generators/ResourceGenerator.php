<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;

class ResourceGenerator extends EntityGenerator
{
    public function generate()
    {
        $this->generateResource();
        $this->generateCollectionResource();
    }

    public function generateCollectionResource()
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
            'plural_name' => $pluralName
        ]);

        $this->saveClass('resources', "{$pluralName}CollectionResource", $collectionResourceContent);

        event(new SuccessCreateMessage("Created a new CollectionResource: {$pluralName}CollectionResource"));
    }

    public function generateResource()
    {
        if ($this->classExists('resources', "{$this->model}Resource")) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model}Resource cause {$this->model}Resource already exists.",
                "Remove {$this->model}Resource."
            );
        }

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }
}