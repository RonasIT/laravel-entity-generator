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
        $this->fileSystemMock = new FileSystemMock;
        $this->fileSystemMock->novaModels = [];
        $this->fileSystemMock->models = [
            'Post.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->setStructure();
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
        $this->fileSystemMock->models = null;

        $this->fileSystemMock->setStructure();
    }
}
