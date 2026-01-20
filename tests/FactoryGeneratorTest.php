<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\IncorrectClassPathException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Exceptions\ResourceNotExistsException;
use RonasIT\Support\Exceptions\UnknownFieldTypeException;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Factory\FactoryMockTrait;

class FactoryGeneratorTest extends TestCase
{
    use FactoryMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockFilesystem();
    }

    public function testModelNotExists()
    {
        $this->mockFileSystemWithoutPostModel();

        $this->assertExceptionThrew(
            className: ResourceNotExistsException::class,
            message: 'Cannot create PostFactory cause Post does not exist. Create app/Models/Post.php and run command again.',
        );

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testFactoryClassExists()
    {
        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostFactory cause it already exists. Remove database/factories/PostFactory.php and run command again.',
        );

        $this->mockFactoryGenerator(
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['factories', 'PostFactory']),
        );

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testProcessUnknownFieldType()
    {
        $this->assertExceptionThrew(
            className: UnknownFieldTypeException::class,
            message: "Unknown field type another_type in Entity Generator.",
        );

        app(FactoryGenerator::class)
            ->setFields($this->getFieldsDTO([
                'another_type' => ['some_field'],
            ]))
            ->setRelations(new RelationsDTO())
            ->setModel('Post')
            ->generate();
    }

    public function testCreateSuccess()
    {
        app(FactoryGenerator::class)
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_factory_fields')))
            ->setRelations(new RelationsDTO(
                hasOne: ['user'],
                belongsTo: ['user'],
            ))
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_factory.php', '/database/factories/PostFactory.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Factory: PostFactory',
        );
    }

    public function testCreateFactoryWithoutFactoryStub(): void
    {
        config(['entity-generator.stubs.factory' => 'incorrect_stub']);

        app(FactoryGenerator::class)
            ->setFields($this->getFieldsDTO([
                'integer' => ['author_id:required'],
                'string' => ['title', 'iban', 'something'],
                'json' => ['json_text'],
            ]))
            ->setRelations(new RelationsDTO(
                hasOne: ['user'],
                belongsTo: ['user'],
            ))
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('app/Database/Factories/PostFactory.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of factory has been skipped cause the view incorrect_stub from the config entity-generator.stubs.factory is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testConfigFolderWithIncorrectCase(): void
    {
        $this->mockFilesystem();

        Config::set('entity-generator.paths.factories', 'dAtaAbase/FactoorieesS');

        $this->expectException(IncorrectClassPathException::class);

        $this->expectExceptionMessage('Incorrect path to factories, dAtaAbase folder must start with a capital letter, please specify the path according to the PSR.');

        app(FactoryGenerator::class)
            ->setFields($this->getFieldsDTO([
                'integer' => ['author_id:required'],
                'string' => ['title', 'iban', 'something'],
                'json' => ['json_text'],
            ]))
            ->setRelations(new RelationsDTO(
                hasOne: ['user'],
                belongsTo: ['user'],
            ))
            ->setModel('Post')
            ->generate();
    }

    public function testConfigFolderWithExtension(): void
    {
        $this->mockFilesystem();

        Config::set('entity-generator.paths.factories', 'database/factories/Factory.php');

        app(FactoryGenerator::class)
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_factory_fields')))
            ->setRelations(new RelationsDTO(
                hasOne: ['user'],
                belongsTo: ['user'],
            ))
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_factory.php', '/database/factories/PostFactory.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Factory: PostFactory',
        );
    }
}
