<?php

namespace RonasIT\Support\Tests\Support\Factory;

use org\bovigo\vfs\vfsStream;
use ReflectionClass;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait FactoryMockTrait
{
    use GeneratorMockTrait;

    public function getFactoryGeneratorMockForMissingModel(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false
            ]
        ]);
    }

    public function getFactoryGeneratorMockForExistingFactory(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true
            ],
            [
                'method' => 'classExists',
                'arguments' => ['factory', 'PostFactory'],
                'result' => true
            ],
        ]);
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

    public function mockFactoryGeneratorForGenericTypeCreation(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'allowedToCreateFactoryInSeparateClass',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function mockFactoryGeneratorForClassTypeCreation(): void
    {
        $this->mockClass(FactoryGenerator::class, [
            [
                'method' => 'allowedToCreateFactoryInSeparateClass',
                'arguments' => [],
                'result' => true
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

    public function getMockForFileExists(bool $result = true)
    {
        return $this->mockNativeFunction('\\RonasIT\\Support\\Generators', 'file_exists', $result);
    }

    public function mockConfigurations(): void
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

    public function mockConfigurationsForClassStyleFactory(): void
    {
        config([
            'entity-generator.stubs.factory' => 'entity-generator::factory',
            'entity-generator.stubs.legacy_factory' => 'entity-generator::legacy_factory',
            'entity-generator.stubs.legacy_empty_factory' => 'entity-generator::legacy_empty_factory',
            'entity-generator.paths' => [
                'models' => 'app/Models',
                'factory' => 'database/factories',
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

    public function mockFilesystemForGenericStyleCreation(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => file_get_contents(getcwd() . '/tests/Support/Factory/Post.php'),
                    'User.php' => '<?php'
                ]
            ],
            'database' => []
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystemForClassStyleFactoryCreation(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => file_get_contents(getcwd() . '/tests/Support/Factory/Post.php'),
                    'User.php' => '<?php'
                ]
            ],
            'database' => [
                'factories' => []
            ]
        ];

        vfsStream::create($structure);
    }
}