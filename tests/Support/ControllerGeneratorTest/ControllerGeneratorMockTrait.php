<?php

namespace RonasIT\Support\Tests\Support\ControllerGeneratorTest;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait ControllerGeneratorMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function mockFilesystemWithoutRoutesFile(): void
    {
        $fileSystemMock = new FileSystemMock;
        $fileSystemMock->services = [
            'PostService.php' => '<?php'
        ];
        $fileSystemMock->controllers = [];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;
        $fileSystemMock->services = [
            'PostService.php' => '<?php'
        ];
        $fileSystemMock->controllers = [];
        $fileSystemMock->routes = [
            'api.php' => '<?php'
        ];

        $fileSystemMock->setStructure();
    }
}