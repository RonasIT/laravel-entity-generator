<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Enums\FieldModifiersEnum;
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
        $parameters['array'][] = $this->convertToField('with', []);

        $parameters['string'][] = $this->convertToField('with.*', [FieldModifiersEnum::Required->value]);

        return $this->getValidationParameters($parameters, true);
    }

    protected function getCreateValidationParameters(): array
    {
        $parameters = $this->fields->toArray();

        if (!empty($parameters['boolean'])) {
            $parameters['boolean'] = $this->replaceFieldModifier($parameters['boolean'], FieldModifiersEnum::Required, 'present');
        }

        return $this->getValidationParameters($parameters, true);
    }

    protected function getUpdateValidationParameters(): array
    {
        $parameters = $this->fields->toArray();

        $this->removeFieldModifier($parameters['boolean'], FieldModifiersEnum::Required);

        return $this->getValidationParameters($parameters, false);
    }

    protected function getSearchValidationParameters(): array
    {
        $parameters = Arr::except($this->fields->toArray(), ['timestamp']);

        $parameters['boolean'] = [
            ...$this->removeFieldModifier($parameters['boolean'], FieldModifiersEnum::Required),
            $this->convertToField('desc', []),
            $this->convertToField('all', []),
        ];

        $parameters['integer'] = [
            ...$this->fields->integer,
            $this->convertToField('page', []),
            $this->convertToField('per_page', []),
        ];

        $parameters['string'] = [
            ...$this->fields->string,
            $this->convertToField('order_by', []),
            $this->convertToField('query', ['nullable']),
            $this->convertToField('with.*', [FieldModifiersEnum::Required->value]),
        ];

        $parameters['array'][] = $this->convertToField('with', []);

        $rules = $this->getValidationParameters($parameters, true);

        return $this->orderSearchRequest($rules);
    }

    public function getValidationParameters($parameters, $requiredAvailable): array
    {
        $result = [];

        foreach ($parameters as $type => $typedFields) {
            foreach ($typedFields as $field) {
                $isRequired = in_array(FieldModifiersEnum::Required->value, $field['modifiers']);
                $isNullable = in_array('nullable', $field['modifiers']);
                $isPresent = in_array('present', $field['modifiers']);

                $required = $isRequired && $requiredAvailable;

                $result[] = $this->getRules($field, $type, $required, $isNullable, $isPresent);
            }
        }

        return $result;
    }

    protected function getRules($field, $type, $required, $nullable, $present): array
    {
        $replaces = [
            'timestamp' => 'date',
            'float' => 'numeric',
            'json' => 'array',
        ];

        $rules = [
            Arr::get($replaces, $type, $type)
        ];

        if (in_array($field['name'], $this->relationFields)) {
            $tableName = str_replace('_id', '', $field['name']);

            $rules[] = "exists:{$this->getTableName($tableName)},id";

            $required = true;
        }

        if ($required) {
            array_unshift($rules, 'required');
        }

        if ($nullable) {
            $rules[] = 'nullable';
        }

        if ($present) {
            $rules[] = 'present';
        }

        if (in_array($field['name'], ['order_by', 'with.*'])) {
            $rules[] = 'in:';
        }

        return [
            'name' => $field['name'],
            'rules' => $rules,
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

    protected function removeFieldModifier(array $fields, FieldModifiersEnum $removeModifier): array
    {
        foreach ($fields as &$field) {
            $field['modifiers'] = array_filter(
                array: $field['modifiers'],
                callback: fn ($modifier) => $modifier !== $removeModifier->value,
            );
        }

        return $fields;
    }

    protected function replaceFieldModifier(array $fields, FieldModifiersEnum $originalModifier, string $newModifier): array
    {
        foreach ($fields as &$field) {
            $field['modifiers'] = Arr::map(
                array: $field['modifiers'],
                callback: fn ($modifier) => ($modifier === $originalModifier->value) ? $newModifier : $modifier,
            );
        }

        return $fields;
    }

    protected function orderSearchRequest(array $rules): array
    {
        $order = [
            'page',
            'per_page',
            'order_by',
            'desc',
            'all',
            'query',
            'with',
            'with.*',
        ];

        $ordered = Arr::sort($rules, function ($rule) use ($order) {
            $position = array_search($rule['name'], $order, true);

            return $position === false ? -1 : $position;
        });

        return array_values($ordered);
    }

    private function getEntityName($method): string
    {
        if ($method === self::SEARCH_METHOD) {
            return Str::plural($this->model);
        }

        return $this->model;
    }
}
