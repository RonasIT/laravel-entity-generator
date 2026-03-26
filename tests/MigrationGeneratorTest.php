<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Generators\MigrationGenerator;

class MigrationGeneratorTest extends TestCase
{
    public function testCreateMigration()
    {
        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_migration_fields')))
            ->setRelations(new RelationsDTO())
            ->generate();

        $this->assertGeneratedFileEquals('migrations.php', 'database/migrations/2022_02_02_000000_posts_create_table.php');
    }

    public function testCreateMigrationMYSQL()
    {
        putenv('DB_CONNECTION=mysql');

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_migration_mysql_fields')))
            ->setRelations(new RelationsDTO())
            ->generate();

        $this->assertGeneratedFileEquals('generated_mysql_migration.php', 'database/migrations/2022_02_02_000000_posts_create_table.php');
    }

    public function testCreateMigrationWithoutMigrationStub(): void
    {
        config(['entity-generator.stubs.migration' => 'incorrect_stub']);

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_migration_fields')))
            ->setRelations(new RelationsDTO())
            ->generate();

        $this->assertFileDoesNotExist('database/migrations/2022_02_02_000000_posts_create_table.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of migration has been skipped cause the view incorrect_stub from the config entity-generator.stubs.migration is not exists. Please check that config has the correct view name value.',
        );
    }
}
