<?php

namespace RonasIT\Support\Support;

use Illuminate\Support\Facades\DB as BaseDB;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class DB
{
    public static function connection(string $driver): Connection
    {
        $laravelConnection = BaseDB::connection();
        $config = $laravelConnection->getConfig();

        return DriverManager::getConnection([
            'dbname' => $config['database'],
            'user' => $config['username'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => "pdo_{$driver}",
        ]);
    }
}