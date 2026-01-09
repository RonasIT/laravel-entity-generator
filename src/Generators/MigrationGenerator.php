<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
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
            'relations' => $this->prepareRelations(),
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

    protected function isRequired(array $modifiers): bool
    {
        return in_array('required', $modifiers);
    }

    protected function isNullable(array $modifiers): bool
    {
        return empty($modifiers);
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
        if ($typeName === 'timestamp' && env('DB_CONNECTION') === 'mysql') {
            return "\$table->{$typeName}('{$fieldName}')->nullable();";
        }

        return "\$table->{$typeName}('{$fieldName}');";
    }

    protected function getNonRequiredLine(string $fieldName, string $typeName): string
    {
        return "\$table->{$typeName}('{$fieldName}')->nullable();";
    }

    protected function generateTable(array $fields): array
    {
        $resultTable = [];

        foreach ($fields as $fieldType => $typedFields) {
            foreach ($typedFields as $field) {
                $resultTable[] = $this->getTableRow($fieldType, $field);
            }
        }

        return $resultTable;
    }

    protected function getTableRow(string $fieldType, array $field): string
    {
        if ($this->isJson($fieldType)) {
            return $this->getJsonLine($field['name']);
        }

        if ($this->isRequired($field['modifiers'])) {
            return $this->getRequiredLine($field['name'], $fieldType);
        }

        if ($this->isNullable($field['modifiers'])) {
            return $this->getNonRequiredLine($field['name'], $fieldType);
        }

        throw new UnknownFieldTypeException($fieldType, 'MigrationGenerator');
    }
}