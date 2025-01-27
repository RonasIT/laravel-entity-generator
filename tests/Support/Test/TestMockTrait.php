<?php

namespace RonasIT\Support\Tests\Support\Test;

use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait TestMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockFilesystem(): void
    {
        $userModel = file_get_contents(getcwd() . '/tests/Support/Test/Models/User.php');
        $roleModel = file_get_contents(getcwd() . '/tests/Support/Test/Models/Role.php');
        $commentModel = file_get_contents(getcwd() . '/tests/Support/Test/Models/Comment.php');
        $postModel = file_get_contents(getcwd() . '/tests/Support/Test/Models/Post.php');
        $userFactory = file_get_contents(getcwd() . '/tests/Support/Test/Factories/UserFactory.php');
        $roleFactory = file_get_contents(getcwd() . '/tests/Support/Test/Factories/RoleFactory.php');
        $postFactory = file_get_contents(getcwd() . '/tests/Support/Test/Factories/PostFactory.php');

        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->models = [
            'Post.php' => $postModel,
            'User.php' => $userModel,
            'Role.php' => $roleModel,
            'Comment.php' => $commentModel,
        ];

        $fileSystemMock->factories = [
            'UserFactory.php' => $userFactory,
            'RoleFactory.php' => $roleFactory,
            'PostFactory.php' => $postFactory,
        ];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystemForCircleDependency(): void
    {
        $model = file_get_contents(getcwd() . '/tests/Support/Test/Models/CircularDep.php');
        $factory = file_get_contents(getcwd() . '/tests/Support/Test/Factories/CircularDepFactory.php');

        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->models = ['CircularDep.php' => $model];
        $fileSystemMock->factories = ['CircularDepFactory.php' => $factory];

        $fileSystemMock->setStructure();
    }
}
