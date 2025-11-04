<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;

class RequestsGenerator extends EntityGenerator
{
    const SEARCH_METHOD = 'Search';
    const UPDATE_METHOD = 'Update';
    const CREATE_METHOD = 'Create';
    const DELETE_METHOD = 'Delete';
    const GET_METHOD = 'Get';

    protected array $relationFields = [];

    public function generate(): void
    {
        if (!$this->isStubExists('request')) {
            return;
        }

        $this->createNamespace('requests');

        $this->relationFields = array_map(function ($field) {
            return Str::snake($field) . '_id';
        }, $this->relations->belongsTo);

        if (in_array('R', $this->crudOptions)) {
            $this->createRequest(
                self::GET_METHOD,
                true,
                $this->getGetValidationParameters()
            );
            $this->createRequest(
                self::SEARCH_METHOD,
                false,
                $this->getSearchValidationParameters()
            );
        }

        if (in_array('D', $this->crudOptions)) {
            $this->createRequest(self::DELETE_METHOD);
        }

        if (in_array('C', $this->crudOptions)) {
            $this->createRequest(
                self::CREATE_METHOD,
                false,
                $this->getCreateValidationParameters()
            );
        }

        if (in_array('U', $this->crudOptions)) {
            $this->createRequest(
                self::UPDATE_METHOD,
                true,
                $this->getUpdateValidationParameters()
            );
        }
    }

    protected function createRequest($method, $needToValidate = true, $parameters = []): void
    {
        $requestsFolder = $this->model;
        $modelName = $this->getEntityName($method);

        $content = $this->getStub('request', [
            'method' => $method,
            'entity' => $modelName,
            'parameters' => $parameters,
            'needToValidate' => $needToValidate,
            'requestsFolder' => $requestsFolder,
            'namespace' => $this->getNamespace('requests'),
            'servicesNamespace' => $this->getNamespace('services'),
            'entityNamespace' => $this->getModelClass($this->model),
            'needToValidateWith' => !is_null(Arr::first($parameters, fn ($parameter) => $parameter['name'] === 'with.*')),
            'availableRelations' => $this->getAvailableRelations(),
        ]);

        $this->saveClass('requests', "{$method}{$modelName}Request",
            $content, $requestsFolder
        );

        event(new SuccessCreateMessage("Created a new Request: {$method}{$modelName}Request"));
    }

    protected function getGetValidationParameters(): array
    {
        $parameters['array'] = ['with'];

        $parameters['string-required'] = ['with.*'];

        return $this->getValidationParameters($parameters, true);
    }

    protected function getCreateValidationParameters(): array
    {
        $parameters = Arr::except($this->fields, 'boolean-required');

        if (!empty($this->fields['boolean-required'])) {
            $parameters['boolean-present'] = $this->fields['boolean-required'];
        }

        return $this->getValidationParameters($parameters, true);
    }

    protected function getUpdateValidationParameters(): array
    {
        $parameters = Arr::except($this->fields, 'boolean-required');

        if (!empty($this->fields['boolean-required'])) {
            $parameters['boolean'] = array_merge($parameters['boolean'], $this->fields['boolean-required']);
        }

        return $this->getValidationParameters($parameters, false);
    }

    protected function getSearchValidationParameters(): array
    {
        $parameters = Arr::except($this->fields, [
            'timestamp', 'timestamp-required', 'string-required', 'integer-required', 'boolean-required'
        ]);

        $parameters['boolean'] = array_merge($this->fields['boolean-required'], [
            'desc',
            'all',
        ]);

        $parameters['integer'] = array_merge($this->fields['integer'], [
            'page',
            'per_page',
        ]);

        $parameters['array'] = ['with'];

        $parameters['string'] = ['order_by'];

        $parameters['string-nullable'] = ['query'];

        $parameters['string-required'] = ['with.*'];

        return $this->getValidationParameters($parameters, false);
    }

    public function getValidationParameters($parameters, $requiredAvailable): array
    {
        $result = [];

        foreach ($parameters as $type => $parameterNames) {
            $isRequired = Str::contains($type, 'required');
            $isNullable = Str::contains($type, 'nullable');
            $isPresent = Str::contains($type, 'present');
            $type = head(explode('-', $type));

            foreach ($parameterNames as $name) {
                $required = $isRequired && $requiredAvailable;

                $result[] = $this->getRules($name, $type, $required, $isNullable, $isPresent);
            }
        }

        return $result;
    }

    protected function getRules($name, $type, $required, $nullable, $present): array
    {
        $replaces = [
            'timestamp' => 'date',
            'float' => 'numeric',
            'json' => 'array'
        ];

        $rules = [
            Arr::get($replaces, $type, $type)
        ];

        if (in_array($name, $this->relationFields)) {
            $tableName = str_replace('_id', '', $name);

            $rules[] = "exists:{$this->getTableName($tableName)},id";

            $required = true;
        }

        if ($required) {
            $rules[] = 'required';
        }

        if ($nullable) {
            $rules[] = 'nullable';
        }

        if ($present) {
            $rules[] = 'present';
        }

        if (in_array($name, ['order_by', 'with.*'])) {
            $rules[] = 'in:';
        }

        return [
            'name' => $name,
            'rules' => $rules
        ];
    }

    protected function getAvailableRelations(): array
    {
        $availableRelations = [];

        $relations = $this->prepareRelations();

        foreach ($relations as $type => $entities) {
              array_push(
                 $availableRelations,
                 ...Arr::map($entities, fn ($entity) => $this->getRelationName($entity, $type)),
             );
        }

        return $availableRelations;
    }

    private function getEntityName($method): string
    {
        if ($method === self::SEARCH_METHOD) {
            return Str::plural($this->model);
        }

        return $this->model;
    }
}
