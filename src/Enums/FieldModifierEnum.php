<?php

namespace RonasIT\Support\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum FieldModifierEnum: string
{
    use EnumTrait;

    case Required = 'required';
    case Present = 'present';
    case Nullable = 'nullable';
}
