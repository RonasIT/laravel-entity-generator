<?php

namespace RonasIT\Support\Exceptions;

use Exception;
use Illuminate\Support\Str;

abstract class AbstractResourceException extends Exception
{
    protected function getEntity(string $filePath): string
    {
        $fileName = Str::afterLast($filePath, '/');

        return Str::before($fileName, '.php');
    }
}