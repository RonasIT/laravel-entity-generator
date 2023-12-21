<?php

namespace RonasIT\Support\Tests\Support\Request;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\RepositoryGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait RequestMockTrait
{
    use GeneratorMockTrait;

    public function mockGeneratorForCreation(): void
    {
        $this->mockClass(RepositoryGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false
            ],
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.request' => 'entity-generator::request',
            'entity-generator.paths' => [
                'requests' => 'app/Http/Requests',
                'services' => 'app/Services',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Http' => [
                    'Requests' => [
                        'Posts' => []
                    ],
                ],
                'Services' => [
                    'PostService.php' => '<?php'
                ]
            ],
        ];

        vfsStream::create($structure);
    }
}
