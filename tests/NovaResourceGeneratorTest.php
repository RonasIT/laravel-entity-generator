<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Tests\Support\NovaResourceGeneratorTest\NovaResourceGeneratorMockTrait;
use RonasIT\Support\Tests\Support\NovaResourceGeneratorTest\Post;

class NovaResourceGeneratorTest extends TestCase
{
    use NovaResourceGeneratorMockTrait;

    public function setUp():void
    {
        parent::setUp();

        $this->mockFilesystem();
    }

    public function testCreateWithMissingNovaPackage()
    {
        $this->mockNovaServiceProviderExists(false);

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Nova is not installed and NovaResource is skipped',
        );
    }

    public function testCreateNovaResourceWithMissingModel()
    {
        $this->mockFileSystemWithoutPostModel();

        $this->mockNovaServiceProviderExists();

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create Post Nova resource cause Post Model does not exists. '
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'"
        );

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['nova', 'PostResource']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create App\Nova\PostResource cause it already exists. Remove App\Nova\PostResource and run command again.',
        );

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testNovaResourceStubNotExist()
    {
        $this->mockNovaServiceProviderExists();

        $fields = $this->getJsonFixture('command_line_fields');

        config(['entity-generator.stubs.nova_resource' => 'incorrect_stub']);

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->setFields($fields)
            ->generate();

        $this->assertFileDoesNotExist('app/Nova/PostResource.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of nova resource has been skipped cause the view incorrect_stub from the config entity-generator.stubs.nova_resource is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testSuccess()
    {
        $this->mockNovaServiceProviderExists();

        $fields = $this->getJsonFixture('command_line_fields');

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->setFields($fields)
            ->generate();

        $this->assertGeneratedFileEquals('created_resource.php', 'app/Nova/PostResource.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Nova Resource: PostResource',
        );
    }

    public function testSuccessWithoutCommandLineFields()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockGettingModelInstance(new Post());

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->generate();

        $this->assertGeneratedFileEquals(
            fixtureName: 'created_resource_without_command_line_fields.php',
            filePath: 'app/Nova/PostResource.php',
        );

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Nova Resource: PostResource',
        );
    }
}
