<?php

namespace RonasIT\Support\Tests;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaResourceMockTrait;

class NovaResourceGeneratorTest extends TestCase
{
    use NovaResourceMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        $this->app->setBasePath($this->generatedFileBasePath);
    }

    public function testCreateWithMissingNovaPackage()
    {
        $this->expectsEvents([SuccessCreateMessage::class]);

        $functionMock = $this->mockCheckingNonExistentNovaPackageExistence();

        app(NovaResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $functionMock->disable();
    }

    public function testCreateNovaResourceWithMissingModel()
    {
        $mock = $this->mockClassExistsFunction();

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage('Cannot create Post Nova resource cause Post Model does not exists. '
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'");

        $generatorMock = $this->getResourceGeneratorMockForNonExistingNovaResource();

        try {
            $generatorMock
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $mock = $this->mockClassExistsFunction();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage("Cannot create PostResource cause PostResource already exists. Remove PostResource.");

        $generatorMock = $this->getResourceGeneratorMockForExistingNovaResource();

        try {
            $generatorMock
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreate()
    {
        $functionMock = $this->mockClassExistsFunction();

        $this->mockFilesystem();
        $this->setupConfigurations();
        $this->mockViewsNamespace();
        $this->mockNovaResourceTestGenerator();

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaPostTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaPostTest/dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/NovaPostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/NovaPostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/NovaPostTest/update_post_request.json');

        $functionMock->disable();
    }
}
