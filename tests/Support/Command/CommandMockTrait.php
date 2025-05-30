<?php

namespace RonasIT\Support\Tests\Support\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Illuminate\Database\Connection as LaravelConnection;
use RonasIT\Support\Generators\NovaTestGenerator;
use Illuminate\Support\Facades\DB;
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
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = ['Post.php' => $this->mockPhpFileContent()];
        $fileSystemMock->config = ['entity-generator.php' => ''];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->routes = [ 'api.php' => $this->mockPhpFileContent()];
        $fileSystemMock->config = ['entity-generator.php' => ''];
        $fileSystemMock->translations = [];

        $fileSystemMock->setStructure();
    }

    public function mockGenerator(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
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

        $this->mockDBTransactionStartRollback(2);

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
}