<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\FieldsSchemaDTO;
use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\UnknownFieldTypeException;
use RonasIT\Support\Generators\MigrationGenerator;

class MigrationGeneratorTest extends TestCase
{
    public function testSetUnknownFieldType()
    {
        $this->assertExceptionThrew(
            className: UnknownFieldTypeException::class,
            message: 'Unknown field type unknown in MigrationGenerator.',
        );

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO())
            ->setFields(FieldsSchemaDTO::fromArray([
                'integer' => [
                    [
                        'name' => 'media_id',
                        'modifiers' => ['required'],
                    ],
                    [
                        'name' => 'user_id',
                        'modifiers' => ['required'],
                    ],
                ],
                'unknown' => [
                    [
                        'name' => 'title',
                        'modifiers' => ['unknown'],
                    ],
                ],
            ]))
            ->generate();
    }

    public function testCreateMigration()
    {
        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO())
            ->setFields(FieldsSchemaDTO::fromArray($this->getJsonFixture('create_migration_fields')))
            ->generate();

        $this->assertGeneratedFileEquals('migrations.php', 'database/migrations/2022_02_02_000000_posts_create_table.php');
    }

    public function testCreateMigrationMYSQL()
    {
        putenv('DB_CONNECTION=mysql');

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO())
            ->setFields(FieldsSchemaDTO::fromArray($this->getJsonFixture('create_migration_mysql_fields')))
            ->generate();

        $this->assertGeneratedFileEquals('generated_mysql_migration.php', 'database/migrations/2022_02_02_000000_posts_create_table.php');
    }

    public function testCreateMigrationWithoutMigrationStub(): void
    {
        config(['entity-generator.stubs.migration' => 'incorrect_stub']);

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO())
            ->setFields(FieldsSchemaDTO::fromArray($this->getJsonFixture('create_migration_fields')))
            ->generate();

        $this->assertFileDoesNotExist('database/migrations/2022_02_02_000000_posts_create_table.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of migration has been skipped cause the view incorrect_stub from the config entity-generator.stubs.migration is not exists. Please check that config has the correct view name value.',
        );
    }
}
