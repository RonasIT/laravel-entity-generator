<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Support\CommandLineNovaField;
use RonasIT\Support\Support\DatabaseNovaField;

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

    public function generate(): void
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
                'types' => array_unique(data_get($novaFields, '*.type')),
                'modelNamespace' => $this->getOrCreateNamespace('models'),
                'namespace' => $this->getOrCreateNamespace('nova')
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
        list($fields, $fieldTypesMap) = $this->getFieldsForCreation();

        foreach ($fields as $field) {
            if (!Arr::has($fieldTypesMap, $field->type)) {
                event(new SuccessCreateMessage("Field '{$field->name}' had been skipped cause has an unhandled type {$field->type}."));
            } else if (Arr::has($this->specialFieldNamesMap, $field->name)) {
                $result[$field->name] = [
                    'type' => $this->specialFieldNamesMap[$field->name],
                    'is_required' => $field->isRequired
                ];
            } else {
                $result[$field->name] = [
                    'type' => $fieldTypesMap[$field->type],
                    'is_required' => $field->isRequired
                ];
            }
        }

        return $result;
    }

    protected function getFieldsForCreation(): array
    {
        if ($this->commandFieldsExists()) {
            return $this->getFieldsFromCommandLineArguments();
        }

        return $this->getFieldsFromDatabase();
    }

    protected function getFieldsFromCommandLineArguments(): array
    {
        $fields = [];

        foreach ($this->fields as $type => $names) {
            foreach ($names as $name) {
                $fields[] = new CommandLineNovaField($type, $name);
            }
        }

        return [$fields, $this->novaFieldTypesMap];
    }

    protected function getFieldsFromDatabase(): array
    {
        $modelClass = "App\\Models\\{$this->model}";
        $model = app($modelClass);
        $columns = DB::connection($model->getConnectionName())
            ->getDoctrineSchemaManager()
            ->listTableColumns($model->getTable());

        $fields = array_map(function ($column) {
            return new DatabaseNovaField($column);
        }, $columns);

        return [$fields, $this->novaFieldsDatabaseMap];
    }

    protected function commandFieldsExists(): bool
    {
        return !empty(Arr::flatten($this->fields));
    }
}
