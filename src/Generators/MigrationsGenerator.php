<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use function PHPSTORM_META\type;
use RonasIT\Support\Events\SuccessCreateMessage;

class MigrationsGenerator extends EntityGenerator
{
    protected $migrations;

    public function generate()
    {
        $entities = $this->getTableName($this->model);

        $content = $this->getStub('migration', [
            'class' => $this->getPluralName($this->model),
            'entity' => $this->model,
            'entities' => $entities,
            'relations' => $this->relations,
            'fields' => $this->fields,
            'table' => $this->generateTable($this->fields)
        ]);
        $now = Carbon::now()->format('Y_m_d_His');

        $this->saveClass('migrations', "{$now}_create_{$entities}_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: create_{$entities}_table"));
    }

    protected function isJson($typeName)
    {
        return $typeName == 'json';
    }

    protected function isRequired($typeName)
    {
        return !empty(explode('-', $typeName)[1]);
    }

    protected function isNullable($typeName)
    {
        return empty(explode('-', $typeName)[1]);
    }

    protected function getJsonLine($fieldName)
    {
        if (env("DB_CONNECTION") == "mysql") {
            return '$table->json' . "('{$fieldName}')->nullable();";
        }

        return '$table->jsonb' . "('{$fieldName}')->default(\"{}\");";
    }

    protected function getRequiredLine($fieldName, $typeName)
    {
        $type = explode('-', $typeName)[0];
        return '$table->' . "{$type}('{$fieldName}');";
    }

    protected function getNonRequiredLine($fieldName, $typeName)
    {
        $type = explode('-', $typeName)[0];
        return '$table->' . "{$type}('{$fieldName}')->nullable();";
    }

    protected function generateTable($fields)
    {
        $resultTable = [];

        foreach ($fields as $typeName => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                array_push($resultTable, $this->getTableRow($fieldName, $typeName));
            }
        }

        return $resultTable;
    }

    protected function getTableRow($fieldName, $typeName)
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

        throw new Exception('Unknown fieldType in MigrationsGenerator');
    }
}