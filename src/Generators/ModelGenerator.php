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
        if ($this->classExists('model_entity', $this->model)) {
            $this->throwFailureException(
                exceptionClass: ClassAlreadyExistsException::class,
                failureMessage: "Cannot create {$this->model} Model cause {$this->model} Model already exists.",
                recommendedMessage: "Remove {$this->model} Model.",
            );
        }

        if ($this->isStubExists('model') && (!$this->hasRelations() || $this->isStubExists('relation', 'model'))) {
            $this->prepareRelatedModels();
            $modelContent = $this->getNewModelContent();

            $this->saveClass('model_entity', $this->model, $modelContent);

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
            'namespace' => $this->getOrCreateNamespace('model_entity'),
            'importRelations' => $this->getImportRelations(),
            'anotationProperties' => $this->generateAnnotationProperties($this->fields),
            'hasCarbonField' => !empty($this->fields['timestamp']) || !empty($this->fields['timestamp-required']),
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

                if ($this->shouldImportRelation($relation, 'model_entity')) {
                    $importRelation = $this->buildImportRelation($this->model, 'model_entity');
                    $content = $this->insertUseForRelation($content, $importRelation);
                }

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

    function insertUseForRelation(string $content, string $class): string
    {
        $newUse = "use {$class};";

        if (Str::contains($content, $newUse)) {
            return $content;
        }

        $content = preg_replace('/(namespace\s+[^;]+;\s*)/', "$1{$newUse}\n", $content, 1);

        return $content;
    }

    public function prepareRelations(): array
    {
        $result = [];

        foreach ($this->relations as $type => $relations) {
            foreach ($relations as $relation) {
                if (!empty($relation)) {

                    $entity = class_basename($relation);

                    $result[] = [
                        'name' => $this->getRelationName($entity, $type),
                        'type' => $type,
                        'entity' => $entity,
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
            'json' => 'array',
            'timestamp-required' => 'datetime',
            'timestamp' => 'datetime'
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

    protected function getImportRelations(): array
    {
        $result = [];

        foreach ($this->relations as $relations) {
            foreach ($relations as $relation) {
                if (!empty($relation) && $this->shouldImportRelation($relation, 'model_entity')) {
                    $result[] = $this->buildImportRelation($relation, 'models');
                }
            }
        }

        return array_unique($result);
    }

    protected function shouldImportRelation(string $relation, string $path): bool
    {
        list(, $namespace) = extract_last_part($relation, '/');

        $namespace = Str::trim($namespace);

        $currentModelPath = rtrim("{$this->paths['models']}/{$namespace}", '/');

        return $currentModelPath !== $this->paths[$path];
    }

    protected function buildImportRelation(string $relation, string $path): string
    {
        $importBase = $this->getOrCreateNamespace($path);
        $normalizedRelation = Str::replace('/', '\\', Str::trim($relation, '/'));

        return "{$importBase}\\{$normalizedRelation}";
    }

    protected function generateAnnotationProperties(array $fields): array
    {
        $result = [];

        foreach ($fields as $typeName => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                $result[$fieldName] = $this->getFieldType($typeName);
            }
        }

        return $result;
    }

    protected function getFieldType(string $fieldType): string
    {
        $isNullable = !$this->isJson($fieldType) && !$this->isRequired($fieldType);

        return $this->getProperty($fieldType, $isNullable);
    }
    
    protected function getProperty(string $typeName, bool $isNullable = false): string
    {
        $typesMap = [
            'integer' => 'int',
            'float' => 'float',
            'string' => 'string',
            'boolean' => 'bool',
            'timestamp' => 'Carbon',
            'json' => 'array',
        ];

        $type = $typesMap[Str::before($typeName, '-')];

        if ($isNullable) {
            $type .= '|null';
        }

        return $type;
    }

    protected function isJson(string $typeName): bool
    {
        return $typeName === 'json';
    }

    protected function isRequired(string $typeName): bool
    {
        return Str::endsWith($typeName, 'required');
    }
}
