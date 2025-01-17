<?php

namespace RonasIT\Support\Tests\Support\ControllerGeneratorTest;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ControllerGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystemWithoutRoutesFile(): void
    {
        $fileSystemMock = new FileSystemMock;
        $fileSystemMock->services = [
            'PostService.php' => $this->mockPhpFileContent(),
        ];
        $fileSystemMock->controllers = [];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;
        $fileSystemMock->services = [
            'PostService.php' => $this->mockPhpFileContent(),
        ];
        $fileSystemMock->controllers = [];
        $fileSystemMock->routes = [
            'api.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
    }
}