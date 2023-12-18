<?php

namespace RonasIT\Support\Tests\Support\Factory;

use Illuminate\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use phpmock\functions\FixedValueFunction;
use phpmock\MockBuilder;
use ReflectionClass;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait FactoryMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function getFactoryGeneratorMockForMissingModel(): MockInterface
    {
        $mock = Mockery::mock(FactoryGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('models', 'Post')
            ->andReturn(false);

        return $mock;
    }

    public function getFactoryGeneratorMockForExistingFactory(): MockInterface
    {
        $mock = Mockery::mock(FactoryGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('models', 'Post')
            ->andReturn(true);

        $mock
            ->shouldReceive('classExists')
            ->once()
            ->with('factory', 'PostFactory')
            ->andReturn(true);

        return $mock;
    }

    public function mockGeneratorForMissingRevertedRelationModelFactory(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'checkExistRelatedModelsFactories',
                'arguments' => [],
                'result' => true
            ],
            [
                'method' => 'allowedToCreateFactoryInSeparateClass',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function mockFactoryGenerator(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false
            ],
            [
                'method' => 'allowedToCreateFactoryInSeparateClass',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function mockFactoryGeneratorForAlreadyExistsFactory(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'checkExistModelFactory',
                'arguments' => [],
                'result' => 1
            ],
            [
                'method' => 'allowedToCreateFactoryInSeparateClass',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function mockFactoryGeneratorForMissingRelatedModelFactory(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true
            ],
            [
                'method' => 'allowedToCreateFactoryInSeparateClass',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function getMockForFileExists()
    {
        return $this->mockNativeFunction('\\RonasIT\\Support\\Generators', 'file_exists', true);
    }

    public function mockConfigurations()
    {
        config([
            'entity-generator.stubs.factory' => 'entity-generator::factory',
            'entity-generator.stubs.legacy_factory' => 'entity-generator::legacy_factory',
            'entity-generator.stubs.legacy_empty_factory' => 'entity-generator::legacy_empty_factory',
            'entity-generator.paths' => [
                'models' => 'app/Models',
                'factory' => 'database/factories/ModelFactory.php',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Services' => [
                    'PostService.php' => '<?php'
                ],
                'Controllers' => [],
                'Models' => []
            ],
            'database' => [
                'factories' => [
                    'ModelFactory.php' => '<?php'
                ]
            ]
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystemForNonExistingRelatedModelFactory(): void
    {
        $reflectionClass = new ReflectionClass(ModelWithRelations::class);
        $postModelFileName = $reflectionClass->getFileName();

        $structure = [
            'app' => [
                'Services' => [
                    'PostService.php' => '<?php'
                ],
                'Controllers' => [],
                'Models' => [
                    'Post.php' => file_get_contents($postModelFileName),
                    'User.php' => '<?php'
                ]
            ],
            'database' => [
                'factories' => [
                    'ModelFactory.php' => '<?php',
                ]
            ]
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystemForMissingRevertedRelationModelFactory(): void
    {
        $structure = [
            'app' => [
                'Services' => [
                    'PostService.php' => '<?php'
                ],
                'Controllers' => [],
                'Models' => [
                    'Post.php' => '<?php',
                    'User.php' => '<?php'
                ]
            ],
            'database' => [
                'factories' => [
                    'ModelFactory.php' => file_get_contents(getcwd() . '/tests/Support/Factory/ModelFactory.php'),
                ]
            ]
        ];

        vfsStream::create($structure);
    }
}