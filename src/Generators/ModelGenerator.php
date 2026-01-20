<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\Support\Collections\FieldsCollection;
use RonasIT\Support\Enums\FieldModifierEnum;
use RonasIT\Support\Enums\FieldTypeEnum;
use RonasIT\Support\Events\SuccessCreateMessage;

class ModelGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $this->checkResourceExists('models', $this->model, $this->modelSubFolder);

        if ($this->isStubExists('model') && (!$this->hasRelations() || $this->isStubExists('relation', 'model'))) {
            $this->createNamespace('models', $this->modelSubFolder);

            $this->prepareRelatedModels();
            $modelContent = $this->getNewModelContent();

            $this->saveClass('models', $this->model, $modelContent, $this->modelSubFolder);

            event(new SuccessCreateMessage("Created a new Model: {$this->model}"));
        }
    }

    protected function hasRelations(): bool
    {
        return !collect($this->relations)->every(fn ($relation) => empty($relation));
    }

    protected function getNewModelContent(): string
    {
        $relations = $this->prepareRelations();

        return $this->getStub('model', [
            'entity' => $this->model,
            'fields' => Arr::pluck($this->fields->getFields(), 'name'),
            'relations' => $relations,
            'casts' => $this->getCasts($this->fields),
            'namespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
            'importRelations' => $this->getImportedRelations(),
            'annotationProperties' => $this->generateAnnotationProperties($this->fields, $relations),
            'hasCarbonField' => !empty($this->fields->getFieldsByType(FieldTypeEnum::Timestamp)),
            'hasCollectionType' => !empty($this->relations->hasMany) || !empty($this->relations->belongsToMany),
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
                $this->checkResourceNotExists('models', $this->model, $relation);

                $content = $this->getModelContent($relation);

                if ($this->shouldImportRelation($relation)) {
                    $namespace = $this->generateClassNamespace($this->model, $this->modelSubFolder);
                    $this->insertImport($content, $namespace);
                }

                $relationName = $this->getRelationName($this->model, $types[$type]);

                $newRelation = $this->getStub('relation', [
                    'name' => $relationName,
                    'type' => $types[$type],
                    'entity' => $this->model,
                ]);

                // TODO: use ronasit/larabuilder instead regexp
                $fixedContent = preg_replace('/\}$/', "\n    {$newRelation}\n}", $content);

                $this->insertImport($fixedContent, 'Illuminate\Database\Eloquent\Relations\\' . Str::ucfirst($types[$type]));

                $this->insertPropertyAnnotation($fixedContent, $this->getRelationType($this->model, $types[$type]), $relationName);

                $this->saveClass('models', $relation, $fixedContent);
            }
        }
    }

    public function getModelContent(string $model): string
    {
        $modelPath = base_path("{$this->paths['models']}/{$model}.php");

        return file_get_contents($modelPath);
    }

    protected function insertImport(string &$classContent, string $import): void
    {
        $import = "use {$import};";

        if (!Str::contains($classContent, $import)) {
            // TODO: use ronasit/larabuilder instead regexp
            $classContent = preg_replace('/(namespace\s+[^;]+;\s*)/', "$1{$import}\n", $classContent, 1);
        }
    }

    protected function prepareRelations(): array
    {
        $result = [];

        foreach ($this->relations as $type => $relations) {
            foreach ($relations as $relation) {
                $relation = class_basename($relation);

                $result[] = [
                    'name' => $this->getRelationName($relation, $type),
                    'type' => $type,
                    'entity' => $relation,
                ];
            }
        }

        return $result;
    }

    protected function getCasts(FieldsCollection $fields): array
    {
        $casts = [
            'boolean' => 'boolean',
            'json' => 'array',
            'timestamp' => 'datetime',
        ];

        $result = [];

        foreach ($fields as $field) {
            if (!array_key_exists($field->type->value, $casts)) {
                continue;
            }

            $result[$field->name] = $casts[$field->type->value];
        }

        return $result;
    }

    protected function getImportedRelations(): array
    {
        $result = [];

        foreach ($this->relations as $relationType => $relations) {
            foreach ($relations as $relation) {
                if ($this->shouldImportRelation($relation)) {
                    $result[] = $this->generateClassNamespace($relation);
                }

                $result[] = 'Illuminate\Database\Eloquent\Relations\\' . Str::ucfirst($relationType);
            }
        }

        return array_unique($result);
    }

    protected function shouldImportRelation(string $relation): bool
    {
        $relationNamespace = when(Str::contains($relation, '/'), fn () => Str::beforeLast($relation, '/'), '');

        return $relationNamespace !== $this->modelSubFolder;
    }

    protected function generateClassNamespace(string $className, ?string $folder = null): string
    {
        $path = $this->generateNamespace($this->paths['models'], $folder);
        $psrPath = $this->pathToNamespace($className);

        return "{$path}\\{$psrPath}";
    }

    protected function generateAnnotationProperties(FieldsCollection $fields, array $relations): array
    {
        $result = [];

        foreach ($fields as $field) {
            $result[$field->name] = $this->getFieldType($field->type, $field->modifiers);
        }

        foreach ($relations as $relation) {
            $result[$relation['name']] = $this->getRelationType($relation['entity'], $relation['type']);
        }

        return $result;
    }

    protected function getFieldType(FieldTypeEnum $fieldType, array $modifiers): string
    {
        $isNullable = !$this->isJson($fieldType) && !$this->isRequired($modifiers);

        return $this->getProperty($fieldType, $isNullable);
    }

    protected function getProperty(FieldTypeEnum $type, bool $isNullable = false): string
    {
        $typesMap = [
            'integer' => 'int',
            'float' => 'float',
            'string' => 'string',
            'boolean' => 'bool',
            'timestamp' => 'Carbon',
            'json' => 'array',
        ];

        $type = $typesMap[$type->value];

        if ($isNullable) {
            $type .= '|null';
        }

        return $type;
    }

    protected function isJson(FieldTypeEnum $type): bool
    {
        return $type === FieldTypeEnum::Json;
    }

    protected function isRequired(array $modifiers): bool
    {
        return in_array(FieldModifierEnum::Required, $modifiers);
    }

    protected function getRelationType(string $model, string $relation): string
    {
        if ($this->isPluralRelation($relation)) {
            return "Collection<{$model}>";
        }

        return "{$model}|null";
    }

    protected function insertPropertyAnnotation(string &$content, string $propertyDataType, string $propertyName): void
    {
        $annotation = "* @property {$propertyDataType} \${$propertyName}";

        // TODO: use ronasit/larabuilder instead regexp
        if (!Str::contains($content, '/**')) {
            $content = preg_replace('/^\s*class[\s\S]+?\{/m', "\n/**\n {$annotation}\n */$0", $content);
        } else {
            $content = preg_replace('/\*\//m', "{$annotation}\n $0", $content);
        }

        if (Str::contains($propertyDataType, 'Collection')) {
            $this->insertImport($content, 'Illuminate\Database\Eloquent\Collection');
        }
    }
}
