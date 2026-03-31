<?php

namespace RonasIT\EntityGenerator\Tests\Support\Seeder;

use RonasIT\EntityGenerator\Tests\Support\FileSystemMock;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

trait SeederGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();
        $fileSystemMock->seeders = [
            'DatabaseSeeder.php' => file_get_contents(getcwd() . '/tests/fixtures/SeederGeneratorTest/existed_database_seeder.php'),
        ];

        $fileSystemMock->setStructure();
    }
}
