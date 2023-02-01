<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;

class ResourceGenerator extends EntityGenerator
{
    public function generate()
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