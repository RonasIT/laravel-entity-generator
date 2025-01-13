<?php

namespace RonasIT\Support\Tests\Support\Test;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\TestsGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait TestMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockGenerator(): void
    {
        $this->mockClass(TestsGenerator::class, [
            [
                'function' => 'getModelClass',
                'arguments' => ['User'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\User',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['User'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\User',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Role'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Role',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Post',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['User'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\User',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Role'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Role',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Role'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Role',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Role'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Role',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['User'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\User',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['User'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\User',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Post',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Post',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Post',
            ],
        ]);
    }

    public function mockGeneratorDumpStubNotExist(): void
    {
        $this->mockClass(TestsGenerator::class, [
            [
                'function' => 'getModelClass',
                'arguments' => ['User'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\User',
            ],
            [
                'function' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\Post',
            ],
        ]);
    }

    public function mockGeneratorForCircularDependency(): void
    {
        $this->mockClass(TestsGenerator::class, [
            [
                'function' => 'getModelClass',
                'arguments' => ['CircularDep'],
                'result' => 'RonasIT\\Support\\Tests\\Support\\Test\\CircularDep',
            ],
        ]);
    }

    public function mockFilesystem(): void
    {
        $userModel = file_get_contents(getcwd() . '/tests/Support/Test/User.php');
        $roleModel = file_get_contents(getcwd() . '/tests/Support/Test/Role.php');
        $commentModel = file_get_contents(getcwd() . '/tests/Support/Test/Comment.php');
        $postModel = file_get_contents(getcwd() . '/tests/Support/Test/Post.php');
        $userFactory = file_get_contents(getcwd() . '/tests/Support/Factories/UserFactory.php');
        $roleFactory = file_get_contents(getcwd() . '/tests/Support/Factories/RoleFactory.php');
        $postFactory = file_get_contents(getcwd() . '/tests/Support/Factories/PostFactory.php');

        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => $postModel,
                    'User.php' => $userModel,
                    'Role.php' => $roleModel,
                    'Comment.php' => $commentModel,
                ],
            ],
            'database' => [
                'factories' => [
                    'UserFactory.php' => $userFactory,
                    'RoleFactory.php' => $roleFactory,
                    'PostFactory.php' => $postFactory,
                ],
            ],
            'tests' => [
                'fixtures' => [
                    'PostTest' => [],
                ]
            ]
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystemForCircleDependency(): void
    {
        $model = file_get_contents(getcwd() . '/tests/Support/Test/CircularDep.php');
        $factory = file_get_contents(getcwd() . '/tests/Support/Factories/CircularDepFactory.php');

        $structure = [
            'app' => [
                'Models' => [
                    'CircularDep.php' => $model,
                ],
            ],
            'database' => [
                'factories' => [
                    'CircularDepFactory.php' => $factory,
                ],
            ],
        ];

        vfsStream::create($structure);
    }
}
