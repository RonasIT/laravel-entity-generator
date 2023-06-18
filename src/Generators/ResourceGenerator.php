<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;

class ResourceGenerator extends EntityGenerator
{
    public function generate()
    {
        $this->generateResource();
        $this->generateResourceCollection();
    }

    public function generateResourceCollection()
    {
        if ($this->classExists('resources', "{$this->model}ResourceCollection")) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model}ResourceCollection cause {$this->model}ResourceCollection already exists.",
                "Remove {$this->model}ResourceCollection."
            );
        }

        $resourceCollectionContent = $this->getStub('resource_collection', [
            'entity' => $this->model
        ]);

        $this->saveClass('resources', "{$this->model}ResourceCollection", $resourceCollectionContent);

        event(new SuccessCreateMessage("Created a new ResourceCollection: {$this->model}ResourceCollection"));
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