<?php

namespace RonasIT\Support\DTO;

use Illuminate\Support\Str;

class RelationsDTO
{
    public function __construct(
        public array $hasOne = [],
        public array $hasMany = [],
        public array $belongsTo = [],
        public array $belongsToMany = [],
    ) {
    }

    public function toArray(): array
    {
        $result = [];

        foreach ($this as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }
}