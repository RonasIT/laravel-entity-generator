<?php

namespace RonasIT\EntityGenerator\Tests\Support\Translation;

use RonasIT\EntityGenerator\Tests\Support\FileSystemMock;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

trait TranslationMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->translations = [];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystemForAppend(string $validationStub): void
    {
        $validation = file_get_contents(getcwd() . "/tests/Support/Translation/{$validationStub}.php");

        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->translations = ['validation.php' => $validation];

        $fileSystemMock->setStructure();
    }
}
