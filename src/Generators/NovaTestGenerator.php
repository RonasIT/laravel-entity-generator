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

class NovaTestGenerator extends AbstractTestsGenerator
{
    protected string $novaPath;

    protected string $fullNovaResourceNamePath;

    public function __construct()
    {
        parent::__construct();

        $this->novaPath = base_path($this->paths['nova']);
    }

    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            $novaResources = $this->getCommonNovaResources();

            if (count($novaResources) > 1) {
                $foundedResources = implode(', ', $novaResources);

                // TODO: Change exception message after https://github.com/RonasIT/laravel-entity-generator/issues/159 will be ready
                $this->throwFailureException(
                    EntityCreateException::class,
                    "Cannot create Nova{$this->model}ResourceTest cause was found a lot of suitable resources: {$foundedResources}.",
                    'Make test by yourself.'
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

            $this->fullNovaResourceNamePath = "App\\Nova\\" . Arr::first($novaResources);

            if ($this->classExists('nova', "Nova{$this->model}ResourceTest")) {
                $this->throwFailureException(
                    ClassAlreadyExistsException::class,
                    "Cannot create Nova{$this->model}ResourceTest cause it's already exist.",
                    "Remove Nova{$this->model}ResourceTest."
                );
            }

            parent::generate();
        } else {
            event(new SuccessCreateMessage("Nova is not installed and NovaTest is skipped"));
        }
    }

    public function generateTests(): void
    {
        if (!$this->isStubExists('nova_test')) {
            return;
        }

        $actions = $this->getActions();
        $filters = $this->collectFilters();

        $resourceClass = Str::afterLast($this->fullNovaResourceNamePath, '\\');

        $fileContent = $this->getStub('nova_test', [
            'entity_namespace' => $this->getNamespace('models', $this->modelSubFolder),
            'entity' => $this->model,
            'resource_name' => $resourceClass,
            'resource_namespace' => $this->fullNovaResourceNamePath,
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
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->novaPath));

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
            $relativePath = Str::after($file->getPathname(), $this->novaPath . DIRECTORY_SEPARATOR);

            $class = Str::before(str_replace('/', '\\', $relativePath), '.');

            if ($this->isResourceNameContainModel($class) && $this->isNovaResource($class)) {
                $resources[] = $class;
            }
        }

        return $resources;
    }

    protected function isResourceNameContainModel(string $class): bool
    {
        return str_contains($class, $this->model);
    }

    protected function isNovaResource(string $class): bool
    {
        return is_subclass_of("App\\Nova\\{$class}", 'App\\Nova\\Resource');
    }

    protected function loadNovaActions()
    {
        return app($this->fullNovaResourceNamePath)->actions(new NovaRequest());
    }

    protected function loadNovaFields()
    {
        return app($this->fullNovaResourceNamePath)->fields(new NovaRequest());
    }

    protected function loadNovaFilters()
    {
        return app($this->fullNovaResourceNamePath)->filters(new NovaRequest());
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
        $filters= [];
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
