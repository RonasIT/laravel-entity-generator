<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Nova\Http\Requests\NovaRequest;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\ParserFactory;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;

class NovaTestGenerator extends AbstractTestsGenerator
{
    public function generate(): void
    {
        if (class_exists(NovaServiceProvider::class)) {
            if (!$this->classExists('nova', $this->model)) {
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
        $actions = [];

        if (file_exists(base_path($this->paths['nova_actions']))) {
            $actions = $this->getActions();
        }

        $fileContent = $this->getStub('nova_resource_test', [
            'url_path' => $this->getPluralName(Str::kebab($this->model)),
            'entity' => $this->model,
            'entities' => $this->getPluralName($this->model),
            'lower_entity' => Str::snake($this->model),
            'lower_entities' => $this->getPluralName(Str::snake($this->model)),
            'actions' => $actions,
        ]);

        $this->saveClass('tests', "Nova{$this->model}Test", $fileContent);

        event(new SuccessCreateMessage("Created a new Nova test: Nova{$this->model}Test"));
    }

    protected function getActions(): array
    {
        $actions = $this->loadNovaActions();

        if (empty($actions)) {
            return [];
        }

        $actions = array_unique(array_map(function ($action) {
            return get_class($action);
        }, $actions));

        return array_map(function (string $action) {
            $actionNamespace = explode('\\', $action);
            $actionClass = end($actionNamespace);

            return [
                'url' => Str::kebab($actionClass),
                'fixture' => Str::snake($actionClass),
            ];
        }, $actions);
    }

    protected function loadNovaActions()
    {
        return app("\\App\\Nova\\{$this->model}")->actions(new NovaRequest());
    }

    public function getTestClassName(): string
    {
        return "Nova{$this->model}Test";
    }

    protected function isFixtureNeeded($type): bool
    {
        return true;
    }
}
