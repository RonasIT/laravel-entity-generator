<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\RepositoryGenerator;
use RonasIT\Support\Tests\Support\Repository\RepositoryMockTrait;

class RepositoryGeneratorTest extends TestCase
{
    use RepositoryMockTrait;

    public function testModelDoesntExists()
    {
        $this->mockGeneratorForMissingModel();

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot create Post Model cause Post Model does not exists. "
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'.");

        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateRepository()
    {
        $this->mockConfigurations();
        $this->mockViewsNamespace();
        $this->mockFilesystem();

        app(RepositoryGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('repository.php', 'app/Repositories/PostRepository.php');
    }
}
