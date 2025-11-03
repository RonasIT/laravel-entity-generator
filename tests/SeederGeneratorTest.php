<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Generators\SeederGenerator;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Tests\Support\Repository\RepositoryMockTrait;

class SeederGeneratorTest extends TestCase
{
    use RepositoryMockTrait;

    public function testCreateSeeder()
    {
        app(SeederGenerator::class)
            ->setRelations(new RelationsDTO(
                hasMany: ['Comment'],
                belongsTo: ['User'],
            ))
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('database_seeder.php', 'database/seeders/DatabaseSeeder.php');
        $this->assertGeneratedFileEquals('post_seeder.php', 'database/seeders/PostSeeder.php');
    }

    public function testCreateSeederEmptyDatabaseSeederStubNotExist()
    {
        config(['entity-generator.stubs.database_empty_seeder' => 'entity-generator::database_seed_empty']);

        app(SeederGenerator::class)
            ->setRelations(new RelationsDTO(
                hasMany: ['Comment'],
                belongsTo: ['User'],
            ))
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
        config(['entity-generator.stubs.seeder' => 'incorrect_stub']);

        app(SeederGenerator::class)
            ->setRelations(new RelationsDTO(
                hasMany: ['Comment'],
                belongsTo: ['User'],
            ))
            ->setModel('Post')
            ->generate();

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of seeder has been skipped cause the view incorrect_stub from the config entity-generator.stubs.seeder is not exists. Please check that config has the correct view name value.',
        );

        $this->assertFileDoesNotExist("{$this->generatedFileBasePath}/database/seeders/PostSeeder.php");
        $this->assertFileDoesNotExist('database/seeders/DatabaseSeeder.php');
    }

    public function testSeederAlreadyExists()
    {
        $this->mockClass(SeederGenerator::class, [
            $this->classExistsMethodCall(['seeders', 'PostSeeder']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: "Cannot create PostSeeder cause it already exists. Remove database/seeders/PostSeeder.php and run command again.",
        );

        app(SeederGenerator::class)
            ->setModel('Post')
            ->generate();
    }
}