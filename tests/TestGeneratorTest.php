<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Carbon;
use ReflectionClass;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\CircularRelationsFoundedException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\TestsGenerator;
use RonasIT\Support\Tests\Support\Test\TestMockTrait;
use Mockery;

class TestGeneratorTest extends TestCase
{
    use TestMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake([
            WarningEvent::class,
            SuccessCreateMessage::class,
        ]);
    }

    public function testMissingModel()
    {
        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create PostTest cause Post Model does not exists. "
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'.",
        );

        app(TestsGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateTests()
    {
        Carbon::setTestNow('2022-02-02');

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\DB');
        $mock
            ->shouldReceive('beginTransaction', 'rollBack')
            ->times(5);

        $this->mockGenerator();
        $this->mockFilesystem();

        app(TestsGenerator::class)
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/PostTest/dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/PostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/PostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/PostTest/update_post_request.json');
        $this->assertGeneratedFileEquals('post_test.php', 'tests/PostTest.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                'Created a new Test dump on path: tests/fixtures/PostTest/dump.sql',
                'Created a new Test fixture on path: tests/fixtures/PostTest/create_post_request.json',
                'Created a new Test fixture on path: tests/fixtures/PostTest/create_post_response.json',
                'Created a new Test fixture on path: tests/fixtures/PostTest/update_post_request.json',
                'Created a new Test: PostTest',
            ],
        ]);
    }

    public function testCreateTestsDumpStubNotExist()
    {
        config(['entity-generator.stubs.dump' => 'incorrect_stub']);

        Carbon::setTestNow('2022-02-02');

        $this->mockGeneratorDumpStubNotExist();
        $this->mockFilesystem();

        app(TestsGenerator::class)
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('tests/fixtures/PostTest/dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/PostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/PostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/PostTest/update_post_request.json');
        $this->assertGeneratedFileEquals('post_test.php', 'tests/PostTest.php');

        $this->assertEventPushedChain([
            WarningEvent::class => ['Generation of dump has been skipped cause the view incorrect_stub from the config entity-generator.stubs.dump is not exists. Please check that config has the correct view name value.'],
            SuccessCreateMessage::class => [
                'Created a new Test fixture on path: tests/fixtures/PostTest/create_post_request.json',
                'Created a new Test fixture on path: tests/fixtures/PostTest/create_post_response.json',
                'Created a new Test fixture on path: tests/fixtures/PostTest/update_post_request.json',
                'Created a new Test: PostTest',
            ],
        ]);
    }

    public function testCreateTestsTestStubNotExist()
    {
        config(['entity-generator.stubs.test' => 'incorrect_stub']);

        Carbon::setTestNow('2022-02-02');

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\DB');
        $mock
            ->shouldReceive('beginTransaction', 'rollBack')
            ->times(5);

        $this->mockGenerator();
        $this->mockFilesystem();

        app(TestsGenerator::class)
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/PostTest/dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/PostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/PostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/PostTest/update_post_request.json');
        $this->assertFileDoesNotExist('tests/PostTest.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                'Created a new Test dump on path: tests/fixtures/PostTest/dump.sql',
                'Created a new Test fixture on path: tests/fixtures/PostTest/create_post_request.json',
                'Created a new Test fixture on path: tests/fixtures/PostTest/create_post_response.json',
                'Created a new Test fixture on path: tests/fixtures/PostTest/update_post_request.json',
            ],
            WarningEvent::class => ['Generation of test has been skipped cause the view incorrect_stub from the config entity-generator.stubs.test is not exists. Please check that config has the correct view name value.'],
        ]);
    }

    public function testCreateWithCircularDependencies()
    {
        $this->assertExceptionThrew(
            className: CircularRelationsFoundedException::class,
            message: 'Circular relations founded. Please resolve you relations in models, factories and database.',
        );

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\DB');
        $mock
            ->shouldReceive('beginTransaction', 'rollBack')
            ->once();

        $this->mockGeneratorForCircularDependency();
        $this->mockFilesystemForCircleDependency();

        app(TestsGenerator::class)
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->setModel('CircularDep')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testGetModelClass()
    {
        $reflectionClass = new ReflectionClass(TestsGenerator::class);
        $method = $reflectionClass->getMethod('getModelClass');

        $method->setAccessible(true);

        $result = $method->invoke(new TestsGenerator, 'Post');

        $this->assertEquals('App\\Models\\Post', $result);
    }
}
