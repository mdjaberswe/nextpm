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

use App\Events\ProjectDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProjectDeletedListener
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
     * @param \App\Events\ProjectDeleted $event
     *
     * @return void
     */
    public function handle(ProjectDeleted $event)
    {
        $project_note_info  = \App\Models\NoteInfo::where('linked_type', 'project')
                                                  ->whereIn('linked_id', $event->project_ids)
                                                  ->pluck('id')
                                                  ->toArray();

        $calendar_event_ids = \App\Models\Event::where('linked_type', 'project')
                                               ->whereIn('linked_id', $event->project_ids)
                                               ->pluck('id')
                                               ->toArray();

        $task_ids = \App\Models\Task::where('linked_type', 'project')
                                    ->whereIn('linked_id', $event->project_ids)
                                    ->pluck('id')
                                    ->toArray();

        $issue_ids = \App\Models\Issue::where('linked_type', 'project')
                                      ->whereIn('linked_id', $event->project_ids)
                                      ->pluck('id')
                                      ->toArray();

        // Delete all notes, events, tasks, issues related to deleted projects.
        \App\Models\Note::whereIn('note_info_id', $project_note_info)->delete();
        \App\Models\NoteInfo::where('linked_type', 'project')->whereIn('linked_id', $event->project_ids)->delete();
        \App\Models\Note::where('linked_type', 'project')->whereIn('linked_id', $event->project_ids)->delete();
        \App\Models\Milestone::whereIn('project_id', $event->project_ids)->delete();
        \App\Models\Event::whereIn('id', $calendar_event_ids)->delete();
        \App\Models\Task::whereIn('id', $task_ids)->delete();
        \App\Models\Issue::whereIn('id', $issue_ids)->delete();
    }
}
