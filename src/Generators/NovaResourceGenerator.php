<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassNotExistsException;

class NovaResourceGenerator extends EntityGenerator
{
    protected $novaFieldTypesMap = [
        'boolean' => 'Boolean',
        'boolean-required' => 'Boolean',
        'timestamp' => 'DateTime',
        'timestamp-required' => 'DateTime',
        'string' => 'Text',
        'string-required' => 'Text',
        'json' => 'Text',
        'json-required' => 'Text',
        'integer' => 'Number',
        'integer-required' => 'Number',
        'float' => 'Number',
        'float-required' => 'Number'
    ];

    protected $specialFieldNamesMap = [
        'id' => 'ID',
        'country_code' => 'Country',
        'city' => 'City',
        'time_zone' => 'Timezone'
    ];

    public function generate()
    {
        if (file_exists($this->paths['nova-provider'])) {
            if (!$this->classExists('models', $this->model)) {
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create {$this->model} Model cause {$this->model} Model does not exists.",
                    "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'."
                );
            }

            $novaFields = $this->prepareNovaFields();

            $fileContent = $this->getStub('nova_resource', [
                'model' => $this->model,
                'fields' => $novaFields,
                'types' => array_unique($novaFields)
            ]);

            $this->saveClass('nova', "{$this->model}Resource", $fileContent);

            event(new SuccessCreateMessage("Created a new Nova Resource: {$this->model}Resource"));
        }
    }

    protected function prepareNovaFields(): array
    {
        $result = [];

        foreach ($this->fields as $fieldType => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                if (Arr::has($this->specialFieldNamesMap, $fieldName)) {
                    $result[$fieldName] = $this->specialFieldNamesMap[$fieldName];
                } else if (!Arr::has($this->novaFieldTypesMap, $fieldType)) {
                    event(new SuccessCreateMessage("Field '{$fieldName}' had been skipped cause has an unhandled type {$fieldType}."));
                } else {
                    $result[$fieldName] = $this->novaFieldTypesMap[$fieldType];
                }
            }
        }

        return $result;
    }
}