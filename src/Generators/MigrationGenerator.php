<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Support\Fields\Field;

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
        if (env('DB_CONNECTION') == 'mysql') {
            return "\$table->json('{$fieldName}')->nullable();";
        }

        return "\$table->jsonb('{$fieldName}')->default(\"{}\");";
    }

    protected function generateCommonFieldDefinition(Field $field): string
    {
        $nullablePart = (!$field->isRequired() || ($field->isTimestamp() && env('DB_CONNECTION') === 'mysql'))
            ? '->nullable()'
            : '';

        return "\$table->{$field->type->value}('{$field->name}'){$nullablePart};";
    }

    protected function prepareFields(): array
    {
        return $this->fields->toNamedMap(fn (Field $field) => ($field->isJSON())
                ? $this->generateJsonDefinition($field->name)
                : $this->generateCommonFieldDefinition($field)
        );
    }
}
