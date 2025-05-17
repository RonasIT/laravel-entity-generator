<?php

namespace RonasIT\Support\DTO;

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
        return [
            'hasOne' => $this->hasOne,
            'hasMany' => $this->hasMany,
            'belongsTo' => $this->belongsTo,
            'belongsToMany' => $this->belongsToMany,
        ];
    }
}