<?php

namespace RonasIT\Support\Tests\Support\Model;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ModelMockTrait
{
    use GeneratorMockTrait;


    public function mockFileSystemWithoutCommentModel(): void
    {
        $this->fileSystemMock->models = [
            'User.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
        ];

        $this->fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $this->fileSystemMock = new FileSystemMock;

        $this->fileSystemMock->models = [
            'Comment.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
            'User.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
        ];

        $this->fileSystemMock->setStructure();
    }
}
