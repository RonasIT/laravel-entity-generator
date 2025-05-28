<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use RonasIT\Support\Support\DB;
use Mockery;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

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
        $schemaManagerMock = Mockery::mock(AbstractSchemaManager::class);
        $schemaManagerMock
            ->shouldReceive('listTableColumns')
            ->andReturn(
                [
                    new Column('id', new IntegerType),
                    new Column('title', new StringType),
                    new Column('created_at', new DateTimeType),
                ],
            );

        $connectionMock = Mockery::mock(Connection::class)->makePartial();
        $connectionMock
            ->expects('createSchemaManager')
            ->andReturn($schemaManagerMock);

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
