<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.10.16
 * Time: 8:55
 */

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;

class RequestsGenerator extends EntityGenerator
{
    public function setRelations($relations)
    {
        parent::setRelations($relations);

        $this->relations['belongsTo'] = array_map(function ($field) {
            return snake_case($field).'_id';
        }, $this->relations['belongsTo']);

        return $this;
    }

    public function generate() {
        $this->createRequest('Get');
        $this->createRequest('Delete');

        $this->createRequest(
            'Create',
            false,
            $this->getValidationParameters($this->fields, true)
        );

        $this->createRequest(
            'Update',
            true,
            $this->getValidationParameters($this->fields, false)
        );

        $this->createRequest(
            'Search',
            false,
            $this->getSearchValidationParameters()
        );
    }

    protected function createRequest($method, $needToValidate = true, $parameters = []) {
        $content = $this->getStub('request', [
            'method' => $method,
            'entity' => $this->model,
            'parameters' => $parameters,
            'needToValidate' => $needToValidate
        ]);

        $this->saveClass('requests', "{$method}{$this->model}Request", $content);

        event(new SuccessCreateMessage("Created a new Request: {$method}{$this->model}Request"));
    }

    protected function getSearchValidationParameters() {
        $parameters = array_except($this->fields, ['timestamp', 'timestamp-required', 'string-required']);

        $parameters['integer'] = array_merge($this->fields['integer'], [
            'page', 'per_page', 'all',
        ]);

        $parameters['string'] = ['query'];

        return $this->getValidationParameters($parameters, false);
    }

    public function getValidationParameters($parameters, $requiredAvailable) {
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

    protected function getRules($name, $type, $required) {
        $replaces = [
            'timestamp' => 'date',
            'float' => 'numeric',
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
        }

        return [
            'name' => $name,
            'rules' => $rules
        ];
    }
}