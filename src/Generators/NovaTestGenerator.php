<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Nova\Http\Requests\NovaRequest;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;

class NovaTestGenerator extends AbstractTestsGenerator
{
    protected $novaModelName;

    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            if (!$this->doesNovaResourceExists()) {
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create Nova{$this->model}Test cause {$this->model} Nova resource does not exist.",
                    "Create {$this->model} Nova resource."
                );
            }

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

        $this->saveClass('tests', "Nova{$this->model}Test", $fileContent);

        event(new SuccessCreateMessage("Created a new Nova test: Nova{$this->model}Test"));
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
        return app("\\App\\Nova\\{$this->novaModelName}")->actions(new NovaRequest());
    }

    protected function loadNovaFields()
    {
        return app("\\App\\Nova\\{$this->novaModelName}")->fields(new NovaRequest());
    }

    protected function loadNovaFilters()
    {
        return app("\\App\\Nova\\{$this->novaModelName}")->filters(new NovaRequest());
    }

    public function getTestClassName(): string
    {
        return "Nova{$this->model}Test";
    }

    protected function isFixtureNeeded($type): bool
    {
        return true;
    }

    protected function doesNovaResourceExists(): bool
    {
        $possibleNovaModelNames = [
            "{$this->model}NovaResource",
            "{$this->model}Resource",
            $this->model
        ];

        foreach ($possibleNovaModelNames as $modelName) {
            if ($this->classExists('nova', $modelName)) {
                $this->novaModelName = $modelName;

                return true;
            }
        }

        return false;
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
