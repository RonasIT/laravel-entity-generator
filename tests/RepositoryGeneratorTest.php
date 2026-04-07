<?php

namespace RonasIT\EntityGenerator\Tests;

use RonasIT\EntityGenerator\Events\SuccessCreateMessage;
use RonasIT\EntityGenerator\Events\WarningEvent;
use RonasIT\EntityGenerator\Exceptions\ResourceAlreadyExistsException;
use RonasIT\EntityGenerator\Exceptions\ResourceNotExistsException;
use RonasIT\EntityGenerator\Generators\RepositoryGenerator;
use RonasIT\EntityGenerator\Tests\Support\Repository\RepositoryMockTrait;

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
            className: ResourceNotExistsException::class,
            message: 'Cannot create PostRepository cause Post does not exist. Create app/Models/Post.php and run command again.',
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
            message: 'Cannot create PostRepository cause it already exists. Remove app/Repositories/PostRepository.php and run command again.',
        );

        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();
    }
}
