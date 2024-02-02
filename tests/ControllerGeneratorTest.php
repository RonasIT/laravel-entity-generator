<?php

namespace RonasIT\Support\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use org\bovigo\vfs\vfsStream;
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

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        $this->app->setBasePath($this->generatedFileBasePath);
    }

    public function testControllerAlreadyExists()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage('Cannot create PostController cause PostController already exists. Remove PostController.');

        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall(['controllers', 'PostController'])
        ]);

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testModelServiceNotExists()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage('Cannot create PostService cause PostService does not exists. Create a PostService by himself.');

        $this->mockClass(ControllerGenerator::class, [
            $this->classExistsMethodCall(['controllers', 'PostController'], false),
            $this->classExistsMethodCall(['services', 'PostService'], false)
        ]);

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testRouteFileNotExists()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectErrorMessage("Not found file with routes. Create a routes file on path: 'vfs://root/routes/api.php'");

        $this->mockFilesystemWithoutRoutesFile();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();
    }

    public function testCreate()
    {
        $this->expectsEvents(SuccessCreateMessage::class);

        $this->mockFilesystem();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_controller.php', 'app/Http/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('api.php', 'routes/api.php');
    }
}
