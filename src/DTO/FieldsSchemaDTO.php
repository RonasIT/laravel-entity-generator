<?php

namespace RonasIT\Support\DTO;

class FieldsSchemaDTO
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

    public static function fromArray(array $fields): self
    {
        $dto = new self();

        foreach ($fields as $type => $values) {
            $dto->{$type} = $values;
        }

        return $dto;
    }

    public function toArray(): array
    {
        return [
            'boolean' => $this->boolean,
            'integer' => $this->integer,
            'float' => $this->float,
            'string' => $this->string,
            'json' => $this->json,
            'timestamp' => $this->timestamp,
        ];
    }
}
