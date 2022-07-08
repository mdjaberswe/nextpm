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

namespace App\Listeners;

use App\Events\EventDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventDeletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\EventDeleted $event
     *
     * @return void
     */
    public function handle(EventDeleted $event)
    {
        $event_note_info = \App\Models\NoteInfo::where('linked_type', 'event')
                                               ->whereIn('linked_id', $event->event_ids)
                                               ->pluck('id')
                                               ->toArray();

        // Delete all notes related to deleted events.
        \App\Models\Note::whereIn('note_info_id', $event_note_info)->delete();
        \App\Models\NoteInfo::where('linked_type', 'event')->whereIn('linked_id', $event->event_ids)->delete();
        \App\Models\Note::where('linked_type', 'event')->whereIn('linked_id', $event->event_ids)->delete();
    }
}
