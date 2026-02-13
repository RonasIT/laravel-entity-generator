<?php

namespace RonasIT\EntityGenerator\DTO;

readonly class RelationsDTO
{
    public function __construct(
        public array $hasOne = [],
        public array $hasMany = [],
        public array $belongsTo = [],
        public array $belongsToMany = [],
    ) {
    }
}
