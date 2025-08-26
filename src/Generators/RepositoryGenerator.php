<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

class RepositoryGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if (!$this->classExists('models', "{$this->modelSubFolder}{$this->model}")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Repository cause {$this->modelSubFolder}{$this->model} Model does not exists.",
                "Create a {$this->modelSubFolder}{$this->model} Model by himself or run command 'php artisan make:entity {$this->modelSubFolder}{$this->model} --only-model'."
            );
        }

        if (!$this->isStubExists('repository')) {
            return;
        }

        $repositoryContent = $this->getStub('repository', [
            'entity' => $this->model,
            'namespace' => $this->getOrCreateNamespace('repositories'),
            'modelNamespace' => $this->getOrCreateNamespace('models', $this->modelSubFolder)
        ]);

        $this->saveClass('repositories', "{$this->model}Repository", $repositoryContent);

        event(new SuccessCreateMessage("Created a new Repository: {$this->model}Repository"));
    }
}
