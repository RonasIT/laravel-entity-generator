<?php

namespace RonasIT\Support\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use org\bovigo\vfs\vfsStream;
use ReflectionClass;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ModelFactoryNotFound;
use RonasIT\Support\Exceptions\ModelFactoryNotFoundedException;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Factory\FactoryMockTrait;

class FactoryGeneratorTest extends TestCase
{
    use FactoryMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        $this->app->setBasePath($this->generatedFileBasePath);
    }

    public function testModelNotExists()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot create PostFactory cause Post Model does not exists. "
            . "Create a Post Model by itself or run command 'php artisan make:entity Post --only-model'.");

        $mock = $this->getFactoryGeneratorMockForMissingModel();
        $mock
            ->setModel('Post')
            ->generate();
    }

    public function testFactoryClassExists()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage("Cannot create PostFactory cause PostFactory already exists. Remove PostFactory.");

        $mock = $this->getFactoryGeneratorMockForExistingFactory();
        $mock
            ->setModel('Post')
            ->generate();
    }

    public function testCannotGetContentForGenericFactory()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot get Post Model class content cause Post Model does not exists. "
            . "Create a Post Model by itself or run command 'php artisan make:entity Post --only-model'.");

        $funMock = $this->getMockForFileExists();

        $this->mockConfigurations();
        $this->mockFilesystem();
        $this->mockFactoryGenerator();

        try {
            app(FactoryGenerator::class)
                ->setModel('Post')
                ->generate();
        } finally {
            $funMock->disable();
        }
    }

    public function testRelatedModelWithoutFactory()
    {
        $mock = $this->getMockForFileExists();

        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ModelFactoryNotFoundedException::class);
        $this->expectErrorMessage("Not found User factory for User model in 'database/factories/ModelFactory.php "
            . "Please declare a factory for User model on 'database/factories/ModelFactory.php' path and run your command with option '--only-tests'.");

        $this->mockConfigurations();
        $this->mockFilesystemForNonExistingRelatedModelFactory();
        $this->mockFactoryGeneratorForMissingRelatedModelFactory();

        try {
            app(FactoryGenerator::class)
                ->setModel('Post')
                ->setFields([
                    'integer-required' => ['author_id'],
                    'string' => ['title']
                ])
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testRevertedModelFactoryNotExists()
    {
        $this->expectException(ModelFactoryNotFound::class);
        $this->expectErrorMessage("Model factory for model comment not found. "
            . "Please create it and after thar you can run this command with flag '--only-tests'.");

        $mock = $this->getMockForFileExists();

        $this->mockConfigurations();
        $this->mockViewsNamespace();
        $this->mockGeneratorForMissingRevertedRelationModelFactory();
        $this->mockFilesystemForMissingRevertedRelationModelFactory();

        try {
            app(FactoryGenerator::class)
                ->setRelations([
                    'hasOne' => ['comment'],
                    'hasMany' => ['comment'],
                    'belongsTo' => ['user']
                ])
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testAlreadyExistsFactory()
    {
        $this->expectsEvents([SuccessCreateMessage::class]);

        $this->mockConfigurations();
        $this->mockFactoryGeneratorForAlreadyExistsFactory();

        $mock = $this->getMockForFileExists();

        try {
            app(FactoryGenerator::class)
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreate()
    {
        $this->expectsEvents([SuccessCreateMessage::class]);

        $this->mockConfigurations();
        $this->mockViewsNamespace();
        $this->mockFilesystemForCreation();
        $this->mockFactoryGeneratorForCreation();

        app(FactoryGenerator::class)
            ->setFields([
                'integer-required' => ['author_id'],
                'string' => ['title', 'iban']
            ])
            ->setRelations([
                'hasOne' => ['User'],
                'hasMany' => [],
                'belongsTo' => ['user']
            ])
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('model_factory.php', '/database/factories/ModelFactory.php', true);
    }
}
