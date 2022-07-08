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

namespace App\Models\Traits;

use App\Models\Staff;
use App\Models\Revision;
use Venturecraft\Revisionable\RevisionableTrait;

trait HistoryTrait
{
    use RevisionableTrait;

    /**
     * Polymorphic one-to-many relationship with Revision.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    /**
     * First history value of a field.
     *
     * @param string $field
     *
     * @return mixed
     */
    public function initialVal($field)
    {
        $first_updated = $this->histories()->where('key', $field)->first();

        // If updated record history is found then return old value else by default return current field value.
        if (isset($first_updated)) {
            return $first_updated->old_value;
        }

        return $this->$field;
    }

    /**
     * Get a link related to the specified history.
     *
     * @param \App\Models\Revision $history
     *
     * @return string
     */
    public function historyLink($history)
    {
        return "<a href='" . $this->show_route . "'
                   class='like-txt'><span class='icon " . $this->icon . "' data-toggle='tooltip' data-placement='top'
                   title='" . $this->identifier_call_name . "'></span> " .
                   $this->historyVal('name', $history->created_at)['closest'] .
                "</a>";
    }

    /**
     * Get the history values of a field.
     *
     * @param string         $field
     * @param \Carbon\Carbon $point_at
     *
     * @return array
     */
    public function historyVal($field, $point_at)
    {
        $less_closest_history = $this->histories()
                                     ->where('key', $field)
                                     ->where('created_at', '<', $point_at)
                                     ->latest('id')
                                     ->first();

        // If we get the closest history before the point at,
        // then get history values from less closest history
        // and return old, new, closest field values in an array format.
        if (isset($less_closest_history)) {
            return [
                'old'     => $less_closest_history->old_value,
                'new'     => $less_closest_history->new_value,
                'closest' => $less_closest_history->new_value,
            ];
        }

        $greater_closest_history = $this->histories()
                                        ->where('key', $field)
                                        ->where('created_at', '>', $point_at)
                                        ->orderBy('id')->first();

        // If we get the closest history after the point at,
        // then get history values from greater closest history
        // and return old, new, closest field values in an array format.
        if (isset($greater_closest_history)) {
            return [
                'old'     => $greater_closest_history->old_value,
                'new'     => $greater_closest_history->new_value,
                'closest' => $greater_closest_history->old_value,
            ];
        }

        // By default return old value is null and new, closest is the current field value.
        return [
            'old'     => null,
            'new'     => $this->$field,
            'closest' => $this->$field,
        ];
    }

    /**
     * Get history ids array.
     *
     * @return array
     */
    public function getHistoryIdsAttribute()
    {
        return $this->histories()->latest('id')->pluck('id')->toArray();
    }

    /**
     * Get last history id.
     *
     * @return int
     */
    public function getLastHistoryIdAttribute()
    {
        return last($this->history_ids);
    }

    /**
     * Get the latest group of histories.
     *
     * @param int|null $user_id
     *
     * @return \App\Model\Revision
     */
    public function newUpdatedHistory($user_id = null)
    {
        $user_id = is_null($user_id) ? auth()->user()->id : $user_id;
        $last_history = Revision::whereIn('id', $this->history_ids)
                                ->where('user_id', $user_id)
                                ->orderBy('id', 'desc')
                                ->first();

        // If we have the last history, then we get all histories that are created at the same time
        // and where the user is also the same and related to the specified module resource history.
        if (isset($last_history)) {
            $updated_stories = Revision::whereIn('id', $this->history_ids)
                                       ->where('user_id', $user_id)
                                       ->orderBy('id', 'desc')
                                       ->where('created_at', $last_history->created_at)
                                       ->get();

            return $updated_stories;
        }

        return collect();
    }

    /**
     * Get the latest group of histories values array.
     *
     * @param int|null $user_id
     *
     * @return array
     */
    public function newUpdatedArray($user_id = null)
    {
        $outcome = [];
        $user_id = is_null($user_id) ? auth()->user()->id : $user_id;
        $updated_histories = $this->newUpdatedHistory($user_id);

        // If we have updated field histories, then get an array of key, field, old value, new value of each history.
        if ($updated_histories->count() > 0) {
            foreach ($updated_histories as $updated_history) {
                $key       = $updated_history->key;
                $field     = display_field($updated_history->key);
                $old_value = $updated_history->old_value;
                $new_value = $updated_history->new_value;
                $outcome[] = compact('key', 'field', 'old_value', 'new_value');
            }
        }

        return $outcome;
    }

