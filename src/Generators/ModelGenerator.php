<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

class ModelGenerator extends EntityGenerator
{
    protected const array PLURAL_NUMBER_REQUIRED = [
        'belongsToMany',
        'hasMany',
    ];

    public function generate(): void
    {
        if ($this->classExists('models', $this->model)) {
            $this->throwFailureException(
                exceptionClass: ClassAlreadyExistsException::class,
                failureMessage: "Cannot create {$this->model} Model cause {$this->model} Model already exists.",
                recommendedMessage: "Remove {$this->model} Model.",
            );
        }

        if ($this->isStubExists('model') && (!$this->hasRelations() || $this->isStubExists('relation', 'model'))) {
            $this->prepareRelatedModels();
            $modelContent = $this->getNewModelContent();

            $this->saveClass('models', $this->model, $modelContent);

            event(new SuccessCreateMessage("Created a new Model: {$this->model}"));
        }
    }

    protected function hasRelations(): bool
    {
        return !collect($this->relations)->every(fn ($relation) => empty($relation));
    }

    protected function getNewModelContent(): string
    {
        return $this->getStub('model', [
            'entity' => $this->model,
            'fields' => Arr::collapse($this->fields),
            'relations' => $this->prepareRelations(),
            'casts' => $this->getCasts($this->fields),
            'namespace' => $this->getOrCreateNamespace('models'),
        ]);
    }

    public function prepareRelatedModels(): void
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
                        exceptionClass: ClassNotExistsException::class,
                        failureMessage: "Cannot create {$this->model} Model cause relation model {$relation} does not exist.",
                        recommendedMessage: "Create the {$relation} Model by himself or run command 'php artisan make:entity {$relation} --only-model'.",
                    );
                }

                $content = $this->getModelContent($relation);

                $newRelation = $this->getStub('relation', [
                    'name' => $this->getRelationName($this->model, $types[$type]),
                    'type' => $types[$type],
                    'entity' => $this->model,
                ]);

                $fixedContent = preg_replace('/\}$/', "\n    {$newRelation}\n}", $content);

                $this->saveClass('models', $relation, $fixedContent);
            }
        }
    }

    public function getModelContent(string $model): string
    {
        $modelPath = base_path("{$this->paths['models']}/{$model}.php");

        return file_get_contents($modelPath);
    }

    public function prepareRelations(): array
    {
        $result = [];

        foreach ($this->relations as $type => $relations) {
            foreach ($relations as $relation) {
                if (!empty($relation)) {
                    $result[] = [
                        'name' => $this->getRelationName($relation, $type),
                        'type' => $type,
                        'entity' => $relation,
                    ];
                }
            }
        }

        return $result;
    }

    protected function getCasts(array $fields): array
    {
        $casts = [
            'boolean-required' => 'boolean',
            'boolean' => 'boolean',
            'json' => 'array'
        ];

        $result = [];

        foreach ($fields as $fieldType => $names) {
            if (!array_key_exists($fieldType, $casts)) {
                continue;
            }

            foreach ($names as $name) {
                $result[$name] = $casts[$fieldType];
            }
        }

        return $result;
    }

    private function getRelationName(string $relation, string $type): string
    {
        $relationName = Str::snake($relation);

        if (in_array($type, self::PLURAL_NUMBER_REQUIRED)) {
            $relationName = Str::plural($relationName);
        }

        return $relationName;
    }
}
