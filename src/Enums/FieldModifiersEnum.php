<?php

namespace RonasIT\Support\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum FieldModifiersEnum: string
{
    use EnumTrait;

    case Required = 'required';
}
