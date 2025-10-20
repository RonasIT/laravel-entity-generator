<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;

class TestsGenerator extends AbstractTestsGenerator
{
    public function generate(): void
    {
        $this->entity = $this->model;

        parent::generate();
    }

    public function getTestClassName(): string
    {
        return "{$this->model}Test";
    }

    protected function isFixtureNeeded($type): bool
    {
        $firstLetter = strtoupper($type[0]);

        return in_array($firstLetter, $this->crudOptions);
    }

    protected function generateFixture($fixtureName, $data): void
    {
        $fixturePath = $this->getFixturesPath($fixtureName);
        $content = json_encode($data, JSON_PRETTY_PRINT);
        $fixtureRelativePath = "{$this->paths['tests']}/fixtures/{$this->getTestClassName()}/{$fixtureName}";
        $createMessage = "Created a new Test fixture on path: {$fixtureRelativePath}";

        file_put_contents($fixturePath, $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function generateTests(): void
    {
        if (!$this->isStubExists('test')) {
            return;
        }

        $content = $this->getStub('test', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'entityNamespace' => $this->getNamespace('models', $this->modelSubFolder),
            'userNamespace' => $this->getNamespace('models'),
            'hasModificationEndpoints' => !empty(array_intersect($this->crudOptions, ['C', 'U', 'D'])),
        ]);

        $testName = $this->getTestClassName();
        $createMessage = "Created a new Test: {$testName}";

        $this->saveClass('tests', $testName, $content);

        event(new SuccessCreateMessage($createMessage));
    }
}
