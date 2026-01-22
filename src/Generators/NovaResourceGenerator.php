<?php

namespace RonasIT\Support\Generators;

use Doctrine\DBAL\DriverManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\CommandLineNovaField;
use RonasIT\Support\Support\DatabaseNovaField;

class NovaResourceGenerator extends EntityGenerator
{
    protected $novaFieldTypesMap = [
        'boolean' => 'Boolean',
        'timestamp' => 'DateTime',
        'string' => 'Text',
        'json' => 'Text',
        'integer' => 'Number',
        'float' => 'Number',
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
        'time_zone' => 'Timezone',
    ];

    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            $this->checkResourceNotExists('models', "{$this->model}Resource", $this->model, $this->modelSubFolder);

            $this->checkResourceExists('nova', "{$this->model}Resource", $this->modelSubFolder);

            if (!$this->isStubExists('nova_resource')) {
                return;
            }

            $this->createNamespace('nova');

            $novaFields = $this->prepareNovaFields();

            $fileContent = $this->getStub('nova_resource', [
                'model' => $this->model,
                'fields' => $novaFields,
                'types' => array_unique(data_get($novaFields, '*.type')),
                'imports' => $this->getImports(),
                'namespace' => $this->generateNamespace($this->paths['nova'], $this->modelSubFolder),
            ]);

            $this->saveClass('nova', "{$this->model}Resource", $fileContent, $this->modelSubFolder);

            event(new SuccessCreateMessage("Created a new Nova Resource: {$this->model}Resource"));
        } else {
            event(new SuccessCreateMessage('Nova is not installed and NovaResource is skipped'));
        }
    }

    protected function prepareNovaFields(): array
    {
        $result = [];
        list($fields, $fieldTypesMap) = $this->getFieldsForCreation();

        foreach ($fields as $field) {
            if (Arr::has($this->specialFieldNamesMap, $field->name)) {
                $result[$field->name] = [
                    'type' => $this->specialFieldNamesMap[$field->name],
                    'is_required' => $field->isRequired,
                ];
            } else {
                $result[$field->name] = [
                    'type' => $fieldTypesMap[$field->type],
                    'is_required' => $field->isRequired,
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

        foreach ($this->fields as $commandLineField) {
            $fields[] = new CommandLineNovaField($commandLineField->type, $commandLineField);
        }

        return [$fields, $this->novaFieldTypesMap];
    }

    protected function getFieldsFromDatabase(): array
    {
        $modelClass = $this->getModelClass($this->model);
        $model = app($modelClass);

        $columns = $this->getColumnList($model->getTable(), $model->getConnectionName());

        $fields = array_map(function ($column) {
            return new DatabaseNovaField($column);
        }, $columns);

        return [$fields, $this->novaFieldsDatabaseMap];
    }

    protected function commandFieldsExists(): bool
    {
        return !empty(Arr::flatten($this->fields));
    }

    protected function getColumnList(string $table, ?string $connectionName = null): array
    {
        $config = DB::connection($connectionName)->getConfig();

        $dbalConnection = DriverManager::getConnection([
            'dbname' => $config['database'],
            'user' => $config['username'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => "pdo_{$config['driver']}",
        ]);

        return $dbalConnection
            ->createSchemaManager()
            ->listTableColumns($table);
    }

    protected function getImports(): array
    {
        $imports = [
            "{$this->generateNamespace($this->paths['models'], $this->modelSubFolder)}\\{$this->model}",
        ];

        if (!empty($this->modelSubFolder)) {
            $imports[] = "{$this->generateNamespace($this->paths['nova'])}\\Resource";
        }

        return $imports;
    }
}
