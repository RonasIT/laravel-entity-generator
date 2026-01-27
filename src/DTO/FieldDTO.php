<?php

namespace RonasIT\Support\DTO;

final readonly class FieldDTO
{
    public function __construct(
        public string $name,
        public array $modifiers = [],
    ) {
    }
}
