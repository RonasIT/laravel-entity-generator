<?php

namespace RonasIT\Support\Tests\Support\Factory;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait FactoryMockTrait
{
    use GeneratorMockTrait;

    public function mockFactoryGenerator(array ...$functionCalls): void
    {
        $this->mockClass(FactoryGenerator::class, $functionCalls);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.paths' => [
                'models' => 'app/Models',
                'factories' => 'database/factories',
            ],
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => $this->mockPhpFileContent(),
                    'User.php' => $this->mockPhpFileContent(),
                ],
            ],
            'database' => [
                'factories' => [],
            ],
        ];

        vfsStream::create($structure);
    }
}
