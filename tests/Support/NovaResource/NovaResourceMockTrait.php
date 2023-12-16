<?php

namespace RonasIT\Support\Tests\Support\NovaResource;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait NovaResourceMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function getResourceGeneratorMockForNonExistingNovaResource(): MockInterface
    {
        return $this->getGeneratorMockForNonExistingNovaResource(NovaResourceGenerator::class);
    }

    public function getResourceGeneratorMockForExistingNovaResource(): MockInterface
    {
        $mock = Mockery::mock(NovaResourceGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('models', 'Post')
            ->andReturn(true);

        $mock
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'PostResource')
            ->andReturn(true);

        return $mock;
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

    public function mockGettingModelInstance()
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