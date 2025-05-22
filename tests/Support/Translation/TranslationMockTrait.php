<?php

namespace RonasIT\Support\Tests\Support\Translation;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait TranslationMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $this->fileSystemMock = new FileSystemMock();

        $this->fileSystemMock->translations = [];

        $this->fileSystemMock->setStructure();
    }

    public function mockFilesystemForAppend(): void
    {
        $validation = file_get_contents(getcwd() . '/tests/Support/Translation/validation_without_exceptions.php');

        $this->fileSystemMock->translations = ['validation.php' => $validation];

        $this->fileSystemMock->setStructure();
    }
}
