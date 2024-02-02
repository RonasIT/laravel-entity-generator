<?php

namespace RonasIT\Support\Tests;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use org\bovigo\vfs\vfsStream;
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

    public function setUp(): void
    {
        parent::setUp();

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        $this->app->setBasePath($this->generatedFileBasePath);
    }

    public function testCreateWithMissingNovaPackage()
    {
        $this->expectsEvents([SuccessCreateMessage::class]);

        $functionMock = $this->mockCheckingNovaPackageExistence();

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $functionMock->disable();
    }

    public function testCreateNovaResourceWithMissingModel()
    {
        $mock = $this->mockClassExistsFunction();

        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall([], false)
        ]);

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage('Cannot create Post Nova resource cause Post Model does not exists. '
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'");

        try {
            app(NovaResourceGenerator::class)
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $mock = $this->mockClassExistsFunction();

        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['nova', 'PostResource']),
        ]);

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage("Cannot create PostResource cause PostResource already exists. Remove PostResource.");

        try {
            app(NovaResourceGenerator::class)
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreate()
    {
        $this->expectsEvents(SuccessCreateMessage::class);

        $functionMock = $this->mockClassExistsFunction();

        $this->mockFilesystem();

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->setFields([
                'boolean' => ['is_published'],
                'string-required' => ['title', 'body'],
                'integer' => ['id'],
                'non_existing_type' => ['comment'],
                'json' => [],
                'timestamp-required' => [],
            ])
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_resource.php', 'app/Nova/PostResource.php');

        $functionMock->disable();
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
