<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Nova\Http\Requests\NovaRequest;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RonasIT\Support\Exceptions\EntityCreateException;
use Generator;

class NovaTestGenerator extends AbstractTestsGenerator
{
    protected string $resourceName;

    protected ?string $fullNovaResourcePath = null;

    protected string $shortNovaResourceName;

    protected string $novaPath;

    public function __construct()
    {
        $this->novaPath = app_path('Nova');

        parent::__construct();
    }

    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            if (!$this->classExists('models', $this->model)) {
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create Nova{$this->shortNovaResourceName}Test cause {$this->model} does not exist.",
                    "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'."
                );
            }

            $this->isNovaResourceExists();

            if ($this->classExists('nova', "Nova{$this->shortNovaResourceName}Test")) {
                $this->throwFailureException(
                    ClassAlreadyExistsException::class,
                    "Cannot create Nova{$this->shortNovaResourceName}Test cause it's already exist.",
                    "Remove Nova{$this->shortNovaResourceName}Test."
                );
            }

            parent::generate();
        } else {
            event(new SuccessCreateMessage("Nova is not installed and NovaTest is skipped"));
        }
    }

    public function setMetaData(array $data): self
    {
        $resourceName = empty($data['resource_name']) ? "{$this->model}Resource" : $data['resource_name'];

        $this->resourceName = Str::studly($resourceName);

        $this->shortNovaResourceName = Str::afterLast($this->resourceName, '\\');

        return $this;
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
            'entities' => $this->getPluralName($this->model),
            'snake_entity' => Str::snake($this->model),
            'dromedary_entity' => Str::lcfirst($this->model),
            'lower_entities' => $this->getPluralName(Str::snake($this->model)),
            'actions' => $actions,
            'filters' => $filters,
        ]);

        $this->saveClass('tests', "Nova{$this->shortNovaResourceName}Test", $fileContent);

        event(new SuccessCreateMessage("Created a new Nova test: Nova{$this->shortNovaResourceName}Test"));
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

    protected function loadNovaActions()
    {
        return app("{$this->fullNovaResourcePath}")->actions(new NovaRequest());
    }

    protected function loadNovaFields()
    {
        return app("{$this->fullNovaResourcePath}")->fields(new NovaRequest());
    }

    protected function loadNovaFilters()
    {
        return app("{$this->fullNovaResourcePath}")->filters(new NovaRequest());
    }

    public function getTestClassName(): string
    {
        return "Nova{$this->shortNovaResourceName}Test";
    }

    protected function isFixtureNeeded($type): bool
    {
        return true;
    }

    protected function isNovaResourceExists(): true
    {
        $allNovaClasses = $this->getAllNovaClasses();

        $resources = [];

        foreach ($allNovaClasses as $class) {
            if ($class === $this->resourceName) {
                $this->fullNovaResourcePath = "App\\Nova\\{$this->resourceName}";

                return true;
            }

            if (Str::contains($class, $this->model) && is_subclass_of("App\\Nova\\{$class}", "App\\Nova\\Resource")) {
                $resources[] = $class;
            }
        }

        if (!empty($resources)) {
            $resources = implode(', ', $resources);

            $this->throwFailureException(
                EntityCreateException::class,
                "Cannot create Nova{$this->shortNovaResourceName}Test cause I am found a lot of suitable resources: $resources",
                "Please, use --resource-name option"
            );
        }

        $this->throwFailureException(
            ClassNotExistsException::class,
            "Cannot create Nova{$this->shortNovaResourceName}Test cause {$this->resourceName} Nova resource does not exist.",
            "Create {$this->resourceName} Nova resource."
        );
    }

    protected function getAllNovaClasses(): Generator
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->novaPath));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = Str::after($file->getPathname(), $this->novaPath . DIRECTORY_SEPARATOR);

                yield str_replace(['/', '.php'], ['\\', ''], $relativePath);
            }
        }
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
