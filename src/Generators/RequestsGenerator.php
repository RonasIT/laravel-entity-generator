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

    public function setRelations($relations)
    {
        parent::setRelations($relations);

        $this->relations['belongsTo'] = array_map(function ($field) {
            return Str::snake($field) . '_id';
        }, $this->relations['belongsTo']);

        return $this;
    }

    public function generate()
    {
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

            if (!empty($this->fields['boolean-required'])) {
                $this->fields['boolean|present'] = $this->fields['boolean-required'];

                Arr::forget($this->fields, 'boolean-required');
            }

            $this->createRequest(
                self::CREATE_METHOD,
                false,
                $this->getValidationParameters($this->fields, true)
            );
        }

        if (in_array('U', $this->crudOptions)) {

            if (!empty($this->fields['boolean|present'])) {
                $this->fields['boolean'] = array_merge($this->fields['boolean'], $this->fields['boolean|present']);
                Arr::forget($this->fields, 'boolean|present');
            }

            $this->createRequest(
                self::UPDATE_METHOD,
                true,
                $this->getValidationParameters($this->fields, false)
            );
        }
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

        $parameters['string-required'] = ['with.*'];

        return $this->getValidationParameters($parameters, true);
    }

    protected function getSearchValidationParameters()
    {
        $parameters = Arr::except($this->fields, [
            'timestamp', 'timestamp-required', 'string-required', 'integer-required'
        ]);

        $parameters['integer'] = array_merge($this->fields['integer'], [
            'page', 'per_page', 'all',
        ]);

        $parameters['array'] = ['with'];

        $parameters['boolean'] = ['desc'];

        $parameters['string'] = ['order_by'];

        $parameters['string-nullable'] = ['query'];

        $parameters['string-required'] = ['with.*'];

        return $this->getValidationParameters($parameters, false);
    }

    public function getValidationParameters($parameters, $requiredAvailable)
    {
        $result = [];

        foreach ($parameters as $type => $parameterNames) {
            $isRequired = Str::contains($type, 'required');
            $isNullable = Str::contains($type, 'nullable');
            $type = head(explode('-', $type));

            foreach ($parameterNames as $name) {
                $required = $isRequired && $requiredAvailable;
                $nullable = $isNullable;

                $result[] = $this->getRules($name, $type, $required, $nullable);
            }
        }

        return $result;
    }

    protected function getRules($name, $type, $required, $nullable)
    {
        $replaces = [
            'timestamp' => 'date',
            'float' => 'numeric',
            'json' => 'array'
        ];

        $rules = [
            Arr::get($replaces, $type, $type)
        ];

        if (in_array($name, $this->relations['belongsTo'])) {
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

        return [
            'name' => $name,
            'rules' => $rules
        ];
    }

    private function getEntityName($method) {
        if ($method === self::SEARCH_METHOD) {
            return Str::plural($this->model);
        }

        return $this->model;
    }
}
