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

namespace App\Jobs;

use App\Models\EventAttendee;
use App\Jobs\Job;

class SaveEventAttendee extends Job
{
    protected $event;
    protected $attendee;
    protected $attendee_type;
    protected $sync;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Event $event
     * @param array             $attendee
     *
     * @return void
     */
    public function __construct($event, $attendee, $sync = false)
    {
        $this->event         = $event;
        $this->attendee      = $attendee;
        $this->attendee_type = $this->setAttendeeType();
        $this->sync          = $sync;
    }

    /**
     * Set valid attendee type
     *
     * @return array
     */
    public function setAttendeeType()
    {
        return ['staff'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->sync) {
            $this->event->attendees()->delete();
        }

        // Save event attendees with proper user type
        if (isset($this->attendee) && count($this->attendee)) {
            foreach ($this->attendee as $attendee) {
                $divider = strpos($attendee, '-');
                $type    = substr($attendee, 0, $divider);
                $id      = substr($attendee, $divider + 1);

                if (in_array($type, $this->attendee_type)) {
                    $find = morph_to_model($type)::find($id);
                    $attendee_exists = EventAttendee::where('event_id', $this->event->id)
                                                    ->where('linked_id', $id)
                                                    ->where('linked_type', $type)
                                                    ->count();

                    if (! is_null($find) && ! $attendee_exists) {
                        $event_attendee              = new EventAttendee;
                        $event_attendee->event_id    = $this->event->id;
                        $event_attendee->linked_id   = $id;
                        $event_attendee->linked_type = $type;
                        $event_attendee->save();
                    }
                }
            }
        }
    }
}
