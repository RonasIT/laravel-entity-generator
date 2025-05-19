<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\UnknownFieldTypeException;

class MigrationGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if (!$this->isStubExists('migration')) {
            return;
        }

        $entities = $this->getTableName($this->model);

        $content = $this->getStub('migration', [
            'class' => $this->getPluralName($this->model),
            'entity' => $this->model,
            'entities' => $entities,
            'relations' => $this->relations->toArray(),
            'fields' => $this->fields,
            'table' => $this->generateTable($this->fields)
        ]);

        $now = Carbon::now()->format('Y_m_d_His');

        $this->saveClass('migrations', "{$now}_{$entities}_create_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: {$entities}_create_table"));
    }

    protected function isJson(string $typeName): bool
    {
        return $typeName === 'json';
    }

    protected function isRequired(string $typeName): bool
    {
        return Str::afterLast($typeName, '-') === 'required';
    }

    protected function isNullable(string $typeName): bool
    {
        return empty(explode('-', $typeName)[1]);
    }

    protected function getJsonLine(string $fieldName): string
    {
        if (env("DB_CONNECTION") == "mysql") {
            return "\$table->json('{$fieldName}')->nullable();";
        }

        return "\$table->jsonb('{$fieldName}')->default(\"{}\");";
    }

    protected function getRequiredLine(string $fieldName, string $typeName): string
    {
        $type = explode('-', $typeName)[0];

        if ($type === 'timestamp' && env('DB_CONNECTION') === 'mysql') {
            return "\$table->{$type}('{$fieldName}')->nullable();";
        }

        return "\$table->{$type}('{$fieldName}');";
    }

    protected function getNonRequiredLine(string $fieldName, string $typeName): string
    {
        $type = explode('-', $typeName)[0];

        return "\$table->{$type}('{$fieldName}')->nullable();";
    }

    protected function generateTable(array $fields): array
    {
        $resultTable = [];

        foreach ($fields as $typeName => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                $resultTable[] = $this->getTableRow($fieldName, $typeName);
            }
        }

        return $resultTable;
    }

    protected function getTableRow(string $fieldName, string $typeName): string
    {
        if ($this->isJson($typeName)) {
            return $this->getJsonLine($fieldName);
        }

        if ($this->isRequired($typeName)) {
            return $this->getRequiredLine($fieldName, $typeName);
        }

        if ($this->isNullable($typeName)) {
            return $this->getNonRequiredLine($fieldName, $typeName);
        }

        throw new UnknownFieldTypeException($typeName, 'MigrationGenerator');
    }
}