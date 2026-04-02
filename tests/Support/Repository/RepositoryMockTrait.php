<?php

namespace RonasIT\EntityGenerator\Tests\Support\Repository;

use RonasIT\EntityGenerator\Tests\Support\FileSystemMock;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

trait RepositoryMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
    }
}
