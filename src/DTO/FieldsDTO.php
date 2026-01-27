<?php

namespace RonasIT\Support\DTO;

final readonly class FieldsDTO
{
    public function __construct(
        public array $integer = [],
        public array $float = [],
        public array $string = [],
        public array $boolean = [],
        public array $json = [],
        public array $timestamp = [],
    ) {
    }
}
