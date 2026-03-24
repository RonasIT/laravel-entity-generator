<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\Fields\Field;

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
                $this->getGetValidationParameters(),
            );
            $this->createRequest(
                self::SEARCH_METHOD,
                false,
                $this->getSearchValidationParameters(),
            );
        }

        if (in_array('D', $this->crudOptions)) {
            $this->createRequest(self::DELETE_METHOD);
        }

        if (in_array('C', $this->crudOptions)) {
            $this->createRequest(
                self::CREATE_METHOD,
                false,
                $this->getCreateValidationParameters(),
            );
        }

        if (in_array('U', $this->crudOptions)) {
            $this->createRequest(
                self::UPDATE_METHOD,
                true,
                $this->getUpdateValidationParameters(),
            );
        }
    }

    protected function createRequest($method, $needToValidate = true, $parameters = []): void
    {
        $requestsFolder = $this->model;
        $modelName = $this->getEntityName($method);

        $this->checkResourceExists('requests', "{$requestsFolder}/{$method}{$modelName}Request");

        $content = $this->getStub('request', [
            'method' => $method,
            'entity' => $modelName,
            'parameters' => $parameters,
            'needToValidate' => $needToValidate,
            'requestsFolder' => $requestsFolder,
            'namespace' => $this->generateNamespace($this->paths['requests']),
            'servicesNamespace' => $this->generateNamespace($this->paths['services']),
            'entityNamespace' => $this->getModelClass($this->model),
            'needToValidateWith' => Arr::has($parameters, 'with.*'),
            'availableRelations' => $this->getAvailableRelations(),
        ]);

        $this->saveClass('requests', "{$method}{$modelName}Request",
            $content, $requestsFolder,
        );

        event(new SuccessCreateMessage("Created a new Request: {$method}{$modelName}Request"));
    }

    protected function getGetValidationParameters(): array
    {
        return [
            'with' => ['array'],
            'with.*' => ['required', 'string', 'in:'],
        ];
    }

    protected function getCreateValidationParameters(): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            $rules = $this->getFieldRules($field);

            if ($field->isRequired()) {
                $field->isBoolean()
                    ? $rules[] = 'present'
                    : array_unshift($rules, 'required');
            }

            $result[$field->name] = $rules;
        }

        return $result;
    }

    protected function getUpdateValidationParameters(): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            $rules = $this->getFieldRules($field);

            if ($field->isRequired() && !$field->isBoolean()) {
                array_unshift($rules, 'required');
            }

            $result[$field->name] = $rules;
        }

        return $result;
    }

    protected function getSearchValidationParameters(): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            if (!$field->isTimestamp()) {
                $result[$field->name] = $this->getFieldRules($field);
            }
        }

        return [
            ...$result,
            'page' => ['integer'],
            'per_page' => ['integer'],
            'desc' => ['boolean'],
            'all' => ['boolean'],
            'order_by' => ['string', 'in:'],
            'query' => ['string', 'nullable'],
            'with' => ['array'],
            'with.*' => ['required', 'string', 'in:']
        ];
    }

    protected function getFieldRules(Field $field): array
    {
        $replaces = [
            FieldTypeEnum::Timestamp->value => 'date',
            FieldTypeEnum::Float->value => 'numeric',
            FieldTypeEnum::Json->value => 'array',
        ];

        $rules = [Arr::get($replaces, $field->type->value, $field->type->value)];

        if ($field->isKeyField() || in_array($field->name, $this->relationFields)) {
            $tableName = str_replace('_id', '', $field->name);

            $rules[] = "exists:{$this->getTableName($tableName)},id";
        }

        return $rules;
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