    /**
     * Get all histories of a specified module.
     *
     * @param bool|null $get_all
     *
     * @return \App\Models\Revision
     */
    public function getAllHistoriesAttribute($get_all = null)
    {
        $all_histories = collect();
        $get_all       = is_null($get_all) ? true : $get_all;
        $history_ids   = $this->history_ids;

        // Histories are related to notes.
        if (method_exists($this, 'notes') && $this->notes()->withTrashed()->count()) {
            $note_ids            = $this->notes()->withTrashed()->pluck('id')->toArray();
            $note_info_ids       = $this->notes()->withTrashed()->pluck('note_info_id')->toArray();
            $note_histories      = Revision::where('revisionable_type', 'note')
                                           ->whereIn('revisionable_id', $note_ids)
                                           ->pluck('id')
                                           ->toArray();
            $note_info_histories = Revision::where('revisionable_type', 'note_info')
                                           ->whereIn('revisionable_id', $note_info_ids)
                                           ->pluck('id')
                                           ->toArray();

            $history_ids = push_flatten($history_ids, $note_histories);
            $history_ids = push_flatten($history_ids, $note_info_histories);
        }

        // Histories are related to the attached files.
        if (method_exists($this, 'attachfiles') && $this->attachfiles()->withTrashed()->count()) {
            $file_ids       = $this->attachfiles()->withTrashed()->pluck('id')->toArray();
            $file_histories = Revision::where('revisionable_type', 'attach_file')
                                      ->whereIn('revisionable_id', $file_ids)
                                      ->pluck('id')
                                      ->toArray();
            $history_ids    = push_flatten($history_ids, $file_histories);
        }

        // Histories are related to tasks.
        if ($get_all && method_exists($this, 'tasks') && $this->tasks()->withTrashed()->count()) {
            foreach ($this->tasks()->withTrashed()->get() as $task) {
                $task_history_ids = $task->all_histories->pluck('id')->toArray();
                $history_ids = push_flatten($history_ids, $task_history_ids);
            }
        }

        // Histories are related to issues.
        if ($get_all && method_exists($this, 'issues') && $this->issues()->withTrashed()->count()) {
            foreach ($this->issues()->withTrashed()->get() as $issue) {
                $issue_history_ids = $issue->all_histories->pluck('id')->toArray();
                $history_ids = push_flatten($history_ids, $issue_history_ids);
            }
        }

        // Histories are related to events.
        if ($get_all && method_exists($this, 'events') && $this->events()->withTrashed()->count()) {
            foreach ($this->events()->withTrashed()->get() as $event) {
                $event_history_ids = $event->all_histories->pluck('id')->toArray();
                $history_ids = push_flatten($history_ids, $event_history_ids);
            }
        }

        // Histories are related to milestones.
        if ($get_all && method_exists($this, 'milestones') && $this->milestones()->withTrashed()->count()) {
            foreach ($this->milestones()->withTrashed()->get() as $milestone) {
                $milestone_history_ids = $milestone->getAllHistoriesAttribute(false)->pluck('id')->toArray();
                $history_ids = push_flatten($history_ids, $milestone_history_ids);
            }
        }

        // Histories are related to projects.
        if ($get_all && method_exists($this, 'projects') && $this->projects()->withTrashed()->count()) {
            foreach ($this->projects()->withTrashed()->get() as $project) {
                $project_history_ids = $project->getAllHistoriesAttribute(false)->pluck('id')->toArray();
                $history_ids = push_flatten($history_ids, $project_history_ids);
            }
        }

        if (count($history_ids)) {
            $all_histories = Revision::whereIn('id', $history_ids)->where('key', '!=', 'linked_type');
        }

        return $all_histories;
    }

    /**
     * Get the latest histories of a specified module.
     *
     * @param int $limit
     *
     * @return \App\Models\Revision
     */
    public function getRecentHistoryAttribute($limit = 5)
    {
        $root_id = $this->id;
        $root    = $this->identifier;
        $limit   = is_null($limit) ? 5 : $limit;

        return $this->all_histories
                    ->latest('id')
                    ->take($limit)
                    ->get()
                    ->filter(function ($history) use ($root, $root_id) {
                        $history->root    = $root;
                        $history->root_id = $root_id;

                        return true;
                    });
    }

    /**
     * Get recent histories timeline HTML.
     *
     * @return string
     */
    public function getRecentHistoryHtmlAttribute()
    {
        $html = '';

        // Render recent histories HTML.
        if ($this->all_histories->count() > 0) {
            foreach ($this->recent_history as $history) {
                $html .= $history->timeline_html;
            }
        }

        return $html;
    }

    /**
     * Get all histories timeline HTML.
     *
     * @param int|null $latest_id
     * @param int|null $end_down
     *
     * @return string
     */
    public function getAllHistoriesHtmlAttribute($latest_id = null, $end_down = null)
    {
        $root_id = $this->id;
        $root    = $this->identifier;
        $html    = '';
        $i       = 0;

        if ($this->all_histories->count()) {
            $histories = $this->all_histories;

            // If we have the latest history and prev histories not more than 30 then disable load more histories.
            if (isset($latest_id)) {
                $histories = $histories->where('id', '<', $latest_id);
                $end_down_disable = $histories->count() < 30 ? 'disable' : '';
            }

            // Keep histories related module as root and module id as root id.
            $histories = $histories->latest('id')->take(30)->get()->filter(function ($history) use ($root, $root_id) {
                $history->root    = $root;
                $history->root_id = $root_id;

                return true;
            });

            foreach ($histories as $history) {
                $html .= $history->timeline_html;
            }
        }

        if (isset($end_down) && isset($latest_id)) {
            $html .= "<div class='timeline-info end-down " . $end_down_disable . "'>
                        <i class='load-icon fa fa-circle-o-notch fa-spin'></i>
                        <div class='timeline-icon'><a class='load-timeline'><i class='fa fa-angle-down'></i></a></div>
                     </div>";
        }

        return $html;
    }

    /**
     * Additional information on the updated history.
     *
     * @param \App\Models\Revision $history
     *
     * @return string
     */
    public function updatedHistoryInfo($history)
    {
        return '<br>' . $this->historyLink($history);
    }
}
