<?php

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;

class MigrationsGenerator extends EntityGenerator
{
    protected $migrations;

    const SPECIAL_BEHAVIOR_REQUIRED_FIELDS = ['json', 'json-required'];

    const KNOWN_TYPES_OF_BEHAVIOR = [
        'defaultNonRequiredKeys',
        'defaultRequiredKeys',
        'RequiredJson',
        'NonRequiredJson',
    ];

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

    protected function generateTable($fields)
    {
        $resultTable = [];

        $handlers = $this->loadHandlers();

        foreach ($fields as $typeName => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                array_push($resultTable, $handlers[$typeName]($fieldName, $typeName));
            }
        }

        return $resultTable;
    }

    protected function loadHandlers()
    {
        $loadedHandlers = [];

        $fieldTypes = $this->splitFieldTypesByBehavior();
        $handlers = $this->getTableGenerationHandlers();

        foreach (self::KNOWN_TYPES_OF_BEHAVIOR as $behaior) {
            foreach ($fieldTypes[$behaior] as $fieldType) {
                $loadedHandlers[$fieldType] = $handlers[$behaior];
            }
        }
        return $loadedHandlers;
    }


    protected function getTableGenerationHandlers()
    {
        $handlers = [];

        $handlers[self::KNOWN_TYPES_OF_BEHAVIOR[0]] = function ($fieldName, $typeName) {
            return '$table->' . explode('-', $typeName)[0] . "({$fieldName})->nullable();";
        };

        $handlers[self::KNOWN_TYPES_OF_BEHAVIOR[1]] = function ($fieldName, $typeName) {
            return '$table->' . explode('-', $typeName)[0] . "({$fieldName});";
        };

        $handlers[self::KNOWN_TYPES_OF_BEHAVIOR[2]] = function ($fieldName, $typeName) {
            if (env("DB_CONNECTION") != "mysql") {
                return
                    '$table->' . explode('-', $typeName)[0] . "({$fieldName})->default(\"{}\")->nullable();";
            }

            return '$table->' . explode('-', $typeName)[0] . "({$fieldName})->nullable();";
        };

        $handlers[self::KNOWN_TYPES_OF_BEHAVIOR[3]] = function ($fieldName, $typeName) {
            if (env("DB_CONNECTION") == "mysql") {
                return '$table->' . explode('-', $typeName)[0] . "({$fieldName})->default(\"{}\");";
            }

            return '$table->' . explode('-', $typeName)[0] . "({$fieldName});";
        };

        return $handlers;
    }

    protected function splitFieldTypesByBehavior()
    {
        $splittedKeys = $this->splitRequireNonRequireFields();

        $keys = [];

        $keys[self::KNOWN_TYPES_OF_BEHAVIOR[0]] = [];
        $keys[self::KNOWN_TYPES_OF_BEHAVIOR[1]] = [];
        $keys[self::KNOWN_TYPES_OF_BEHAVIOR[2]] = [];
        $keys[self::KNOWN_TYPES_OF_BEHAVIOR[3]] = [];

        foreach ($splittedKeys['keysWithoutRequire'] as $key) {
            if (!array_search($key, self::SPECIAL_BEHAVIOR_REQUIRED_FIELDS)) {
                array_push($keys[self::KNOWN_TYPES_OF_BEHAVIOR[0]], $key);
            } else {
                array_push($keys[self::KNOWN_TYPES_OF_BEHAVIOR[1]], $key);
            }
        }

        foreach ($splittedKeys['keysWithRequire'] as $key) {
            if (!array_search($key, self::SPECIAL_BEHAVIOR_REQUIRED_FIELDS)) {
                array_push($keys[self::KNOWN_TYPES_OF_BEHAVIOR[2]], $key);
            } else {
                array_push($keys[self::KNOWN_TYPES_OF_BEHAVIOR[3]], $key);
            }
        }

        return $keys;
    }

    protected function splitRequireNonRequireFields()
    {
        $splittedKeys = [];

        $requireFilter = function ($typeName) {
            if (!empty(explode('-', $typeName)[1])) {
                return $typeName;
            }
        };
        $nonRequireFilter = function ($typeName) {
            if (empty(explode('-', $typeName)[1])) {
                return $typeName;
            }
        };

        $splittedKeys['keysWithoutRequire'] = array_filter(self::AVAILABLE_FIELDS, $nonRequireFilter);
        $splittedKeys['keysWithRequire'] = array_filter(self::AVAILABLE_FIELDS, $requireFilter);

        return $splittedKeys;
    }
}