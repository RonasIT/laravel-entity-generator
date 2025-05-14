<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\View\ViewException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\IncorrectClassPathException;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Factory\FactoryMockTrait;

class FactoryGeneratorTest extends TestCase
{
    use FactoryMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        vfsStream::newDirectory('config')->at($this->rootDirectory);
        vfsStream::newDirectory('routes')
            ->at($this->rootDirectory)
            ->addChild(new vfsStreamDirectory('api.php'));
    }

    public function testModelNotExists()
    {
        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create PostFactory cause Post Model does not exists. "
            . "Create a Post Model by itself or run command 'php artisan make:entity Post --only-model'.",
        );

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testFactoryClassExists()
    {
        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: "Cannot create PostFactory cause PostFactory already exists. Remove PostFactory.",
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
        $this->mockFilesystem();

        $this->assertExceptionThrew(
            className: ViewException::class,
            message: "Cannot generate fake data for unsupported another_type field type. "
            . "Supported custom field types are json",
        );

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

    public function testCreateSuccess()
    {
        $this->mockFilesystem();

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

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Factory: PostFactory',
        );
    }

    public function testCreateFactoryWithoutFactoryStub(): void
    {
        $this->mockFilesystem();

        config(['entity-generator.stubs.factory' => 'incorrect_stub']);

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

        $this->assertFileDoesNotExist('app/Database/Factories/PostFactory.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of factory has been skipped cause the view incorrect_stub from the config entity-generator.stubs.factory is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testHasntFolderCorrectCase(): void
    {
        $this->mockFilesystem();

        Config::set('entity-generator.paths.factories', 'dAtaAbase/FactoorieesS');

        $this->expectException(IncorrectClassPathException::class);

        $this->expectExceptionMessage('Incorrect path to factories, dAtaAbase folder must start with a capital letter, please specify the path according to the PSR.');

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
    }

    public function testRemovePhpExtension(): void
    {
        $this->mockFilesystem();

        Config::set('entity-generator.paths.factories', 'database/factories/Factory.php');

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

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Factory: PostFactory',
        );
    }
}
