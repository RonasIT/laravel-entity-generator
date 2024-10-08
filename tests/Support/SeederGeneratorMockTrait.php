<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\View;
use org\bovigo\vfs\vfsStream;

trait SeederGeneratorMockTrait
{
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

    public function mockViewsNamespace(): void
    {
        View::addNamespace('entity-generator', getcwd() . '/stubs');
    }
}