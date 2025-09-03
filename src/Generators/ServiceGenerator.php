<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

class ServiceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if ($this->classExists('repositories', "{$this->model}Repository")) {
            $stub = 'service';
        } else {
            $stub = 'service_with_trait';

            if (!$this->classExists('models', $this->model)) {
                // TODO: pass $this->modelSubfolder to Exception after refactoring in https://github.com/RonasIT/laravel-entity-generator/issues/179
                $this->throwFailureException(
                    exceptionClass: ClassNotExistsException::class,
                    failureMessage: "Cannot create {$this->model}Service cause {$this->model} Model does not exists.",
                    recommendedMessage: "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'.",
                );
            }
        }

        if (!$this->isStubExists($stub)) {
            return;
        }

        $serviceContent = $this->getStub($stub, [
            'entity' => $this->model,
            'fields' => $this->getFields(),
            'namespace' => $this->getOrCreateNamespace('services'),
            'repositoriesNamespace' => $this->getOrCreateNamespace('repositories'),
            'modelsNamespace' => $this->getOrCreateNamespace('models')
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
