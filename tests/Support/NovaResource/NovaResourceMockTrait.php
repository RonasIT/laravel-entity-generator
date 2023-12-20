<?php

namespace RonasIT\Support\Tests\Support\NovaResource;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Mockery;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait NovaResourceMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function mockResourceGeneratorForNonExistingNovaResource(): void
    {
        $this->mockClass(NovaResourceGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function mockResourceGeneratorForExistingNovaResource(): void
    {
        $this->mockClass(NovaResourceGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true
            ],
            [
                'method' => 'classExists',
                'arguments' => ['nova', 'PostResource'],
                'result' => true
            ]
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
        $connection = $this->mockClassWithReturn(Connection::class, ['getDoctrineSchemaManager'], true);

        $mock = Mockery::mock('alias:' . DB::class);
        $mock
            ->expects('connection')
            ->with('pgsql')
            ->andReturn($connection);

        $connection
            ->expects($this->once())
            ->method('getDoctrineSchemaManager')
            ->willReturn(new SchemaManager);

        $this->app->instance('App\\Models\\Post', new Post);
    }
}