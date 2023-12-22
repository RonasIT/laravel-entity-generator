<?php

namespace RonasIT\Support\Tests\Support\Service;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ServiceGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait ServiceMockTrait
{
    use GeneratorMockTrait;

    public function mockGeneratorForMissingModel()
    {
        $this->mockClass(ServiceGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['repositories', 'PostRepository'],
                'result' => false
            ],
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false
            ]
        ]);
    }

    public function mockGeneratorForServiceWithTrait()
    {
        $this->mockClass(ServiceGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['repositories', 'PostRepository'],
                'result' => false
            ],
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true
            ]
        ]);
    }

    public function mockGeneratorForServiceWithoutTrait()
    {
        $this->mockClass(ServiceGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['repositories', 'PostRepository'],
                'result' => true
            ]
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.service' => 'entity-generator::service',
            'entity-generator.stubs.service_with_trait' => 'entity-generator::service_with_trait',
            'entity-generator.paths' => [
                'repositories' => 'app/Repositories',
                'services' => 'app/Services',
                'models' => 'app/Models',
            ]
        ]);
    }

    public function mockFilesystemForServiceWithTrait(): void
    {
        $structure = [
            'app' => [
                'Services' => [],
            ]
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystemForServiceWithoutTrait(): void
    {
        $structure = [
            'app' => [
                'Services' => [],
                'Repositories' => [
                    'PostRepository.php' => '<?php'
                ],
            ]
        ];

        vfsStream::create($structure);
    }
}
