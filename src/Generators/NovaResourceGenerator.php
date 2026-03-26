<?php

namespace RonasIT\Support\Generators;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\Fields\Field;

class NovaResourceGenerator extends EntityGenerator
{
    protected $novaFieldTypesMap = [
        FieldTypeEnum::Boolean->value => 'Boolean',
        FieldTypeEnum::Timestamp->value => 'DateTime',
        FieldTypeEnum::String->value => 'Text',
        FieldTypeEnum::Json->value => 'Text',
        FieldTypeEnum::Integer->value => 'Number',
        FieldTypeEnum::Float->value => 'Number',
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

            $novaFields = $this->prepareFields();

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

    protected function prepareFields(): array
    {
        if (!$this->fields->isEmpty()) {
            return $this
                ->fields
                ->toNamedMap(fn (Field $field) => $this->getCommandFieldData($field));
        }

        return $this->getFieldsFromDatabase();
    }

    protected function getFieldsFromDatabase(): array
    {
        $model = app($this->getModelClass($this->model));

        return Arr::mapWithKeys(
            array: $this->getColumnList($model->getTable(), $model->getConnectionName()),
            callback: fn (Column $column) => [$column->getName() => $this->getDatabaseFieldData($column)],
        );
    }

    protected function getCommandFieldData(Field $field): array
    {
        return [
            'type' => $this->specialFieldNamesMap[$field->name] ?? $this->novaFieldTypesMap[$field->type->value],
            'is_required' => $field->isRequired(),
        ];
    }

    protected function getDatabaseFieldData(Column $field): array
    {
        $type = strtolower($field->getType()->getBindingType()->name);

        return [
            'type' => $this->specialFieldNamesMap[$field->getName()] ?? $this->novaFieldsDatabaseMap[$type],
            'is_required' => $field->getNotNull(),
        ];
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
