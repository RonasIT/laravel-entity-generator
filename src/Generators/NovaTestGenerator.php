<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Str;
use Laravel\Nova\NovaServiceProvider;
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
            $modelActions = $this->getModelActions();

            foreach ($modelActions as $action) {
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

    protected function getModelActions()
    {
        $novaResource = base_path($this->paths['nova'] . "/{$this->model}.php");
        $code = file_get_contents($novaResource);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        $modelActions = [];

        foreach ($ast[0]->stmts as $astStmt) {
            if ($astStmt instanceof Class_) {
                foreach ($astStmt->stmts as $classStmt) {
                    if (!$classStmt instanceof ClassMethod || $classStmt->name->name !== 'actions') {
                        continue;
                    }

                    foreach ($classStmt->stmts as $methodStmt) {
                        if (!$methodStmt instanceof Return_) {
                            continue;
                        }

                        foreach ($methodStmt->expr->items as $returnArrayItem) {
                            $actionClassName = $this->getActionClassName($returnArrayItem->value);

                            if (is_null($actionClassName)) {
                                continue;
                            }

                            if (!in_array($actionClassName, $modelActions)) {
                                $modelActions[] = $actionClassName;
                            }
                        }
                    }
                }
            }
        }

        return $modelActions;
    }

    protected function getActionClassName(Expr $expr): ?string
    {
        if (property_exists($expr, 'class')) {
            return $expr->class->parts[0];
        }

        if (property_exists($expr, 'var')) {
            return $this->getActionClassName($expr->var);
        }

        return null;
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
