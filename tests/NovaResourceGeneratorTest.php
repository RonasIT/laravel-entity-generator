<?php

namespace RonasIT\Support\Tests;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Illuminate\Support\Facades\Event;
use ReflectionClass;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Support\DatabaseNovaField;
use RonasIT\Support\Tests\Support\NovaResourceGeneratorTest\NovaResourceGeneratorMockTrait;

class NovaResourceGeneratorTest extends TestCase
{
    use NovaResourceGeneratorMockTrait;

    public function testCreateWithMissingNovaPackage()
    {
        Event::fake();

        $this->mockCheckingNovaPackageExistence();

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testCreateNovaResourceWithMissingModel()
    {
        $this->mockClassExistsFunction();

        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall([], false)
        ]);

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage('Cannot create Post Nova resource cause Post Model does not exists. '
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'");

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $this->mockClassExistsFunction();

        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['nova', 'PostResource']),
        ]);

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectExceptionMessage("Cannot create PostResource cause PostResource already exists. Remove PostResource.");

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreate()
    {
        Event::fake();

        $this->mockClassExistsFunction();

        $this->mockFilesystem();

        $fields = $this->getJsonFixture('command_line_fields.json');

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->setFields($fields)
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_resource.php', 'app/Nova/PostResource.php');

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testGetModelFieldsFromDatabase()
    {
        $this->mockGettingModelInstance();

        $reflectionClass = new ReflectionClass(NovaResourceGenerator::class);
        $method = $reflectionClass->getMethod('getFieldsForCreation');
        $method->setAccessible(true);

        $generator = (new NovaResourceGenerator)
            ->setFields([])
            ->setModel('Post');

        $fields = $method->invokeArgs($generator, []);

        $this->assertEquals([
            [
                new DatabaseNovaField(new Column('id', new IntegerType)),
                new DatabaseNovaField(new Column('title', new StringType)),
                new DatabaseNovaField(new Column('created_at', new DatetimeType)),
            ],
            [
                'integer' => 'Number',
                'smallint' => 'Number',
                'bigint' => 'Number',
                'float' => 'Number',
                'decimal' => 'Number',
                'string' => 'Text',
                'text' => 'Text',
                'guid' => 'Text',
                'json' => 'Text',
                'date' => 'Date',
                'datetime' => 'DateTime',
                'datetimetz' => 'DateTime',
                'boolean' => 'Boolean',
            ]
        ], $fields);
    }
}
