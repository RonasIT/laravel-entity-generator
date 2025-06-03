<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait NovaResourceGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;
        $fileSystemMock->novaModels = [];
        $fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
    }

    public function mockFileSystemWithoutPostModel(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = null;

        $fileSystemMock->setStructure();
    }
}
