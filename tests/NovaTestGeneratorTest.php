<?php

namespace RonasIT\Support\Tests;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestMockTrait;

class NovaTestGeneratorTest extends TestCase
{
    use NovaTestMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        $this->app->setBasePath($this->generatedFileBasePath);
    }

    public function testCreateNovaTestsResourceNotExists()
    {
        $mock = $this->mockClassExistsFunction();

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot create NovaPostTest cause Post Nova resource does not exist. Create Post Nova resource.");

        $generatorMock = $this->getGeneratorMockForNonExistingNovaResource();

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
        $this->setupConfigurations();

        $mock = $this->mockClassExistsFunction();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage("Cannot create NovaPostTest cause it's already exist. Remove NovaPostTest.");

        $generatorMock = $this->getGeneratorMockForExistingNovaResourceTest();

        try {
            $generatorMock
                ->setModel('Post')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreateWithActions()
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
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaPostTest/nova_post_dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/NovaPostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/NovaPostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/NovaPostTest/update_post_request.json');

        $functionMock->disable();
    }
}
