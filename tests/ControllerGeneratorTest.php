<?php

namespace RonasIT\Support\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Tests\Support\Controller\ControllerMockTrait;

class ControllerGeneratorTest extends TestCase
{
    use ControllerMockTrait;

    public function testControllerAlreadyExists()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage('Cannot create PostController cause PostController already exists. Remove PostController.');

        $this->mockControllerGeneratorForExistingController();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testModelServiceNotExists()
    {
        $this->getFiredEvents([SuccessCreateMessage::class]);
        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage('Cannot create PostService cause PostService does not exists. Create a PostService by himself.');

        $this->mockControllerGeneratorForNotExistingService();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testRouteFileNotExists()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectErrorMessage("Not found file with routes. Create a routes file on path: 'vfs://root/routes/api.php'");

        $this->mockFilesystemWithoutRoutesFile();
        $this->mockConfigurations();
        $this->mockViewsNamespace();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();
    }

    public function testCreate()
    {
        $this->expectsEvents(SuccessCreateMessage::class);

        $this->mockFilesystem();
        $this->mockConfigurations();
        $this->mockViewsNamespace();

        app(ControllerGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_controller.php', 'app/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('api.php', 'routes/api.php');

        $this->removeRecursivelyGeneratedFolders(getcwd() . '/vfs:');
        $this->removeRecursivelyGeneratedFolders(getcwd() . '/tests/vfs:');
    }

    protected function removeRecursivelyGeneratedFolders(string $path): void
    {
        $dirs = glob($path . '/*');

        foreach($dirs as $dir) {
            $scan = glob(rtrim($dir, '/').'/*');

            foreach($scan as $nestedDirPath) {
                $this->removeRecursivelyGeneratedFolders($nestedDirPath);
            }

            rmdir($dir);
        }

        if (file_exists($path)) {
            rmdir($path);
        }
    }
}
