<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;

class TestsGenerator extends AbstractTestsGenerator
{
    public function getTestClassName(): string
    {
        return "{$this->model}Test";
    }

    protected function generateExistedEntityFixture()
    {
        $object = $this->getFixtureValuesList($this->model);
        $entity = Str::snake($this->model);

        foreach (self::FIXTURE_TYPES as $type => $modifications) {
            if ($this->isFixtureNeeded($type)) {
                foreach ($modifications as $modification) {
                    $excepts = [];
                    if ($modification === 'request') {
                        $excepts = ['id'];
                    }
                    $this->generateFixture("{$type}_{$entity}_{$modification}.json", Arr::except($object, $excepts));
                }
            }
        }
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
            'modelsNamespace' => $this->getOrCreateNamespace('models')
        ]);

        $testName = $this->getTestClassName();
        $createMessage = "Created a new Test: {$testName}";

        $this->saveClass('tests', $testName, $content);

        event(new SuccessCreateMessage($createMessage));
    }
}
