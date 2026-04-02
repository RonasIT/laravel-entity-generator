<?php

namespace RonasIT\EntityGenerator\Generators;

use Carbon\Carbon;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;
use RonasIT\EntityGenerator\Support\Fields\Field;

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
            'fields' => $this->prepareFields(),
        ]);

        $now = Carbon::now()->format('Y_m_d_His');

        $this->saveClass('migrations', "{$now}_{$entities}_create_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: {$entities}_create_table"));
    }

    protected function generateJsonDefinition(string $fieldName): string
    {
        if ($this->generateForMySQL()) {
            return "\$table->json('{$fieldName}')->nullable();";
        }

        return "\$table->jsonb('{$fieldName}')->default(\"{}\");";
    }

    protected function generateCommonFieldDefinition(Field $field): string
    {
        $columnModifiers = $this->getColumnModifiers($field);

        return "\$table->{$field->type->value}('{$field->name}'){$columnModifiers};";
    }

    protected function prepareFields(): array
    {
        return $this->fields->toNamedMap(fn (Field $field) => ($field->isJSON())
                ? $this->generateJsonDefinition($field->name)
                : $this->generateCommonFieldDefinition($field),
        );
    }

    protected function generateForMySQL(): bool
    {
        return env('DB_CONNECTION') === 'mysql';
    }

    protected function getColumnModifiers(Field $field): string
    {
        $result = '';

        if (!$field->isRequired() || ($field->isTimestamp() && $this->generateForMySQL())) {
            $result .= '->nullable()';
        }

        if ($field->isUnique()) {
            $result .= '->unique()';
        }

        return $result;
    }
}
