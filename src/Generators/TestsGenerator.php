<?php

namespace RonasIT\Support\Generators;

use RonasIT\Support\Events\SuccessCreateMessage;

class TestsGenerator extends BaseTestsGenerator
{
    public function getTestClassName()
    {
        return "{$this->model}Test";
    }

    protected function generateTest()
    {
        $content = $this->getStub('test', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth
        ]);

        $testName = $this->getTestClassName();
        $createMessage = "Created a new Test: {$testName}";

        $this->saveClass('tests', $testName, $content);

        event(new SuccessCreateMessage($createMessage));
    }

    protected function isFixtureNeeded($type): bool
    {
        $firstLetter = strtoupper($type[0]);

        return in_array($firstLetter, $this->crudOptions);
    }
}
