<?php

namespace RonasIT\Support\Tests\Support\Controller;

use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait ControllerMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function getControllerGeneratorMockForExistingController(): MockInterface
    {
        $mock = Mockery::mock(ControllerGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('controllers', 'PostController')
            ->andReturn(true);

        return $mock;
    }

    public function getControllerGeneratorMockForNotExistingService(): MockInterface
    {
        $mock = Mockery::mock(ControllerGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('controllers', 'PostController')
            ->andReturn(false);

        $mock
            ->shouldReceive('classExists')
            ->once()
            ->with('services', 'PostService')
            ->andReturn(false);

        return $mock;
    }

    public function mockConfigurations()
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