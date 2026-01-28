<?php

namespace RonasIT\Support\Generators;

use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\NovaServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\EntityCreateException;
use RonasIT\Support\Exceptions\ResourceNotExistsException;

class NovaTestGenerator extends AbstractTestsGenerator
{
    protected ?string $novaResourceClassName = null;

    public function setNovaResource(?string $novaResource): self
    {
        if (!empty($novaResource)) {
            $path = $this->paths['nova'] . DIRECTORY_SEPARATOR . Str::studly($novaResource);

            $this->novaResourceClassName = $this->pathToNamespace($path);
        }

        return $this;
    }

    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            if (!isset($this->novaResourceClassName)) {
                $this->novaResourceClassName = $this->findNovaResource();
            } elseif (!class_exists($this->novaResourceClassName)) {
                $this->throwFailureException(
                    exceptionClass: ClassNotExistsException::class,
                    failureMessage: "Cannot create {$this->getTestClassName()} cause {$this->novaResourceClassName} does not exist.",
                    recommendedMessage: "Create {$this->novaResourceClassName}.",
                );
            }

            $this->checkResourceExists('nova', $this->getTestClassName());

            parent::generate();
        } else {
            event(new SuccessCreateMessage('Nova is not installed and NovaTest is skipped'));
        }
    }

    public function generateTests(): void
    {
        if (!$this->isStubExists('nova_test')) {
            return;
        }

        $actions = $this->getActions();
        $filters = $this->collectFilters();

        $fileContent = $this->getStub('nova_test', [
            'entity_namespace' => $this->generateNamespace($this->paths['models'], $this->modelSubFolder),
            'entity' => $this->model,
            'resource_name' => $this->getTestingEntityName(),
            'resource_namespace' => $this->novaResourceClassName,
            'snake_resource' => Str::snake($this->getTestingEntityName()),
            'dromedary_entity' => Str::lcfirst($this->model),
            'lower_entities' => $this->getPluralName(Str::snake($this->model)),
            'actions' => $actions,
            'filters' => $filters,
            'models_namespace' => $this->generateNamespace($this->paths['models']),
        ]);

        $this->saveClass('tests', $this->getTestClassName(), $fileContent);

        event(new SuccessCreateMessage("Created a new Nova test: {$this->getTestClassName()}"));
    }

    protected function findNovaResource(): string
    {
        $novaResources = $this->getCommonNovaResources();

        if (count($novaResources) > 1) {
            $foundedResources = implode(', ', $novaResources);

            $this->throwFailureException(
                exceptionClass: EntityCreateException::class,
                failureMessage: "Cannot create Nova{$this->model}ResourceTest cause was found a lot of suitable resources: {$foundedResources}.",
                recommendedMessage: 'You may use --nova-resource-name option to specify a concrete resource.',
            );
        }

        if (empty($novaResources)) {
            throw new ResourceNotExistsException("Nova{$this->model}ResourceTest", "{$this->model} Nova resource");
        }

        return Arr::first($novaResources);
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
        return "Nova{$this->getTestingEntityName()}Test";
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
                'fixture_name' => Str::snake(class_basename($filter)),
            ];
        }

        return $filters;
    }

    protected function getTestingEntityName(): string
    {
        return Str::afterLast($this->novaResourceClassName, '\\');
    }
}
