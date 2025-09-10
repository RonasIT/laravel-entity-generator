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

class NovaTestGenerator extends AbstractTestsGenerator
{
    protected $novaResourceName;

    public function generate(): void
    {

        if (class_exists(NovaServiceProvider::class)) {
            $novaResources = $this->getCommonNovaResources();

            if (count($novaResources) > 1){
                $foundedResources = implode(', ', $novaResources);

                $this->throwFailureException(
                    EntityCreateException::class,
                    "Cannot create Nova{$this->model}Resource Test cause was found a lot of suitable resources: {$foundedResources}.",
                    'Make test by yourself.'
                );
            }

            if (empty($novaResources)) {
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create Nova{$this->model}Test cause {$this->model} Nova resource does not exist.",
                    "Create {$this->model} Nova resource."
                );
            }

            $this->novaResourceName = array_pop($novaResources);

            if ($this->classExists('nova', "Nova{$this->model}Test")) {
                $this->throwFailureException(
                    ClassAlreadyExistsException::class,
                    "Cannot create Nova{$this->model}Test cause it's already exist.",
                    "Remove Nova{$this->model}Test."
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

        $fileContent = $this->getStub('nova_test', [
            'url_path' => Str::kebab($this->model) . '-resources',
            'entity' => $this->model,
            'resource' => $this->novaResourceName,
            'resource_path' => "App\\Nova\\{$this->novaResourceName}",
            'entities' => $this->getPluralName($this->model),
            'snake_resource' => Str::snake($this->novaResourceName),
            'dromedary_entity' => Str::lcfirst($this->model),
            'lower_entities' => $this->getPluralName(Str::snake($this->model)),
            'actions' => $actions,
            'filters' => $filters,
        ]);

        $this->saveClass('tests', "Nova{$this->novaResourceName}Test", $fileContent);

        event(new SuccessCreateMessage("Created a new Nova test: Nova{$this->novaResourceName}Test"));
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
        $novaPath = base_path($this->paths['nova']);

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($novaPath));

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

            $class = str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if ($this->isNovaResource($class) && $this->isResourceNameContainModel($class)) {
                $resources[] = $class;
            }
        }

        return $resources;
    }

    protected function isNovaResource(string $resource): bool
    {
        return is_subclass_of("App\\Nova\\{$resource}", 'Laravel\\Nova\\Resource');
    }

    protected function isResourceNameContainModel(string $resource): bool
    {
        $resource = str_replace('Resource', '', $resource);

        return Str::afterLast($resource, '\\') === $this->model;
    }

    protected function loadNovaActions()
    {
        return app("\\App\\Nova\\{$this->novaResourceName}")->actions(new NovaRequest());
    }

    protected function loadNovaFields()
    {
        return app("\\App\\Nova\\{$this->novaResourceName}")->fields(new NovaRequest());
    }

    protected function loadNovaFilters()
    {
        return app("\\App\\Nova\\{$this->novaResourceName}")->filters(new NovaRequest());
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
