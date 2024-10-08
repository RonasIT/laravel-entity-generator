<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\WarningMessage;
use RonasIT\Support\Generators\SeederGenerator;
use RonasIT\Support\Tests\Support\SeederGeneratorMockTrait;

class SeederGeneratorTest extends TestCase
{
    use SeederGeneratorMockTrait;

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

    public function testCreateSeederWithOldConfig()
    {
        Event::fake();
        $this->mockViewsNamespace();
        $this->mockConfigurations();
        $this->mockFilesystem();

        config([
            'entity-generator.stubs.database_empty_seeder' => 'entity-generator::database_seed_empty',
        ]);

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

        Event::assertDispatched(WarningMessage::class);
    }
}