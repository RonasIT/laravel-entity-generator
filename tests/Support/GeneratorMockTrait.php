<?php

namespace RonasIT\Support\Tests\Support;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Illuminate\Database\Connection as LaravelConnection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Traits\MockTrait;
use Mockery;
use Illuminate\Support\Str;

trait GeneratorMockTrait
{
    use MockTrait;

    public function mockNativeGeneratorFunctions(...$functionCalls): void
    {
        $this->mockNativeFunction('\RonasIT\Support\Generators', $functionCalls);
    }

    public function mockNovaServiceProviderExists(bool $result = true): void
    {
        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true], $result),
        );
    }

    public function classExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'classExists',
            'arguments' => $arguments,
            'result' => $result
        ];
    }

    public function doesNovaResourceExistsCall(bool $result = true): array
    {
        return [
            'function' => 'doesNovaResourceExists',
            'arguments' => [],
            'result' => $result
        ];
    }

    public function nativeClassExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'class_exists',
            'arguments' => $arguments,
            'result' => $result,
        ];
    }

    public function mockPhpFileContent(): string
    {
        return '<?php';
    }

    public function mockDBTransactionStartRollback(int $count = 1): void
    {
        DB::shouldReceive('beginTransaction')->times($count);
        DB::shouldReceive('rollBack')->times($count);
    }

    public function mockGettingModelInstance(object $model): void
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
                new Column('id', new IntegerType()),
                new Column('title', new StringType()),
                new Column('created_at', new DateTimeType()),
            ]);

        $connectionMock = Mockery::mock(Connection::class);
        $connectionMock->makePartial()
            ->expects('createSchemaManager')
            ->andReturn($schemaManagerMock);

        Mockery::mock('alias:' . DriverManager::class)
            ->shouldReceive('getConnection')
            ->with([
                'dbname' => 'my_db',
                'user' => 'my_user',
                'password' => 'secret',
                'host' => '127.0.0.1',
                'driver' => 'pdo_pgsql',
            ])
            ->andReturn($connectionMock);

        $modelName = Str::afterLast(get_class($model), '\\');

        $this->app->instance("App\\Models\\{$modelName}", $model);
    }
}
