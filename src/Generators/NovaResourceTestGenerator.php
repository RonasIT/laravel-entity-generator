<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;

class NovaResourceTestGenerator extends BaseTestsGenerator
{
    public function generate()
    {
        if (class_exists(NovaServiceProvider::class)) {
            if (!$this->classExists('nova', "{$this->model}")) {
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

    public function generateTest(): void
    {
        $actions = [];

        if (file_exists(base_path($this->paths['nova_actions']))) {
            $objectsInsideFolder = scandir(base_path($this->paths['nova_actions']));
            $modelActions = array_filter($objectsInsideFolder, function ($value) {
                return strpos($value, $this->model) !== false
                    && substr($value, -4) === '.php';
            });
            foreach ($modelActions as $action) {
                $action = substr($action, 0, -4);
                $actions[] = [
                    'url' => Str::kebab($action),
                    'fixture' => Str::snake($action),
                ];
            }
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

    public function getTestClassName(): string
    {
        return "Nova{$this->model}Test";
    }

    protected function isFixtureNeeded($type): bool
    {
        return true;
    }
}
