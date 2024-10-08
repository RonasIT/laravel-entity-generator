<?php

namespace RonasIT\Support\Events;

use Illuminate\Queue\SerializesModels;

class WarningMessage
{
    use SerializesModels;

    public string $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
}
