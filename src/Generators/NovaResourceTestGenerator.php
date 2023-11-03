<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;

class NovaResourceTestGenerator extends EntityGenerator
{
    public function generate()
    {
        if (class_exists(NovaServiceProvider::class)) {
            if (!$this->classExists('nova', "{$this->model}")) {
                $this->throwFailureException(
                    ClassNotExistsException::class,
                    "Cannot create {$this->model}NovaTest cause {$this->model} Nova resource does not exist.",
                    "Create {$this->model} Nova resource."
                );
            }

            if ($this->classExists('nova', "{$this->model}NovaTest")) {
                $this->throwFailureException(
                    ClassAlreadyExistsException::class,
                    "Cannot create {$this->model}NovaTest cause it's already exist.",
                    "Remove {$this->model}NovaTest."
                );
            }

            $actionsUrl = [];

            if (file_exists($this->paths['nova_actions'])) {
                $objectsInsideFolder = scandir($this->paths['nova_actions']);
                $modelActions = array_filter($objectsInsideFolder, function ($value) {
                    return strpos($value, $this->model) !== false
                        && substr($value, -strlen($value)) === '.php';
                });
                foreach ($modelActions as $action) {
                    $actionsUrl[] = Str::kebab($action);
                }
            }

            $fileContent = $this->getStub('nova_resource_test', [
                'url_path' => $this->getPluralName(Str::kebab($this->model)),
                'entity' => $this->model,
                'entities' => $this->getPluralName($this->model),
                'lower_entity' => Str::snake($this->model),
                'lower_entities' => $this->getPluralName(Str::snake($this->model)),
                'actions_url' => $actionsUrl,
            ]);

            $this->saveClass('tests', "{$this->model}ResourceTest", $fileContent);

            event(new SuccessCreateMessage("Created a new Nova Resource: {$this->model}ResourceTest"));
        } else {
            event(new SuccessCreateMessage("Nova is not installed and NovaResource is skipped"));
        }
    }
}
