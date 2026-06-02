<?php

namespace RonasIT\EntityGenerator\Exceptions;

class CircularRelationsFoundedException extends EntityCreateException
{
    public function __construct()
    {
        parent::__construct("Circular relations found.\nPlease resolve your relations in models, factories and database.");
    }
}
