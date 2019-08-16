<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;

class RequestsGenerator extends EntityGenerator
{
    const SEARCH_METHOD = 'Search';
    const UPDATE_METHOD = 'Update';
    const CREATE_METHOD = 'Create';
    const DELETE_METHOD = 'Delete';
    const GET_METHOD = 'Get';

    public function setRelations($relations)
    {
        parent::setRelations($relations);

        $this->relations['belongsTo'] = array_map(function ($field) {
            return snake_case($field) . '_id';
        }, $this->relations['belongsTo']);

        return $this;
    }

    public function generate()
    {
        $this->createRequest(
            self::GET_METHOD,
            true,
            $this->getGetValidationParameters()
        );

        $this->createRequest(self::DELETE_METHOD);

        $this->createRequest(
            self::CREATE_METHOD,
            false,
            $this->getValidationParameters($this->fields, true)
        );

        $this->createRequest(
            self::UPDATE_METHOD,
            true,
            $this->getValidationParameters($this->fields, false)
        );

        $this->createRequest(
            self::SEARCH_METHOD,
            false,
            $this->getSearchValidationParameters()
        );
    }

    protected function createRequest($method, $needToValidate = true, $parameters = [])
    {
        $requestsFolder = $this->getPluralName($this->model);
        $modelName = $this->getEntityName($method);

        $content = $this->getStub('request', [
            'method' => $method,
            'entity' => $modelName,
            'parameters' => $parameters,
            'needToValidate' => $needToValidate,
            'requestsFolder' => $requestsFolder,
        ]);

        $this->saveClass('requests', "{$method}{$modelName}Request",
            $content, $requestsFolder
        );

        event(new SuccessCreateMessage("Created a new Request: {$method}{$modelName}Request"));
    }

    protected function getGetValidationParameters()
    {
        $parameters['array'] = ['with'];

        return $this->getValidationParameters($parameters, true);
    }

    protected function getSearchValidationParameters()
    {
        $parameters = array_except($this->fields, [
            'timestamp', 'timestamp-required', 'string-required', 'integer-required'
        ]);

        $parameters['integer'] = array_merge($this->fields['integer'], [
            'page', 'per_page', 'all',
        ]);

        $parameters['array'] = ['with'];

        $parameters['boolean'] = ['desc'];

        $parameters['string'] = ['query', 'order_by'];

        $parameters['string-required'] = ['with.*'];

        return $this->getValidationParameters($parameters, true);
    }

    public function getValidationParameters($parameters, $requiredAvailable)
    {
        $result = [];

        foreach ($parameters as $type => $parameterNames) {
            $isRequired = str_contains($type, 'required');
            $type = head(explode('-', $type));

            foreach ($parameterNames as $name) {
                $required = $isRequired && $requiredAvailable;

                $result[] = $this->getRules($name, $type, $required);
            }
        }

        return $result;
    }

    protected function getRules($name, $type, $required)
    {
        $replaces = [
            'timestamp' => 'date',
            'float' => 'numeric',
            'json' => 'array'
        ];

        $rules = [
            array_get($replaces, $type, $type)
        ];

        if (in_array($name, $this->relations['belongsTo'])) {
            $tableName = str_replace('_id', '', $name);

            $rules[] = "exists:{$this->getTableName($tableName)},id";

            $required = true;
        }

        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        return [
            'name' => $name,
            'rules' => $rules
        ];
    }

    private function getEntityName($method) {
        if ($method === self::SEARCH_METHOD) {
            return str_plural($this->model);
        }

        return $this->model;
    }
}