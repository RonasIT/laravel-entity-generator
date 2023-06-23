<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
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
        if (class_exists(NovaServiceProvider::class)) {
            if (!$this->classExists('models', $this->model)) {
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create {$this->model} Nova resource cause {$this->model} Model does not exists.",
                    "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'."
                );
            }

            $novaFields = $this->prepareNovaFields();

            $fileContent = $this->getStub('nova_resource', [
                'model' => $this->model,
                'fields' => $novaFields,
                'types' => array_unique(data_get($novaFields, '*.type'))
            ]);

            $this->saveClass('nova', "{$this->model}Resource", $fileContent);

            event(new SuccessCreateMessage("Created a new Nova Resource: {$this->model}Resource"));
        } else {
            event(new SuccessCreateMessage("Nova is not installed and NovaResource is skipped"));
        }
    }

    protected function prepareNovaFields(): array
    {
        $result = [];

        foreach ($this->fields as $fieldType => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                if (!Arr::has($this->novaFieldTypesMap, $fieldType)) {
                    event(new SuccessCreateMessage("Field '{$fieldName}' had been skipped cause has an unhandled type {$fieldType}."));
                } else if (Arr::has($this->specialFieldNamesMap, $fieldName)) {
                    $result[$fieldName] = [
                        'type' => $this->specialFieldNamesMap[$fieldName],
                        'is_required' => Str::contains($fieldType, 'required')
                    ];
                } else {
                    $result[$fieldName] = [
                        'type' => $this->novaFieldTypesMap[$fieldType],
                        'is_required' => Str::contains($fieldType, 'required')
                    ];
                }
            }
        }

        return $result;
    }
}