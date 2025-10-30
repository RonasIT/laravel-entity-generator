<?php

namespace RonasIT\Support\Tests\Support\Seeder;

use org\bovigo\vfs\vfsStream;

trait SeederGeneratorMockTrait
{
    public function mockFilesystem(): void
    {
        $structure = [
            'database' => [
                'seeders' => [],
            ],
        ];

        $root = vfsStream::setup('root', null, $structure);

        $databaseSeederContent = file_get_contents(getcwd() . '/tests/fixtures/SeederGeneratorTest/database_seeder_existing.php');

        vfsStream::newFile('database/seeders/DatabaseSeeder.php')
            ->at($root)
            ->setContent($databaseSeederContent);
    }
}
