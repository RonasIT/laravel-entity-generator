<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Carbon;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\UnknownFieldTypeException;
use RonasIT\Support\Generators\MigrationGenerator;

class MigrationGeneratorTest extends TestCase
{
    public function testSetUnknownFieldType()
    {
        $this->assertExceptionThrew(
            className: UnknownFieldTypeException::class,
            message: 'Unknown field type unknown-type in MigrationGenerator.',
        );

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations([
                'belongsTo' => [],
                'belongsToMany' => [],
                'hasOne' => [],
                'hasMany' => [],
            ])
            ->setFields([
                'integer-required' => ['media_id', 'user_id'],
                'unknown-type' => ['title'],
            ])
            ->generate();
    }

    public function testCreateMigration()
    {
        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations([
                'belongsTo' => [],
                'belongsToMany' => [],
                'hasOne' => [],
                'hasMany' => [],
            ])
            ->setFields([
                'integer-required' => ['media_id', 'user_id'],
                'string' => ['title', 'body'],
                'json' => ['meta'],
                'timestamp' => ['created_at'],
            ])
            ->generate();

        $this->assertGeneratedFileEquals('migrations.php', 'database/migrations/2022_02_02_000000_posts_create_table.php');
    }

    public function testCreateMigrationMYSQL()
    {
        putenv('DB_CONNECTION=mysql');

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations([
                'belongsTo' => [],
                'belongsToMany' => [],
                'hasOne' => [],
                'hasMany' => [],
            ])
            ->setFields([
                'integer-required' => ['media_id', 'user_id'],
                'string' => ['title', 'body'],
                'json' => ['meta'],
                'timestamp' => ['created_at'],
                'timestamp-required' => ['published_at'],
            ])
            ->generate();

        $this->assertGeneratedFileEquals('generated_mysql_migration.php', 'database/migrations/2022_02_02_000000_posts_create_table.php');
    }

    public function testCreateMigrationWithoutMigrationStub(): void
    {
        config(['entity-generator.stubs.migration' => 'incorrect_stub']);

        app(MigrationGenerator::class)
            ->setModel('Post')
            ->setRelations([
                'belongsTo' => [],
                'belongsToMany' => [],
                'hasOne' => [],
                'hasMany' => [],
            ])
            ->setFields([
                'integer-required' => ['media_id', 'user_id'],
                'string' => ['title', 'body'],
                'json' => ['meta'],
                'timestamp' => ['created_at'],
            ])
            ->generate();

        $this->assertFileDoesNotExist('database/migrations/2022_02_02_000000_posts_create_table.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of migration has been skipped cause the view incorrect_stub from the config entity-generator.stubs.migration is not exists. Please check that config has the correct view name value.',
        );
    }
}
