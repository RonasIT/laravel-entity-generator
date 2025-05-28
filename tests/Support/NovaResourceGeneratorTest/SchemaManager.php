<?php

namespace RonasIT\Support\Tests\Support\NovaResourceGeneratorTest;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;

class SchemaManager
{
    public function listTableColumns(): array
    {
        return [
            new Column('id', new IntegerType),
            new Column('title', new StringType),
            new Column('created_at', new DateTimeType),
        ];
    }
}
