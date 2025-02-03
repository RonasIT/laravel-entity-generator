<?php

namespace RonasIT\Support\Tests\Support\Command;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Generators\TestsGenerator;
use RonasIT\Support\Tests\Support\Command\Models\Post;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Tests\Support\NovaResourceGeneratorTest\SchemaManager;
use Mockery;

trait CommandMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystemPostModelExists(): void
    {
        $structure = [
            'app' => [
                'Http' => [
                    'Controllers' => [],
                ],
                'Models' => [
                    'Post.php' => '<?php'
                ],
                'Repositories' => []
            ],
            'config' => [
                'entity-generator.php' => ''
            ],
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->routes = [ 'api.php' => $this->mockPhpFileContent()];
        $fileSystemMock->config = ['entity-generator.php' => ''];
        $fileSystemMock->translations = [];

        $fileSystemMock->setStructure();
    }

    public function mockGenerator(): void
    {
        $this->mockClass(TestsGenerator::class, [
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
        ]);

        $this->mockClass(NovaResourceGenerator::class, [
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
        ]);

        $this->mockClass(NovaTestGenerator::class, [
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'getModelClass',
                arguments: ['Post'],
                result: 'RonasIT\\Support\\Tests\\Support\\Command\\Models\\Post',
            ),
            $this->functionCall(
                name: 'loadNovaActions',
                result: [],
            ),
            $this->functionCall(
                name: 'loadNovaFields',
                result: [],
            ),
            $this->functionCall(
                name: 'loadNovaFilters',
                result: [],
            ),
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Post', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Post', true]),
        );
    }

    public function mockGettingModelInstance(): void
    {
        $connectionMock = Mockery::mock(Connection::class)->makePartial();
        $connectionMock
            ->expects('getDoctrineSchemaManager')
            ->andReturn(new SchemaManager);

        $mock = Mockery::mock('alias:' . DB::class);
        $mock
            ->expects('connection')
            ->with('pgsql')
            ->andReturn($connectionMock);

        $mock->shouldReceive('beginTransaction', 'rollBack');

        $this->app->instance('App\\Models\\Post', new Post());
    }
}