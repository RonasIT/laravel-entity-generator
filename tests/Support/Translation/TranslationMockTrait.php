<?php

namespace RonasIT\Support\Tests\Support\Translation;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait TranslationMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->translations = [];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystemForAppend(): void
    {
        $validation = file_get_contents(getcwd() . '/tests/Support/Translation/validation_without_exceptions.php');

        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->translations = ['validation.php' => $validation];

        $fileSystemMock->setStructure();
    }
}
