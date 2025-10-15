<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Nova\Http\Requests\NovaRequest;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\EntityCreateException;
use Generator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Arr;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;

class NovaTestGenerator extends AbstractTestsGenerator
{
    protected string $novaResourceClassName;

    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            if ($this->classExists('nova', "Nova{$this->model}ResourceTest")) {

                $path = $this->getClassPath('nova', "Nova{$this->model}ResourceTest");

                throw new ResourceAlreadyExistsException($path);
            }

            $novaResources = $this->getCommonNovaResources();

            if (count($novaResources) > 1) {
                $foundedResources = implode(', ', $novaResources);

                // TODO: Change exception message after https://github.com/RonasIT/laravel-entity-generator/issues/159 will be ready
                    $this->throwFailureException(
                        EntityCreateException::class,
                        "Cannot create Nova{$this->model}ResourceTest cause was found a lot of suitable resources: {$foundedResources}.",
                        'Please, use --resource-name option.'
                    );
            }

            if (empty($novaResources)) {
                // TODO: pass $this->modelSubfolder to Exception after refactoring in https://github.com/RonasIT/laravel-entity-generator/issues/179
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create Nova{$this->model}ResourceTest cause {$this->model} Nova resource does not exist.",
                    "Create {$this->model} Nova resource."
                );
            }

            $this->novaResourceClassName = Arr::first($novaResources);

            parent::generate();
        } else {
            event(new SuccessCreateMessage("Nova is not installed and NovaTest is skipped"));
        }
    }

    public function setMetaData(array $data): self
    {
        $this->novaResourceName = !empty($data['resource_name']) ? Str::studly($data['resource_name']) : null;

        return $this;
    }

    public function generateTests(): void
    {
        if (!$this->isStubExists('nova_test')) {
            return;
        }

        $actions = $this->getActions();
        $filters = $this->collectFilters();

        $resourceClass = Str::afterLast($this->novaResourceClassName, '\\');

        $fileContent = $this->getStub('nova_test', [
            'entity_namespace' => $this->getNamespace('models', $this->modelSubFolder),
            'entity' => $this->model,
            'resource_name' => $resourceClass,
            'resource_namespace' => $this->novaResourceClassName,
            'snake_resource' => Str::snake($resourceClass),
            'dromedary_entity' => Str::lcfirst($this->model),
            'lower_entities' => $this->getPluralName(Str::snake($this->model)),
            'actions' => $actions,
            'filters' => $filters,
        ]);

        $this->saveClass('tests', "Nova{$this->model}ResourceTest", $fileContent);

        event(new SuccessCreateMessage("Created a new Nova test: Nova{$this->model}ResourceTest"));
    }

    protected function getActions(): array
    {
        $actions = $this->loadNovaActions();

        $actions = array_unique(array_map(function ($action) {
            return get_class($action);
        }, $actions));

        return array_map(function (string $action) {
            $actionClass = class_basename($action);

            return [
                'className' => $actionClass,
                'fixture' => Str::snake($actionClass),
            ];
        }, $actions);
    }

    protected function getNovaFiles(): Generator
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($this->paths['nova'])));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file;
            }
        }
    }

    protected function getCommonNovaResources(): array
    {
        $resources = [];

        foreach ($this->getNovaFiles() as $file) {
            $relativePath = Str::after($file->getPathname(), $this->paths['nova'] . DIRECTORY_SEPARATOR);

            $class = Str::before($relativePath, '.');

            $className = $this->pathToNamespace($this->paths['nova'] . DIRECTORY_SEPARATOR . $class);

            if ($this->isResourceNameContainModel($className) && $this->isNovaResource($className)) {
                $resources[] = $className;
            }
        }

        return $resources;
    }

    protected function isResourceNameContainModel(string $className): bool
    {
        return str_contains($className, $this->model);
    }

    protected function isNovaResource(string $className): bool
    {
        return is_subclass_of($className, 'Laravel\\Nova\\Resource');
    }

    protected function loadNovaActions()
    {
        return app($this->novaResourceClassName)->actions(new NovaRequest());
    }

    protected function loadNovaFields()
    {
        return app($this->novaResourceClassName)->fields(new NovaRequest());
    }

    protected function loadNovaFilters()
    {
        return app($this->novaResourceClassName)->filters(new NovaRequest());
    }

    public function getTestClassName(): string
    {
        return "Nova{$this->model}Test";
    }

    protected function isFixtureNeeded($type): bool
    {
        return true;
    }

    protected function collectFilters(): array
    {
        $filtersFromFields = $this->getFiltersFromFields();
        $filters = $this->getFilters();

        return array_merge($filtersFromFields, $filters);
    }

    protected function getFiltersFromFields(): array
    {
        $filters = [];
        $fields = $this->loadNovaFields();

        foreach ($fields as $field) {
            if (!property_exists($field, 'filterableCallback') || is_null($field->filterableCallback)) {
                continue;
            }

            $classname = class_basename($field);
            $fieldName = Str::snake($field->name);
            $filterName = "{$classname}:{$fieldName}";

            if (!in_array($filterName, $filters)) {
                $filters[] = [
                    'name' => $filterName,
                    'fixture_name' => Str::snake($classname),
                ];
            }
        }

        return $filters;
    }

    protected function getFilters(): array
    {
        $filters = [];
        $novaResourceFilters = $this->loadNovaFilters();

        foreach ($novaResourceFilters as $filter) {
            $filters[] = [
                'name' => get_class($filter),
                'fixture_name' => Str::snake(class_basename($filter))
            ];
        }

        return $filters;
    }

    protected function getDumpName(): string
    {
        $modelName = Str::snake($this->model);

        return "nova_{$modelName}_dump.sql";
    }
}
