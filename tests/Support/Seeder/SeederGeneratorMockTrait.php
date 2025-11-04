<?php

namespace RonasIT\Support\Tests\Support\Seeder;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait SeederGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $structure = [
            'database' => [
                'seeders' => [],
            ],
        ];

        $root = vfsStream::setup('root', null, $structure);

        $databaseSeederContent = file_get_contents(getcwd() . '/tests/fixtures/SeederGeneratorTest/existed_database_seeder.php');

        vfsStream::newFile('database/seeders/DatabaseSeeder.php')
            ->at($root)
            ->setContent($databaseSeederContent);
    }
}
