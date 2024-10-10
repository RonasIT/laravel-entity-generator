<?php

namespace RonasIT\Support\Events;

use Illuminate\Queue\SerializesModels;

class WarningEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public readonly string $message,
    ) {
    }
}
