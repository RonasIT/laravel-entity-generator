<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Mockery;
use Illuminate\Support\Facades\DB;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Connection as LaravelConnection;

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
        $laravelConnectionMock = Mockery::mock(LaravelConnection::class);
        $laravelConnectionMock
            ->shouldReceive('getConfig')
            ->andReturn([
                'database' => 'my_db',
                'username' => 'my_user',
                'password' => 'secret',
                'host' => '127.0.0.1',
                'driver' => 'pgsql',
            ]);

        DB::shouldReceive('connection')
            ->with('pgsql')
            ->andReturn($laravelConnectionMock);

        $schemaManagerMock = Mockery::mock(AbstractSchemaManager::class);
        $schemaManagerMock
            ->shouldReceive('listTableColumns')
            ->andReturn([
                new Column('id', new IntegerType),
                new Column('title', new StringType),
                new Column('created_at', new DateTimeType),
            ]);

        $connectionMock = Mockery::mock(Connection::class)->makePartial();
        $connectionMock
            ->expects('createSchemaManager')
            ->andReturn($schemaManagerMock);

        $driverManagerMock = Mockery::mock('alias:' . DriverManager::class);
        $driverManagerMock
            ->shouldReceive('getConnection')
            ->with([
                'dbname' => 'my_db',
                'user' => 'my_user',
                'password' => 'secret',
                'host' => '127.0.0.1',
                'driver' => 'pdo_pgsql',
            ])
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
