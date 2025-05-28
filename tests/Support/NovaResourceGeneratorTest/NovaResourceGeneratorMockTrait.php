<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use Doctrine\DBAL\Connection;
use Illuminate\Database\Connection as LaravelConnection;
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
        $laravelConnectionMock = Mockery::mock(LaravelConnection::class)->makePartial();
        $laravelConnectionMock
            ->expects('getConfig')
            ->andReturn(
                [
                    'dbname'   => 'my_db',
                    'user'     => 'my_user',
                    'password' => 'secret',
                    'host'     => '127.0.0.1',
                    'driver'   => 'pdo_pgsql',
                ]
            );

        $connectionMock = Mockery::mock(Connection::class)->makePartial();
        $connectionMock
            ->expects('createSchemaManager')
            ->andReturn(new SchemaManager);

        $mock = Mockery::mock('alias:' . DB::class);
        $mock
            ->expects('connection')
            ->with('pgsql')
            ->andReturn($connectionMock);

        $this->app->instance('App\\Models\\Post', new Post);
    }
}
