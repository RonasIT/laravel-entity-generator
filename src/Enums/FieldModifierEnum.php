<?php

namespace RonasIT\Support\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum FieldModifierEnum: string
{
    use EnumTrait;

    case Required = 'required';
    case Unique = 'unique';

    public static function tryFromAlias(string $alias): ?self
    {
        return match ($alias) {
            'r' => FieldModifierEnum::Required,
            'u' => FieldModifierEnum::Unique,
            default => null,
        };
    }
}
