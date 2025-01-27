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
        $userModel = file_get_contents(getcwd() . '/tests/Support/Test/User.php');
        $roleModel = file_get_contents(getcwd() . '/tests/Support/Test/Role.php');
        $commentModel = file_get_contents(getcwd() . '/tests/Support/Test/Comment.php');
        $postModel = file_get_contents(getcwd() . '/tests/Support/Test/Post.php');
        $userFactory = file_get_contents(getcwd() . '/tests/Support/Factories/UserFactory.php');
        $roleFactory = file_get_contents(getcwd() . '/tests/Support/Factories/RoleFactory.php');
        $postFactory = file_get_contents(getcwd() . '/tests/Support/Factories/PostFactory.php');

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
        $model = file_get_contents(getcwd() . '/tests/Support/Test/CircularDep.php');
        $factory = file_get_contents(getcwd() . '/tests/Support/Factories/CircularDepFactory.php');

        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->models = ['CircularDep.php' => $model];
        $fileSystemMock->factories = ['CircularDepFactory.php' => $factory];

        $fileSystemMock->setStructure();
    }
}
