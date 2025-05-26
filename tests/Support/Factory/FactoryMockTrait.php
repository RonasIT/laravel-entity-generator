<?php

namespace RonasIT\Support\Tests\Support\Factory;

use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait FactoryMockTrait
{
    use GeneratorMockTrait;

    public function mockFactoryGenerator(array ...$functionCalls): void
    {
        $this->mockClass(FactoryGenerator::class, $functionCalls);
    }

    public function mockFileSystemWithoutPostModel(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = [
            'User.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
            'User.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
    }
}
