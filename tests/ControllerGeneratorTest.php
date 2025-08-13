<?php

namespace RonasIT\Support\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\View;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
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

        $this->mockFilesystem();
    }

    public function testControllerAlreadyExists()
    {
        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall(['controllers', 'PostController']),
        ]);

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: 'Cannot create PostController cause PostController already exists. Remove PostController.',
        );

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testModelServiceNotExists()
    {
        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall(['controllers', 'PostController'], false),
            $this->classExistsMethodCall(['services', 'PostService'], false),
        ]);

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create PostController cause PostService does not exists. Create a PostService by himself.',
        );

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testRouteFileNotExists()
    {
        $this->mockFilesystemWithoutRoutesFile();

        $this->assertExceptionThrew(
            className: FileNotFoundException::class,
            message: "Not found file with routes. Create a routes file on path: 'vfs://root/routes/api.php'",
        );

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();
    }

    public function testControllerStubNotExist()
    {
        View::shouldReceive('exists')
            ->with('entity-generator::controller')
            ->once()
            ->andReturnFalse();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('empty_api.php', 'routes/api.php');
        $this->assertFileDoesNotExist('app/Http/Controllers/PostController.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of controller has been skipped cause the view entity-generator::controller from the config entity-generator.stubs.controller is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testRoutesStubNotExist()
    {
       config(['entity-generator.stubs.routes' => 'incorrect_stub']);

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('created_controller.php', 'app/Http/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('empty_api.php', 'routes/api.php');

        $this->assertEventPushedChain([
            WarningEvent::class => ['Generation of routes has been skipped cause the view incorrect_stub from the config entity-generator.stubs.routes is not exists. Please check that config has the correct view name value.'],
            SuccessCreateMessage::class => ['Created a new Controller: PostController'],
        ]);
    }

    public function testUseRoutesStubNotExist()
    {
        config(['entity-generator.stubs.use_routes' => 'incorrect_stub']);

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('created_controller.php', 'app/Http/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('empty_api.php', 'routes/api.php');

        $this->assertEventPushedChain([
            WarningEvent::class => ['Generation of use routes has been skipped cause the view incorrect_stub from the config entity-generator.stubs.use_routes is not exists. Please check that config has the correct view name value.'],
            SuccessCreateMessage::class => ['Created a new Controller: PostController'],
        ]);
    }

    public function testSuccess()
    {
        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('created_controller.php', 'app/Http/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('api.php', 'routes/api.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                "Created a new Route: Route::post('posts', 'create');",
                "Created a new Route: Route::put('posts/{id}', 'update')->whereNumber('id');",
                "Created a new Route: Route::delete('posts/{id}', 'delete')->whereNumber('id');",
                "Created a new Route: Route::get('posts/{id}', 'get')->whereNumber('id');",
                "Created a new Route: Route::get('posts', 'search');",
                'Created a new Controller: PostController',
            ],
        ]);
    }
}
