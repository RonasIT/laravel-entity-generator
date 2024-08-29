<?php

namespace RonasIT\Support\Tests\Support\Migration;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait MigrationMockTrait
{
    use GeneratorMockTrait;

    public function setupConfigurations(): void
    {
        config([
            'entity-generator.stubs.migration' => 'entity-generator::migration',
            'entity-generator.paths' => [
                'migrations' => 'database/migrations',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'database' => [
                'migrations' => [],
            ],
        ];

        vfsStream::create($structure);
    }
}
