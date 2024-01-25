<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Mockery;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait NovaResourceGeneratorMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function mockResourceGeneratorForNonExistingNovaResource(): void
    {
        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall(null, null, false)
        ]);
    }

    public function mockResourceGeneratorForExistingNovaResource(): void
    {
        $this->mockClass(NovaResourceGenerator::class, [
            $this->classExistsMethodCall('models', 'Post'),
            $this->classExistsMethodCall('nova', 'PostResource'),
        ]);
    }

    public function setupConfigurations(): void
    {
        config([
            'entity-generator.stubs.nova_resource' => 'entity-generator::nova_resource',
            'entity-generator.paths' => [
                'nova' => 'app/Nova',
                'models' => 'app/Models'
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Nova' => [],
                'Models' => [
                    'Post.php' => '<?php'
                ]
            ],
        ];

        vfsStream::create($structure);
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

        $this->app->instance('App\\Models\\Post', new Post);
    }
}