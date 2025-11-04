<?php

namespace RonasIT\Support\Tests\Support\Model;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ModelMockTrait
{
    use GeneratorMockTrait;

    public function mockDefaultFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = [
            'Comment.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
            'User.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
            'Forum/Author.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
        ];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(array $models): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = $models;

        $fileSystemMock->setStructure();
    }
}
