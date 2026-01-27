<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;

class ServiceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $this->checkResourceNotExists('repositories', "{$this->model}Service", "{$this->model}Repository");

        $this->checkResourceExists('services', "{$this->model}Service");

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
        return [
            'simple_search' => $this->fields->whereTypeIn([FieldTypeEnum::Integer, FieldTypeEnum::Boolean])->pluck('name')->toArray(),
            'search_by_query' => $this->fields->whereType(FieldTypeEnum::String)->pluck('name')->toArray(),
        ];
    }
}
