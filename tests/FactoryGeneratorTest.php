<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\View\ViewException;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ModelFactoryNotFound;
use RonasIT\Support\Exceptions\ModelFactoryNotFoundedException;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Factory\FactoryMockTrait;

class FactoryGeneratorTest extends TestCase
{
    use FactoryMockTrait;

    public function testModelNotExists()
    {
        Event::fake();

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage("Cannot create PostFactory cause Post Model does not exists. "
            . "Create a Post Model by itself or run command 'php artisan make:entity Post --only-model'.");

        $this->getFactoryGeneratorMockForMissingModel();

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testFactoryClassExists()
    {
        Event::fake();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectExceptionMessage("Cannot create PostFactory cause PostFactory already exists. Remove PostFactory.");

        $this->getFactoryGeneratorMockForExistingFactory();

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testCannotGetContentForGenericFactory()
    {
        Event::fake();

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage("Cannot get Post Model class content cause Post Model does not exists. "
            . "Create a Post Model by itself or run command 'php artisan make:entity Post --only-model'.");

        $this->mockForFileExists('database/factories/ModelFactory.php');

        $this->mockConfigurations();
        $this->mockFilesystem();
        $this->mockFactoryGenerator();

        app(FactoryGenerator::class)
           ->setModel('Post')
           ->generate();

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testRelatedModelWithoutFactory()
    {
        Event::fake();

        $this->expectException(ModelFactoryNotFoundedException::class);
        $this->expectExceptionMessage("Not found Post factory for Post model in 'database/factories/ModelFactory.php. "
            . "Please declare a factory for Post model on 'database/factories/ModelFactory.php' "
            . "path and run your command with option '--only-tests'.");

        $this->mockConfigurations();
        $this->mockFilesystemForNonExistingRelatedModelFactory();
        $this->mockFactoryGeneratorForMissingRelatedModelFactory();

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->setFields([
                'integer-required' => ['author_id'],
                'string' => ['title'],
            ])
            ->generate();

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testRevertedModelFactoryNotExists()
    {
        $this->expectException(ModelFactoryNotFound::class);
        $this->expectExceptionMessage("Model factory for model comment not found. "
            . "Please create it and after thar you can run this command with flag '--only-tests'.");

        $this->mockConfigurations();
        $this->getMockGeneratorForMissingRevertedRelationModelFactory();
        $this->mockFilesystemForMissingRevertedRelationModelFactory();

        app(FactoryGenerator::class)
            ->setRelations([
                'hasOne' => ['comment'],
                'hasMany' => ['comment'],
                'belongsTo' => ['user'],
            ])
            ->setModel('Post')
            ->generate();
    }

    public function testAlreadyExistsFactory()
    {
        Event::fake();

        $this->mockConfigurations();
        $this->mockFactoryGeneratorForAlreadyExistsFactory();

        $this->mockForFileExists('database/factories/ModelFactory.php');

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testCreateGenericFactory()
    {
        Event::fake();

        $this->mockConfigurations();
        $this->mockFilesystemForGenericStyleCreation();
        $this->mockFactoryGeneratorForGenericTypeCreation();

        app(FactoryGenerator::class)
            ->setFields([
                'integer-required' => ['author_id'],
                'string' => ['title', 'iban', 'something'],
                'json' => ['json_text'],
            ])
            ->setRelations([
                'hasOne' => ['User'],
                'hasMany' => [],
                'belongsTo' => ['user'],
            ])
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('model_factory.php', '/database/factories/ModelFactory.php');

        Event::assertDispatched(SuccessCreateMessage::class);
    }

    public function testProcessUnknownFieldType()
    {
        $this->mockConfigurations();
        $this->mockFilesystemForGenericStyleCreation();
        $this->mockFactoryGeneratorForGenericTypeCreation();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage("Cannot generate fake data for unsupported another_type field type. "
            . "Supported custom field types are json");

        app(FactoryGenerator::class)
            ->setFields([
                'another_type' => ['some_field'],
            ])
            ->setRelations([
                'hasOne' => [],
                'hasMany' => [],
                'belongsTo' => [],
            ])
            ->setModel('Post')
            ->generate();

    }

    public function testCreateClassStyleFactory()
    {
        Event::fake();

        $this->mockConfigurationsForClassStyleFactory();
        $this->mockFilesystemForClassStyleFactoryCreation();
        $this->mockFactoryGeneratorForClassTypeCreation();

        app(FactoryGenerator::class)
            ->setFields([
                'integer-required' => ['author_id'],
                'string' => ['title', 'iban', 'something'],
                'json' => ['json_text'],
            ])
            ->setRelations([
                'hasOne' => ['user'],
                'hasMany' => [],
                'belongsTo' => ['user'],
            ])
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_factory.php', '/database/factories/PostFactory.php');

        Event::assertDispatched(SuccessCreateMessage::class);
    }
}