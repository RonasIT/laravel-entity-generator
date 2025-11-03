<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;

class RepositoryGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $this->checkResourceNotExists('models', "{$this->model}Repository", $this->model, $this->modelSubFolder);

        if (!$this->isStubExists('repository')) {
            return;
        }

        $this->createNamespace('repositories');

        $repositoryContent = $this->getStub('repository', [
            'entity' => $this->model,
            'namespace' => $this->generateNamespace($this->paths['repositories']),
            'modelNamespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder)
        ]);

        $this->saveClass('repositories', "{$this->model}Repository", $repositoryContent);

        event(new SuccessCreateMessage("Created a new Repository: {$this->model}Repository"));
    }
}
