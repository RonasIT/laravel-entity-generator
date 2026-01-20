<?php

namespace RonasIT\Support\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum FieldTypeEnum: string
{
    use EnumTrait;

    case Integer = 'integer';
    case Float = 'float';
    case String = 'string';
    case Boolean = 'boolean';
    case Json = 'json';
    case Timestamp = 'timestamp';
    case Array = 'array';
}
