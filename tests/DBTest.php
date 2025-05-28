<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\DB as LaravelDB;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use RonasIT\Support\Support\DB;
use Mockery;

class DBTest extends TestCase
{
    public function testConnectionReturnsDoctrineConnection()
    {
        $config = [
            'database' => 'my_db',
            'username' => 'my_user',
            'password' => 'secret',
            'host'     => '127.0.0.1',
            'driver'   => 'pgsql',
        ];

        $laravelConnectionMock = Mockery::mock();
        $laravelConnectionMock
            ->shouldReceive('getConfig')
            ->andReturn($config);

        LaravelDB::shouldReceive('connection')
            ->with(null)
            ->andReturn($laravelConnectionMock);

        $doctrineConnectionMock = Mockery::mock(Connection::class);

        $driverManagerMock = Mockery::mock('alias:' . DriverManager::class);
        $driverManagerMock
            ->shouldReceive('getConnection')
            ->with([
                'dbname'   => 'my_db',
                'user'     => 'my_user',
                'password' => 'secret',
                'host'     => '127.0.0.1',
                'driver'   => 'pdo_pgsql',
            ])
            ->andReturn($doctrineConnectionMock);

        $result = DB::connection();

        $this->assertEquals($doctrineConnectionMock, $result);
    }
}