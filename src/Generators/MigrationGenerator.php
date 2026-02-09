<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\Fields\Field;
use RonasIT\Support\Support\Fields\FieldsCollection;

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

    protected function generateJsonLine(string $fieldName): string
    {
        if (env('DB_CONNECTION') == 'mysql') {
            return "\$table->json('{$fieldName}')->nullable();";
        }

        return "\$table->jsonb('{$fieldName}')->default(\"{}\");";
    }

    protected function generateFieldLine(Field $field): string
    {
        if ($field->type === FieldTypeEnum::Timestamp && env('DB_CONNECTION') === 'mysql') {
            return "\$table->{$field->type->value}('{$field->name}')->nullable();";
        }

        $nullablePart = ($field->isRequired()) ? '' : '->nullable()';

        return "\$table->{$field->type->value}('{$field->name}'){$nullablePart};";
    }

    protected function generateTable(FieldsCollection $fields): array
    {
        $resultTable = [];

        foreach ($fields as $field) {
            $resultTable[] = $this->getTableRow($field);
        }

        return $resultTable;
    }

    protected function getTableRow(Field $field): string
    {
        return ($field->isJSON())
            ? $this->generateJsonLine($field->name)
            : $this->generateFieldLine($field);
    }
}
