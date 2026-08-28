<?php

namespace RonasIT\EntityGenerator\Generators;

use Illuminate\Support\Str;
use RonasIT\EntityGenerator\Enums\ReservedFieldEnum;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;

class ResourceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if (!empty(array_intersect(['C', 'R'], $this->crudOptions)) && $this->isStubExists('resource')) {
            $this->createNamespace('resources');

            $this->generateResource();

            if (in_array('R', $this->crudOptions) && $this->isStubExists('collection_resource')) {
                $this->generateCollectionResource();
            }
        }
    }

    public function generateCollectionResource(): void
    {
        $pluralName = $this->getPluralName($this->model);

        $this->checkResourceExists('resources', "{$this->model}/{$pluralName}CollectionResource");

        $collectionResourceContent = $this->getStub('collection_resource', [
            'singular_name' => $this->model,
            'plural_name' => $pluralName,
            'namespace' => $this->generateNamespace($this->paths['resources']),
        ]);

        $this->saveClass('resources', "{$pluralName}CollectionResource", $collectionResourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new CollectionResource: {$pluralName}CollectionResource"));
    }

    public function generateResource(): void
    {
        $this->checkResourceExists('resources', "{$this->model}/{$this->model}Resource");

        $relations = $this->getResourceRelations();

        $this->checkRelationResourcesExist($relations);

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model,
            'namespace' => $this->generateNamespace($this->paths['resources']),
            'model_namespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
            'fields' => $this->getResourceFields($relations),
            'relations' => $relations,
            'relation_imports' => $this->getRelationImports($relations),
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent, $this->model);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }

    protected function getResourceRelations(): array
    {
        $result = [];

        foreach ($this->relations as $type => $relations) {
            foreach ($relations as $relation) {
                $entity = class_basename($relation);

                $result[] = [
                    'name' => $this->getRelationName($entity, $type),
                    'entity' => $entity,
                    'resource' => $this->getRelationResourceName($entity, $type),
                ];
            }
        }

        return $result;
    }

    protected function getRelationResourceName(string $entity, string $type): string
    {
        return ($this->isPluralRelation($type))
            ? Str::plural($entity) . 'CollectionResource'
            : "{$entity}Resource";
    }

    protected function checkRelationResourcesExist(array $relations): void
    {
        $generatedResources = $this->getGeneratedResourceNames();

        foreach ($relations as $relation) {
            $requiredResource = "{$relation['entity']}/{$relation['resource']}";

            if (in_array($requiredResource, $generatedResources)) {
                continue;
            }

            $this->checkResourceNotExists(
                path: 'resources',
                creatableResource: "{$this->model}Resource",
                requiredResource: $requiredResource,
            );
        }
    }

    protected function getGeneratedResourceNames(): array
    {
        $names = ["{$this->model}/{$this->model}Resource"];

        if (in_array('R', $this->crudOptions)) {
            $pluralName = $this->getPluralName($this->model);

            $names[] = "{$this->model}/{$pluralName}CollectionResource";
        }

        return $names;
    }

    protected function getRelationImports(array $relations): array
    {
        $namespace = $this->generateNamespace($this->paths['resources']);

        $imports = [];

        foreach ($relations as $relation) {
            if ($relation['entity'] !== $this->model) {
                $imports[] = "{$namespace}\\{$relation['entity']}\\{$relation['resource']}";
            }
        }

        return array_unique($imports);
    }

    protected function getResourceFields(array $relations): ?array
    {
        $foreignKeys = array_map(
            callback: fn (string $relation) => $this->getForeignKeyName($relation),
            array: $this->relations->belongsTo,
        );

        $names = array_values(array_diff($this->fields->getNames(), $foreignKeys));

        if (empty($names) && empty($relations)) {
            return null;
        }

        return [ReservedFieldEnum::Id->value, ...$names];
    }
}
