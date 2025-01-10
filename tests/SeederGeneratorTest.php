<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Generators\SeederGenerator;
use RonasIT\Support\Tests\Support\SeederGeneratorMockTrait;

class SeederGeneratorTest extends TestCase
{
    use SeederGeneratorMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testCreateSeeder()
    {
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

        $this->assertGeneratedFileEquals('database_seeder.php', 'database/seeders/DatabaseSeeder.php');
        $this->assertGeneratedFileEquals('post_seeder.php', 'database/seeders/PostSeeder.php');
    }

    public function testCreateSeederEmptyDatabaseSeederStubNotExist()
    {
        $this->mockFilesystem();

        config(['entity-generator.stubs.database_empty_seeder' => 'entity-generator::database_seed_empty']);

        app(SeederGenerator::class)
            ->setRelations([
                'hasOne' => [],
                'belongsTo' => ['User'],
                'hasMany' => ['Comment'],
                'belongsToMany' => []
            ])
            ->setModel('Post')
            ->generate();

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of database empty seeder has been skipped cause the view entity-generator::database_seed_empty from the config entity-generator.stubs.database_empty_seeder is not exists. Please check that config has the correct view name value.',
        );

        $this->assertFileDoesNotExist("{$this->generatedFileBasePath}/database/seeders/PostSeeder.php");
        $this->assertFileDoesNotExist('database/seeders/DatabaseSeeder.php');
    }

    public function testCreateSeederEntityDatabaseSeederStubNotExist()
    {
        $this->mockFilesystem();

        config(['entity-generator.stubs.seeder' => 'incorrect_stub']);

        app(SeederGenerator::class)
            ->setRelations([
                'hasOne' => [],
                'belongsTo' => ['User'],
                'hasMany' => ['Comment'],
                'belongsToMany' => []
            ])
            ->setModel('Post')
            ->generate();

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of seeder has been skipped cause the view incorrect_stub from the config entity-generator.stubs.seeder is not exists. Please check that config has the correct view name value.',
        );

        $this->assertFileDoesNotExist("{$this->generatedFileBasePath}/database/seeders/PostSeeder.php");
        $this->assertFileDoesNotExist('database/seeders/DatabaseSeeder.php');
    }
}