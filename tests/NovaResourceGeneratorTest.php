<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\FieldsDTO;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Exceptions\ResourceNotExistsException;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Tests\Support\NovaResourceGeneratorTest\NovaResourceGeneratorMockTrait;
use RonasIT\Support\Tests\Support\NovaResourceGeneratorTest\Post;

class NovaResourceGeneratorTest extends TestCase
{
    use NovaResourceGeneratorMockTrait;

    public function setUp(): void
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
            className: ResourceNotExistsException::class,
            message: 'Cannot create PostResource cause Post does not exist. Create app/Models/Post.php and run command again.',
        );

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall(['models', 'Forum/Post']),
            $this->classExistsMethodCall(['nova', 'Forum/PostResource']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostResource cause it already exists. Remove app/Nova/Forum/PostResource.php and run command again.',
        );

        app(NovaResourceGenerator::class)
            ->setModel('Forum/Post')
            ->generate();
    }

    public function testNovaResourceStubNotExist()
    {
        $this->mockNovaServiceProviderExists();

        $fields = $this->getJsonFixture('command_line_fields');

        config(['entity-generator.stubs.nova_resource' => 'incorrect_stub']);

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->setFields($this->getFieldsDTO($fields))
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
            ->setFields($this->getFieldsDTO($fields))
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
            ->setFields($this->getFieldsDTO())
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
