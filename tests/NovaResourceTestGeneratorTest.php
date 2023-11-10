<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaResourceTestGenerator;
use RonasIT\Support\Tests\Support\NovaResourceMockTrait;

class NovaResourceTestGeneratorTest extends TestCase
{
    use NovaResourceMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        putenv('FAIL_EXPORT_JSON=false');

        vfsStream::setup();

        $this->app->setBasePath(vfsStream::url('root'));
    }

    public function testCreateForNonExistingNovaResource()
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

    public function testCreateForExistingNovaResourceTest()
    {
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

        app(NovaResourceTestGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertTrue($this->generatedFileExists('tests/NovaPostTest.php'));
        $this->assertTrue($this->generatedFileExists('tests/fixtures/NovaPostTest/dump.sql'));
        $this->assertTrue($this->generatedFileExists('tests/fixtures/NovaPostTest/create_post_request.json'));
        $this->assertTrue($this->generatedFileExists('tests/fixtures/NovaPostTest/create_post_response.json'));
        $this->assertTrue($this->generatedFileExists('tests/fixtures/NovaPostTest/update_post_request.json'));

        $testClassContent = $this->loadFileContent('tests/NovaPostTest.php');
        $dumpContent = $this->loadFileContent('tests/fixtures/NovaPostTest/dump.sql');
        $createPostRequestContent = $this->loadJSONContent('tests/fixtures/NovaPostTest/create_post_request.json');
        $createPostResponseContent = $this->loadJSONContent('tests/fixtures/NovaPostTest/create_post_response.json');
        $updatePostRequestContent = $this->loadJSONContent('tests/fixtures/NovaPostTest/update_post_request.json');

        $this->rollbackToDefaultBasePath();

        $this->assertEqualsFixture('created_resource_test.php', $testClassContent);
        $this->assertEqualsFixture('dump.sql', $dumpContent);
        $this->assertEqualsFixture('create_post_request.json', $createPostRequestContent);
        $this->assertEqualsFixture('create_post_response.json', $createPostResponseContent);
        $this->assertEqualsFixture('update_post_request.json', $updatePostRequestContent);

        $functionMock->disable();
    }
}
