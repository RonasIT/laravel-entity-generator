<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

class ServiceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if (!$this->classExists('repositories', "{$this->model}Repository")) {
            $this->throwFailureException(
                exceptionClass: ClassNotExistsException::class,
                failureMessage: "Cannot create {$this->model}Service cause {$this->model}Repository does not exists.",
                recommendedMessage: "Create a {$this->model}Repository by himself or run command 'php artisan make:entity {$this->model} --only-repository'.",
            );
        }

        if (!$this->isStubExists('service')) {
            return;
        }

        $this->createNamespace('services');

        $serviceContent = $this->getStub('service', [
            'entity' => $this->model,
            'fields' => $this->getFields(),
            'namespace' => $this->generateNamespace($this->paths['services']),
            'repositoriesNamespace' => $this->generateNamespace($this->paths['repositories']),
        ]);

        $this->saveClass('services', "{$this->model}Service", $serviceContent);

        event(new SuccessCreateMessage("Created a new Service: {$this->model}Service"));
    }

    protected function getFields(): array
    {
        $simpleSearch = Arr::only($this->fields, ['integer', 'integer-required', 'boolean', 'boolean-required']);

        return [
            'simple_search' => Arr::collapse($simpleSearch),
            'search_by_query' => array_merge($this->fields['string'], $this->fields['string-required'])
        ];
    }
}
