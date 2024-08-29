<?php

namespace RonasIT\Support\Tests\Support\Controller;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait ControllerMockTrait
{
    use GeneratorMockTrait;

    public function mockControllerGeneratorForExistingController(): void
    {
        $this->mockClass(ControllerGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['controllers', 'PostController'],
                'result' => true
            ]
        ]);
    }

    public function mockControllerGeneratorForNotExistingService(): void
    {
        $this->mockClass(ControllerGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['controllers', 'PostController'],
                'result' => false
            ],
            [
                'method' => 'classExists',
                'arguments' => ['services', 'PostService'],
                'result' => false
            ],
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.controller' => 'entity-generator::controller',
            'entity-generator.stubs.use_routes' => 'entity-generator::use_routes',
            'entity-generator.stubs.routes' => 'entity-generator::routes',
            'entity-generator.paths' => [
                'controllers' => 'app/Controllers',
                'services' => 'app/Services',
                'requests' => 'app/Requests',
                'resources' => 'app/Http/Resources',
                'routes' => 'routes/api.php'
            ]
        ]);
    }

    public function mockFilesystemWithoutRoutesFile(): void
    {
        $structure = [
            'app' => [
                'Services' => [
                    'PostService.php' => '<?php'
                ],
                'Controllers' => []
            ],
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Services' => [
                    'PostService.php' => '<?php'
                ],
                'Controllers' => []
            ],
            'routes' => [
                'api.php' => '<?php'
            ]
        ];

        vfsStream::create($structure);
    }
}