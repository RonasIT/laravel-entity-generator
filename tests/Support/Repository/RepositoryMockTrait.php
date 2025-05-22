<?php

namespace RonasIT\Support\Tests\Support\Repository;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait RepositoryMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $this->fileSystemMock = new FileSystemMock;

        $this->fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->setStructure();
    }
}
