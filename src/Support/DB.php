<?php

namespace RonasIT\Support\Support;

use Illuminate\Support\Facades\DB as BaseDB;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class DB extends BaseDB
{
    public static function connection(?string $name = null): Connection
    {
        $config = parent::connection($name)->getConfig();

        $name = empty($name) ? "pdo_{$config['driver']}" : "pdo_{$name}";

        return DriverManager::getConnection([
            'dbname' => $config['database'],
            'user' => $config['username'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => $name,
        ]);
    }
}