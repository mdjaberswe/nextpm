<?php
/**
 * NextPM - Open Source Project Management Script
 * Copyright (c) Muhammad Jaber. All Rights Reserved
 *
 * Email: mdjaber.swe@gmail.com
 *
 * LICENSE
 * --------
 * Licensed under the Apache License v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EventDeleted extends Event
{
    use SerializesModels;

    /**
     * All of the deleted event ids.
     *
     * @var array
     */
    public $event_ids;

    /**
     * Create a new event instance.
     *
     * @param array $event_ids
     *
     * @return void
     */
    public function __construct(array $event_ids)
    {
        $this->event_ids = $event_ids;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
