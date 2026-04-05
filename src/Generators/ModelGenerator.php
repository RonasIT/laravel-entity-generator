<?php

namespace RonasIT\EntityGenerator\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RonasIT\EntityGenerator\Enums\FieldTypeEnum;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;
use RonasIT\EntityGenerator\Support\Fields\Field;

class ModelGenerator extends EntityGenerator
{
    const array MODEL_CASTS_MAP = [
        FieldTypeEnum::Boolean->value => 'boolean',
        FieldTypeEnum::Json->value => 'array',
        FieldTypeEnum::Timestamp->value => 'datetime',
    ];

    const array PROPERTY_TYPES_MAP = [
        FieldTypeEnum::Integer->value => 'int',
        FieldTypeEnum::Float->value => 'float',
        FieldTypeEnum::String->value => 'string',
        FieldTypeEnum::Boolean->value => 'bool',
        FieldTypeEnum::Timestamp->value => 'Carbon',
        FieldTypeEnum::Json->value => 'array',
    ];

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
            'fields' => $this->fields->getNames(),
            'relations' => $relations,
            'casts' => $this->getCasts(),
            'namespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
            'importRelations' => $this->getImportedRelations(),
            'annotationProperties' => $this->generateAnnotationProperties($relations),
            'hasCarbonField' => $this->fields->hasTimestamps(),
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

    protected function getCasts(): array
    {
        return $this
            ->fields
            ->toNamedMap(fn (Field $field) => Arr::get(self::MODEL_CASTS_MAP, $field->type->value));
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

    protected function generateAnnotationProperties(array $relations): array
    {
        $result = $this
            ->fields
            ->toNamedMap(fn (Field $field) => $this->getFieldType($field));

        foreach ($relations as $relation) {
            $result[$relation['name']] = $this->getRelationType($relation['entity'], $relation['type']);
        }

        return array_merge(
            ['id' => self::PROPERTY_TYPES_MAP[FieldTypeEnum::Integer->value]],
            $result,
        );
    }

    protected function getFieldType(Field $field): string
    {
        $isNullable = !$field->isJSON() && !$field->isRequired();

        return $this->getProperty($field->type, $isNullable);
    }

    protected function getProperty(FieldTypeEnum $type, bool $isNullable = false): string
    {
        $type = self::PROPERTY_TYPES_MAP[$type->value];

        if ($isNullable) {
            $type .= '|null';
        }

        return $type;
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
