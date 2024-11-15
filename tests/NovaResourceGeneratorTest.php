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

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();
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
        $this->mockNovaServiceProviderExists();

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage('Cannot create Post Nova resource cause Post Model does not exists. '
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'");

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

        $this->assertExceptionThrowed(
            className: ClassAlreadyExistsException::class,
            message: 'Cannot create PostResource cause PostResource already exists. Remove PostResource.',
        );

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testSuccess()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockFilesystem();

        $fields = $this->getJsonFixture('command_line_fields.json');

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

        $this->mockGettingModelInstance();

        $this->mockFilesystem();

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
