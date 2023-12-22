<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Exceptions\EntityCreateException;
use RonasIT\Support\Generators\SeederGenerator;
use RonasIT\Support\Tests\Support\Seeder\SeederMockTrait;

class SeederGeneratorTest extends TestCase
{
    use SeederMockTrait;

    public function testMissingConfigs()
    {
        $this->expectException(EntityCreateException::class);
        $this->expectErrorMessage('Looks like you have deprecated configs.
                Please follow instructions(https://github.com/RonasIT/laravel-entity-generator/blob/master/ReadMe.md#13');

        app(SeederGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateSeeder()
    {
        $this->mockViewsNamespace();
        $this->mockConfigurations();
        $this->mockFilesystem();

        app(SeederGenerator::class)
            ->setRelations([
                'hasOne' => [],
                'belongsTo' => ['User'],
                'hasMany' => ['Comment'],
                'belongsToMany' => []
            ])
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('database_seeder.php', 'database/seeders/DatabaseSeeder.php');
        $this->assertGeneratedFileEquals('post_seeder.php', 'database/seeders/PostSeeder.php');
    }

    public function testCreateLegacySeeder()
    {

    }
}
