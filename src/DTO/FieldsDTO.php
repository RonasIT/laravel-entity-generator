<?php

namespace RonasIT\Support\DTO;

use IteratorAggregate;
use Traversable;
use ArrayIterator;

final readonly class FieldsDTO implements IteratorAggregate
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

    public function getIterator(): Traversable
    {
        return new ArrayIterator([
            'integer' => $this->integer,
            'float' => $this->float,
            'string' => $this->string,
            'boolean' => $this->boolean,
            'json' => $this->json,
            'timestamp' => $this->timestamp,
        ]);
    }
}
