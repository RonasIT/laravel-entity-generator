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

    const JSON_FIELDS = ['json'];

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
        return in_array($typeName, self::JSON_FIELDS);
    }

    protected function isRequired($typeName)
    {
        if (empty(explode('-', $typeName)[1])) {
            return false;
        }

        return true;
    }

    protected function isNonRequired($typeName)
    {
        if (!empty(explode('-', $typeName)[1])) {
            return false;
        }

        return true;
    }

    protected function getJsonLine($fieldName, $typeName)
    {
        if (env("DB_CONNECTION") == "mysql") {
            return '$table->' . explode('-', $typeName)[0] . "({$fieldName})->nullable();";

        }
        return '$table->' . explode('-', $typeName)[0] . "({$fieldName})->default(\"{}\");";

    }

    protected function getRequiredLine($fieldName, $typeName)
    {
        return '$table->' . explode('-', $typeName)[0] . "({$fieldName});";
    }

    protected function getNonRequiredLine($fieldName, $typeName)
    {
        return '$table->' . explode('-', $typeName)[0] . "({$fieldName})->nullable();";
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
            return $this->getJsonLine($fieldName, $typeName);
        }

        if ($this->isRequired($typeName)) {
            return $this->getRequiredLine($fieldName, $typeName);
        }

        if ($this->isNonRequired($typeName)) {
            return $this->getNonRequiredLine($fieldName, $typeName);
        }

        $message = 'Unknown fieldType in MigrationsGenerator';
        throw new Exception($message);
    }

}