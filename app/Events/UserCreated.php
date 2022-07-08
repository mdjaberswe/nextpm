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
use App\Models\Staff;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserCreated extends Event
{
    use SerializesModels;

    /**
     * The newly created user whose type is 'staff'.
     *
     * @var \App\Models\Staff
     */
    public $staff;

    /**
     * The newly created user data.
     *
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Staff $staff
     * @param array             $data
     *
     * @return void
     */
    public function __construct(Staff $staff, $data)
    {
        $this->staff = $staff;
        $this->data = $data;
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
