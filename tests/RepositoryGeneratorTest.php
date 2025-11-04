<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Generators\RepositoryGenerator;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Tests\Support\Repository\RepositoryMockTrait;

class RepositoryGeneratorTest extends TestCase
{
    use RepositoryMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockFilesystem();
    }

    public function testModelNotExist()
    {
        $this->mockClass(RepositoryGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post'], false),
        ]);

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create PostRepository cause Post Model does not exists. "
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'.",
        );

        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('repository.php', 'app/Repositories/PostRepository.php');
    }

    public function testCreateRepository()
    {
        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('repository.php', 'app/Repositories/PostRepository.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Repository: PostRepository',
        );
    }

    public function testCreateRepositoryStubNotExist()
    {
        config(['entity-generator.stubs.repository' => 'incorrect_stub']);

        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('repository.php', 'app/Repositories/PostRepository.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of repository has been skipped cause the view incorrect_stub from the config entity-generator.stubs.repository is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testRepositoryAlreadyExists()
    {
        $this->mockClass(RepositoryGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['repositories', 'PostRepository']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: "Cannot create PostRepository cause it already exists. Remove app/Repositories/PostRepository.php and run command again.",
        );

        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();
    }
}
