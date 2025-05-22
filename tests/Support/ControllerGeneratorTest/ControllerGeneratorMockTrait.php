<?php

namespace RonasIT\Support\Tests\Support\ControllerGeneratorTest;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ControllerGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystemWithoutRoutesFile(): void
    {
        $this->fileSystemMock->routes = [];

        $this->fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $this->fileSystemMock = new FileSystemMock;
        $this->fileSystemMock->services = [
            'PostService.php' => $this->mockPhpFileContent(),
        ];
        $this->fileSystemMock->controllers = [];
        $this->fileSystemMock->routes = [
            'api.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->setStructure();
    }
}