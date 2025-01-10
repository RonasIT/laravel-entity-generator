<?php

namespace RonasIT\Support\Tests\Support\Resource;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait ResourceMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.paths' => [
                'resources' => 'app/Http/Resources',
                'models' => 'app/Models',
            ],
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
