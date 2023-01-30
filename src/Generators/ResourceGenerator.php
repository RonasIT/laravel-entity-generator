<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;

class ResourceGenerator extends EntityGenerator
{
    public function generate()
    {
        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }
}