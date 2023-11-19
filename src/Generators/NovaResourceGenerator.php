<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
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

    protected $novaFieldsDatabaseMap = [
        'integer' => 'Number',
        'smallint' => 'Number',
        'bigint' => 'Number',
        'float' => 'Number',
        'decimal' => 'Number',
        'string' => 'Text',
        'text' => 'Text',
        'guid' => 'Text',
        'json' => 'Text',
        'date' => 'Date',
        'datetime' => 'DateTime',
        'datetimetz' => 'DateTime',
        'boolean' => 'Boolean',
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

            if ($this->classExists('nova', "{$this->model}Resource")) {
                $this->throwFailureException(
                    ClassAlreadyExistsException::class,
                    "Cannot create {$this->model}Resource cause {$this->model}Resource already exists.",
                    "Remove {$this->model}Resource."
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
        if (empty($this->fields)) {
            return $this->prepareFieldsFromDB();
        }

        return $this->prepareFieldsFromCommand();
    }

    protected function prepareFieldsFromCommand(): array
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

    protected function prepareFieldsFromDB(): array
    {
        $modelClass = "App\Models\{$this->model}";
        $tableName = app($modelClass)->getTable();
        $columns = DB::getDoctrineSchemaManager($tableName);
        $result = [];

        foreach ($columns as $column) {
            $fieldType = $column->getType()->getName();
            $fieldName = $column->getName();

            if (!Arr::has($this->novaFieldsDatabaseMap, $fieldType)) {
                event(new SuccessCreateMessage("Field '{$fieldName}' had been skipped cause has an unhandled type {$fieldType}."));

                continue;
            }

            $result[$fieldName] = [
                'type' => $this->novaFieldsDatabaseMap[$fieldType],
                'is_required' => $column->getNotNull()
            ];
        }

        return $result;
    }
}