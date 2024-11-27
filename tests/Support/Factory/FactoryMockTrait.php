<?php

namespace RonasIT\Support\Tests\Support\Factory;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait FactoryMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function getFactoryGeneratorMockForExistingFactory(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true,
            ],
            [
                'function' => 'classExists',
                'arguments' => ['factory', 'PostFactory'],
                'result' => true,
            ],
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.paths' => [
                'models' => 'app/Models',
                'factory' => 'database/factories',
            ],
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => file_get_contents(getcwd() . '/tests/Support/Factory/Post.php'),
                    'User.php' => '<?php',
                ],
            ],
            'database' => [
                'factories' => [],
            ],
        ];

        vfsStream::create($structure);
    }
}
