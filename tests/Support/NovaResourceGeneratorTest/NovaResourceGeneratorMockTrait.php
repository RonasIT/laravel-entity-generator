<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Mockery;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait NovaResourceGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;
        $fileSystemMock->novaModels = [];
        $fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->setStructure();
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

    public function mockFileSystemWithoutPostModel(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = null;

        $fileSystemMock->setStructure();
    }
}
