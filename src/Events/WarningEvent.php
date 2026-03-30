<?php

namespace RonasIT\EntityGenerator\Events;

use Illuminate\Queue\SerializesModels;

class WarningEvent
{
    use SerializesModels;

    public function __construct(
        public readonly string $message,
    ) {
    }
}
