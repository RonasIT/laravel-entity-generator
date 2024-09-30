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
        $this->expectExceptionMessage("Cannot create NovaSomePostTest cause SomePost Nova resource does not exist. Create SomePost Nova resource.");

        $generatorMock = $this->getGeneratorMockForNonExistingNovaResource();

        try {
            $generatorMock
                ->setModel('SomePost')
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
        $this->expectExceptionMessage("Cannot create NovaSomePostTest cause it's already exist. Remove NovaSomePostTest.");

        $generatorMock = $this->getGeneratorMockForExistingNovaResourceTest();

        try {
            $generatorMock
                ->setModel('SomePost')
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
            ->setModel('SomePost')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaSomePostTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaSomePostTest/nova_some_post_dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/NovaSomePostTest/create_some_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/NovaSomePostTest/create_some_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/NovaSomePostTest/update_some_post_request.json');

        $functionMock->disable();
    }
}
