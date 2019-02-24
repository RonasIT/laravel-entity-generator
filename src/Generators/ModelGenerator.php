<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

class ModelGenerator extends EntityGenerator
{
    CONST PLURAL_NUMBER_REQUIRED = [
        'belongsToMany',
        'hasMany'
    ];

    public function generate()
    {
        if ($this->classExists('models', $this->model)) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model} Model cause {$this->model} Model already exists.",
                "Remove {$this->model} Model or run your command with options:'—without-model'."
            );
        }

        $this->prepareRelatedModels();
        $modelContent = $this->getNewModelContent();

        $this->saveClass('models', $this->model, $modelContent);

        event(new SuccessCreateMessage("Created a new Model: {$this->model}"));
    }

    protected function getNewModelContent()
    {
        return $this->getStub('model', [
            'entity' => $this->model,
            'fields' => array_collapse($this->fields),
            'relations' => $this->prepareRelations(),
            'casts' => $this->getCasts($this->fields)
        ]);
    }

    public function prepareRelatedModels()
    {
        $types = [
            'hasMany' => 'belongsTo',
            'hasOne' => 'belongsTo',
            'belongsTo' => 'hasOne',
            'belongsToMany' => 'belongsToMany',
        ];

        foreach ($this->relations as $type => $relationsByType) {
            foreach ($relationsByType as $relation) {
                if (!$this->classExists('models', $relation)) {
                    $this->throwFailureException(
                        ClassNotExistsException::class,
                        "Cannot create {$relation} Model cause {$relation} Model does not exists.",
                        "Create a {$relation} Model by himself or run command 'php artisan make:entity {$relation} --only-model'."
                    );
                }

                $content = $this->getModelContent($relation);

                $newRelation = $this->getStub('relation', [
                    'name' => $this->getRelationName($this->model, $type),
                    'type' => $types[$type],
                    'entity' => $this->model
                ]);

                $fixedContent = preg_replace('/\}$/', "\n    {$newRelation}\n}", $content);

                $this->saveClass('models', $relation, $fixedContent);
            }
        }
    }

    public function getModelContent($model)
    {
        $modelPath = base_path($this->paths['models'] . "/{$model}.php");

        return file_get_contents($modelPath);
    }

    public function prepareRelations()
    {
        $result = [];

        foreach ($this->relations as $type => $relations) {
            foreach ($relations as $relation) {
                if (!empty($relation)) {
                    $result[] = [
                        'name' => $this->getRelationName($relation, $type),
                        'type' => $type,
                        'entity' => $relation
                    ];
                }
            }
        }

        return $result;
    }

    protected function getCasts($fields)
    {
        $casts = [
            'boolean-required' => 'boolean',
            'boolean' => 'boolean',
            'json' => 'array'
        ];

        $result = [];

        foreach ($fields as $fieldType => $names) {
            if (empty($casts[$fieldType])) {
                continue;
            }

            foreach ($names as $name) {
                $result[$name] = $casts[$fieldType];
            }
        }

        return $result;
    }

    private function getRelationName($relation, $type)
    {
        $relationName = snake_case($relation);

        if (in_array($type, self::PLURAL_NUMBER_REQUIRED)) {
            $relationName = str_plural($relationName);
        }

        return $relationName;
    }
}