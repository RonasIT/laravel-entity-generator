<?php

namespace RonasIT\Support\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Tests\Support\ControllerGeneratorTest\ControllerGeneratorMockTrait;

class ControllerGeneratorTest extends TestCase
{
    use ControllerGeneratorMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testControllerAlreadyExists()
    {
        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall(['controllers', 'PostController'])
        ]);

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectExceptionMessage('Cannot create PostController cause PostController already exists. Remove PostController.');

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testModelServiceNotExists()
    {
        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall(['controllers', 'PostController'], false),
            $this->classExistsMethodCall(['services', 'PostService'], false)
        ]);

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage('Cannot create PostService cause PostService does not exists. Create a PostService by himself.');

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testRouteFileNotExists()
    {
        $this->mockFilesystemWithoutRoutesFile();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("Not found file with routes. Create a routes file on path: 'vfs://root/routes/api.php'");

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();
    }

    public function testCreate()
    {
        $this->mockFilesystem();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('created_controller.php', 'app/Http/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('api.php', 'routes/api.php');

        Event::assertDispatchedTimes(SuccessCreateMessage::class, 6);
        Event::assertDispatched(SuccessCreateMessage::class, function ($event) {
            return in_array($event->message, [
                "Created a new Route: Route::post('posts', [PostController::class, 'create']);",
                "Created a new Route: Route::put('posts/{id}', [PostController::class, 'update']);",
                "Created a new Route: Route::delete('posts/{id}', [PostController::class, 'delete']);",
                "Created a new Route: Route::get('posts/{id}', [PostController::class, 'get']);",
                "Created a new Route: Route::get('posts', [PostController::class, 'search']);",
                "Created a new Controller: PostController",
            ]);
        });
    }
}
