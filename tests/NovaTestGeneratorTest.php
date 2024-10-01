<?php

namespace RonasIT\Support\Tests;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\NovaTestGeneratorMockTrait;

class NovaTestGeneratorTest extends TestCase
{
    use NovaTestGeneratorMockTrait;

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

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'PostNovaResource'], false),
            $this->classExistsMethodCall(['nova', 'PostResource'], false),
            $this->classExistsMethodCall(['nova', 'Post'], false),
        ]);

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage("Cannot create NovaPostTest cause Post Nova resource does not exist. Create Post Nova resource.");

        try {
            app(NovaTestGenerator::class)
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
        $this->expectExceptionMessage("Cannot create NovaPostTest cause it's already exist. Remove NovaPostTest.");

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'PostNovaResource']),
            $this->classExistsMethodCall(['nova', 'NovaPostTest'])
        ]);

        try {
            app(NovaTestGenerator::class)
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

    public function testCreateWithMissingNovaPackage()
    {
        $this->expectsEvents([SuccessCreateMessage::class]);

        $functionMock = $this->mockCheckingNovaPackageExistence();

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();

        $functionMock->disable();
    }
}
