<?php

namespace RonasIT\Support\Events;

use Illuminate\Queue\SerializesModels;

class WarningEvent
{
    use SerializesModels;

    public function __construct(
        public readonly string $message,
    ) {
    }
}
