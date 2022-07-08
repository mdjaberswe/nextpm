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

use App\Events\IssueDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class IssueDeletedListener
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
     * @param App\Events\IssueDeleted $event
     *
     * @return void
     */
    public function handle(IssueDeleted $event)
    {
        $issue_note_info = \App\Models\NoteInfo::where('linked_type', 'issue')
                                               ->whereIn('linked_id', $event->issue_ids)
                                               ->pluck('id')
                                               ->toArray();

        // Delete all notes related to deleted issues.
        \App\Models\Note::whereIn('note_info_id', $issue_note_info)->delete();
        \App\Models\NoteInfo::where('linked_type', 'issue')->whereIn('linked_id', $event->issue_ids)->delete();
        \App\Models\Note::where('linked_type', 'issue')->whereIn('linked_id', $event->issue_ids)->delete();
    }
}
