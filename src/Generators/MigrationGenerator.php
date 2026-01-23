<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use RonasIT\Support\Collections\FieldsCollection;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\ValueObjects\Field;

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
            'table' => $this->generateTable($this->fields),
        ]);

        $now = Carbon::now()->format('Y_m_d_His');

        $this->saveClass('migrations', "{$now}_{$entities}_create_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: {$entities}_create_table"));
    }

    protected function isJson(FieldTypeEnum $type): bool
    {
        return $type === FieldTypeEnum::Json;
    }

    protected function isRequired(array $modifiers): bool
    {
        return in_array(FieldModifierEnum::Required, $modifiers);
    }

    protected function isNullable(array $modifiers): bool
    {
        return empty($modifiers);
    }

    protected function getJsonLine(string $fieldName): string
    {
        if (env('DB_CONNECTION') == 'mysql') {
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

    protected function generateTable(FieldsCollection $fields): array
    {
        $resultTable = [];

        foreach ($fields as $field) {
            $resultTable[] = $this->getTableRow($field->type, $field);
        }

        return $resultTable;
    }

    protected function getTableRow(FieldTypeEnum $fieldType, Field $field): string
    {
        return match (true) {
            $this->isJson($fieldType) => $this->getJsonLine($field->name),
            $this->isRequired($field->modifiers) => $this->getRequiredLine($field->name, $fieldType->value),
            $this->isNullable($field->modifiers) => $this->getNonRequiredLine($field->name, $fieldType->value),
        };
    }
}
