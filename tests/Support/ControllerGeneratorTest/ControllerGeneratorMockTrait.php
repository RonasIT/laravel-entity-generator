<?php

namespace RonasIT\EntityGenerator\Tests\Support\ControllerGeneratorTest;

use RonasIT\EntityGenerator\Tests\Support\FileSystemMock;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

trait ControllerGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystemWithoutRoutesFile(): void
    {
        $fileSystemMock = new FileSystemMock();
        $fileSystemMock->services = [
            'PostService.php' => $this->mockPhpFileContent(),
        ];
        $fileSystemMock->routes = [];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();
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
