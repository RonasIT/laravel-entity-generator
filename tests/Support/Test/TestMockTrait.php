<?php

namespace RonasIT\Support\Tests\Support\Test;

use Illuminate\Database\Eloquent\Factory;
use Mockery;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\TestsGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait TestMockTrait
{
    use GeneratorMockTrait;

    public function mockGenerator()
    {
        $mock = Mockery::mock(Factory::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->expects('isLegacyFactory')
            ->zeroOrMoreTimes()
            ->with('')
            ->andReturn(false);

        $this->app->instance(Factory::class, $mock);

        $this->mockClass(TestsGenerator::class, [
            [
                'method' => 'getModelClass',
                'arguments' => ['User'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\User'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['User'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\User'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['User'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\Post'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['User'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\Post'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\Post'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\Post'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\Post'
            ],
            [
                'method' => 'getModelClass',
                'arguments' => ['Post'],
                'result' => '\\RonasIT\\Support\\Tests\\Support\\Test\\Post'
            ]
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.test' => 'entity-generator::test',
            'entity-generator.stubs.dump' => 'entity-generator::dumps.pgsql',
            'entity-generator.paths' => [
                'tests' => 'tests',
                'models' => 'app/Models',
                'factory' => 'database/factories',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $userModel = file_get_contents(getcwd() . '/tests/Support/Test/User.php');
        $postModel = file_get_contents(getcwd() . '/tests/Support/Test/Post.php');
        $userFactory = file_get_contents(getcwd() . '/tests/Support/Test/UserFactory.php');
        $postFactory = file_get_contents(getcwd() . '/tests/Support/Test/PostFactory.php');

        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => $postModel,
                    'User.php' => $userModel
                ],
            ],
            'database' => [
                'factories' => [
                    'UserFactory.php' => $userFactory,
                    'PostFactory.php' => $postFactory,
                ]
            ],
            'tests' => [
                'fixtures' => [
                    'PostTest' => []
                ]
            ]
        ];

        vfsStream::create($structure);
    }
}