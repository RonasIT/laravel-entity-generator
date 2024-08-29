<?php

namespace RonasIT\Support\Tests\Support\Resource;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ResourceGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait ResourceMockTrait
{
    use GeneratorMockTrait;

    public function mockGeneratorForAlreadyExistsResource(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['resources', 'PostResource'],
                'result' => true
            ],
        ]);
    }

    public function mockGeneratorForAlreadyExistsCollectionResource(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            [
                'method' => 'generateResource',
                'arguments' => [],
                'result' => null
            ],
            [
                'method' => 'classExists',
                'arguments' => ['resources', 'PostsCollectionResource'],
                'result' => true
            ]
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.resource' => 'entity-generator::resource',
            'entity-generator.stubs.collection_resource' => 'entity-generator::collection_resource',
            'entity-generator.paths' => [
                'resources' => 'app/Http/Resources',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Http' => [
                    'Resources' => [],
                ],
            ],
        ];

        vfsStream::create($structure);
    }
}
