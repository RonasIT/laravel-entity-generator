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
        'boolean' => [
            'type' => 'Boolean',
            'is_required' => false
        ],
        'boolean-required' => [
            'type' => 'Boolean',
            'is_required' => true
        ],
        'timestamp' => [
            'type' => 'DateTime',
            'is_required' => false
        ],
        'timestamp-required' => [
            'type' => 'DateTime',
            'is_required' => true
        ],
        'string' => [
            'type' => 'Text',
            'is_required' => false
        ],
        'string-required' => [
            'type' => 'Text',
            'is_required' => true
        ],
        'json' => [
            'type' => 'Text',
            'is_required' => false
        ],
        'json-required' => [
            'type' => 'Text',
            'is_required' => true
        ],
        'integer' => [
            'type' => 'Number',
            'is_required' => false
        ],
        'integer-required' => [
            'type' => 'Number',
            'is_required' => true
        ],
        'float' => [
            'type' => 'Number',
            'is_required' => false
        ],
        'float-required' => [
            'type' => 'Number',
            'is_required' => true
        ]
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
                    $result[$fieldName] = $this->novaFieldTypesMap[$fieldType];
                }
            }
        }

        return $result;
    }
}