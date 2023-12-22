<?php

namespace RonasIT\Support\Tests\Support\Seeder;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait SeederMockTrait
{
    use GeneratorMockTrait;

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.database_empty_seeder' => 'entity-generator::database_empty_seeder',
            'entity-generator.stubs.legacy_seeder' => 'entity-generator::legacy_seeder',
            'entity-generator.stubs.resource' => 'entity-generator::resource',
            'entity-generator.stubs.seeder' => 'entity-generator::seeder',
            'entity-generator.paths' => [
                'seeders' => 'database/seeders',
                'models' => 'app/Models',
                'database_seeder' => 'database/seeders/DatabaseSeeder.php',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'database' => []
        ];

        vfsStream::create($structure);
    }
}
