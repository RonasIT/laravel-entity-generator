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
        $this->fileSystemMock->models = [
            'User.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $this->fileSystemMock = new FileSystemMock();

        $this->fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
            'User.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->setStructure();
    }
}
