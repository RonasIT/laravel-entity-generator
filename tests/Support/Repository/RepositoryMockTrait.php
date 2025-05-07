<?php

namespace RonasIT\Support\Tests\Support\Repository;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait RepositoryMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
    }
}
