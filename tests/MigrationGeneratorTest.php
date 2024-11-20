<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Carbon;
use ReflectionClass;
use RonasIT\Support\Exceptions\UnknownFieldTypeException;
use RonasIT\Support\Generators\MigrationGenerator;
use RonasIT\Support\Tests\Support\Migration\MigrationMockTrait;

class MigrationGeneratorTest extends TestCase
{
    use MigrationMockTrait;

    public function testSetUnknownFieldType()
    {
        $this->setupConfigurations();

        $this->expectException(UnknownFieldTypeException::class);
        $this->expectExceptionMessage('Unknown field type unknown-type in MigrationGenerator.');

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
        Carbon::setTestNow('2022-02-02');

        $this->mockFilesystem();
        $this->setupConfigurations();

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

    public function testMethodForMYSQLConnection()
    {
        putenv('DB_CONNECTION=mysql');

        $generator = app(MigrationGenerator::class);

        $reflectionClass = new ReflectionClass(MigrationGenerator::class);
        $jsonLineMethod = $reflectionClass->getMethod('getJsonLine');
        $requiredLineMethod = $reflectionClass->getMethod('getRequiredLine');

        $jsonLineMethod->setAccessible(true);
        $requiredLineMethod->setAccessible(true);

        $jsonLineResult = $jsonLineMethod->invoke($generator, 'meta');
        $requiredLineResult = $requiredLineMethod->invoke($generator, 'created_at', 'timestamp-required');

        $this->assertEquals("\$table->json('meta')->nullable();", $jsonLineResult);
        $this->assertEquals("\$table->timestamp('created_at')->nullable();", $requiredLineResult);
    }
}
