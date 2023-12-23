<?php

namespace RonasIT\Support\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\TestsGenerator;
use RonasIT\Support\Tests\Support\Test\TestMockTrait;

class TestGeneratorTest extends TestCase
{
    use TestMockTrait;

    public function testMissingModel()
    {
        $this->mockConfigurations();

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot create Post Model cause Post Model does not exists. "
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model");

        app(TestsGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateTests()
    {
        Carbon::setTestNow('2022-02-02');

        $this->mockConfigurations();
        $this->mockGenerator();
        $this->mockFilesystem();
        $this->mockViewsNamespace();

        app()->databasePath();

        app(TestsGenerator::class)
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/PostTest/dump.sql', true);
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/PostTest/create_post_request.json', true);
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/PostTest/create_post_response.json', true);
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/PostTest/update_post_request.json', true);
        $this->assertGeneratedFileEquals('post_test.php', 'tests/PostTest.php', true);
    }
}
