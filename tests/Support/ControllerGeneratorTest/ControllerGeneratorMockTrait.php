<?php

namespace RonasIT\Support\Tests\Support\ControllerGeneratorTest;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait ControllerGeneratorMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function mockControllerGeneratorForExistingController(): void
    {
        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall('controllers', 'PostController')
        ]);
    }

    public function mockControllerGeneratorForNotExistingService(): void
    {
        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall('controllers', 'PostController', false),
            $this->classExistsMethodCall('services', 'PostService', false)
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
                'Http' => [
                    'Controllers' => []
                ]
            ],
            'routes' => [
                'api.php' => '<?php'
            ]
        ];

        vfsStream::create($structure);
    }
}