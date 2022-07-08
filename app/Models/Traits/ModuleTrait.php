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

trait ModuleTrait
{
    /**
     * Default show page tab information.
     *
     * @param string $type
     * @param string $module
     *
     * @return string
     */
    public static function defaultInfoType($type = null, $module = null)
    {
        if (! is_null($type) && array_key_exists($type, self::informationTypes($module))) {
            return $type;
        }

        return 'overview';
    }

    /**
     * Get field list array where array index is field name and value is the display name.
     *
     * @return array
     */
    public static function fieldlist()
    {
        return self::$fieldlist;
    }

    /**
     * Get a mass updatable field list array.
     *
     * @return array
     */
    public static function massfieldlist()
    {
        return self::$mass_fieldlist;
    }

    /**
     * Import data validation.
     *
     * @param array $data
     *
     * @return array
     */
    public static function importValidate($data)
    {
        $status = true;
        $errors = [];

        if (! in_array('name', $data)) {
            $status   = false;
            $errors[] = 'The ' . self::getIdentifier() . ' name field is required.';
        }

        return ['status' => $status, 'errors' => $errors];
    }

    /**
     * Get a filterable field list array.
     *
     * @return array
     */
    public static function filterFieldList()
    {
        return self::$filter_fieldlist;
    }

    /**
     *  Get a filterable field list array where field name as key and field display name as value.
     *
     * @return array
     */
    public static function filterFieldDropDown()
    {
        $except = array_diff(array_keys(self::fieldlist()), self::filterFieldList());

        return array_except(self::fieldlist(), $except);
    }

    /**
     * Get field dropdown values array list.
     *
     * @return array
     */
    public static function getFieldValueDropdownList()
    {
        $identifier = self::getIdentifier();
        $dropdown['days'] = ['7' => '7 days', '30' => '30 days', '90' => '90 days'];
        $dropdown['access'] = [
            'private' => 'Private',
            'public' => 'Public read only',
            'public_rwd' => 'Public read/write/delete',
        ];

        if (in_array($identifier, ['task', 'issue', 'project'])) {
            $module_status = $identifier . '_status';
            $dropdown[$module_status] = \Dropdown::getArrayList($module_status, 'position');

            if ($identifier == 'issue') {
                $module_type = $identifier . '_type';
                $dropdown[$module_type] = \Dropdown::getArrayList($module_type, 'position');
            }
        }

        if (in_array($identifier, ['task', 'issue', 'event', 'project'])) {
            $admin_field = $identifier == 'project' ? 'admin_list' : $identifier . '_owner';
            $dropdown[$admin_field] = ['0' => 'Me'] + \Dropdown::getAdminUsersList();
        }

        if (in_array($identifier, ['task', 'issue', 'event'])) {
            $dropdown['related_type'] = ['' => '-None-', 'project' => 'Project'];
            $dropdown['related_to']['project'] = ['' => '-None-'] + \Dropdown::getArrayList('project');
        }

        if (in_array($identifier, ['task', 'event'])) {
            $dropdown['priority'] = \Dropdown::getPriorityList();
        } elseif ($identifier == 'issue') {
            $dropdown['severity'] = \App\Models\Issue::getSeverityDropdownList();
            $dropdown['reproducible'] = \App\Models\Issue::getReproducibleDropdownList();
        }

        return $dropdown;
    }

    /**
     * Get show page tab array list.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $module
     *
     * @return array
     */
    public static function informationTypes($module = null)
    {
        $information_types = [
            'overview' => 'Overview',
            'notes'    => 'Notes',
            'files'    => 'Files',
            'history'  => 'History',
        ];

        // If the auth user doesn't have permission to view "Note" then remove the note tab.
        if (! permit('note.view')) {
            array_forget($information_types, 'notes');
        }

        // If the auth user doesn't have permission to view "Files" then remove the file tab.
        if (! permit('attachment.view')) {
            array_forget($information_types, 'files');
        }

        return $information_types;
    }

    /**
     * Set the module's name.
     *
     * @param string $value
     *
     * @return string
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = str_replace(["'", '"'], '', $value);
    }

    /**
     * The query only gets open tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyOpen($query)
    {
        if (in_array($this->identifier, ['task', 'issue', 'project'])) {
            $status_model = morph_to_model($this->identifier . '_status');
            $open_status_ids = $status_model::where('category', 'open')->pluck('id')->toArray();

            return $query->whereIn($this->identifier . '_status_id', $open_status_ids);
        }

        return $query;
    }

    /**
     * The query only gets closed tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyClosed($query)
    {
        if (in_array($this->identifier, ['task', 'issue', 'project'])) {
            $status_model = morph_to_model($this->identifier . '_status');
            $closed_status_ids = $status_model::where('category', 'closed')->pluck('id')->toArray();

            // Task only closed if the status is closed and completion percentage is 100%
            if ($this->identifier == 'task') {
                $query = $query->where('completion_percentage', 100);
            }

            return $query->whereIn($this->identifier . '_status_id', $closed_status_ids);
        }

        return $query;
    }

    /**
     * If the related module item is not in the dropdown list then add the related module item.
     *
     * @param string $type
     * @param array  $list
     * @param bool   $init
     * @param string $permission
     *
     * @return array
     */
    public function fixRelatedDropdown(string $type, array $list, $init = false, $permission = 'create')
    {
        if ($init) {
            $list = morph_to_model($this->linked_type)::getAuthPermittedData($this->identifier, $permission)
                                                      ->get(['id', 'name'])
                                                      ->pluck('name', 'id')
                                                      ->toArray();
        }

        // If this resource related module is not in the list then add the related module to the list and fix the issue.
        if (not_null_empty($this->linked_type)
            && $this->linked_type == $type
            && ! array_key_exists($this->linked_id, $list)
        ) {
            $list = $list + [$this->linked_id => $this->linked->name];
            ksort($list);
        }

        return $list;
    }

    /**
     * Get all allowed user names in the HTML tooltip.
     *
     * @return string
     */
    public function getAllowedStaffTooltipAttribute()
    {
        $html = '';

        if ($this->allowedstaffs->count()) {
            foreach ($this->allowedstaffs as $allowed) {
                $html .= str_replace(' ', '&nbsp;', $allowed->staff->name) . '<br>';
            }
        }

        return $html;
    }

    /**
     * Get 'access' HTML according to its value.
     *
     * @return string
     */
    public function getAccessHtmlAttribute()
    {
        $access_html   = '';
        $title         = is_null($this->complete_name) ? $this->name : $this->complete_name;
        $allowed_staff = $this->allowedstaffs->count();

        if ($this->access == 'public') {
            $access_html = "Public - <span class='color-shadow sm'>Read Only</span>";
        } elseif ($this->access == 'public_rwd') {
            $access_html = "Public - <span class='color-shadow sm'>Read/Write/Delete</span>";
        } elseif ($this->access == 'private') {
            $access_html = 'Private';
            $access_css  = $this->auth_can_edit ? 'private-users' : 'cursor-text';
            $access_icon = $this->auth_can_edit ? "<i class='mdi mdi-account-multiple'></i>" : '';

            if ($allowed_staff) {
                $access_html .= " - <a class='$access_css' editid='" . $this->id .  "' type='" . $this->identifier . "'
                                       modal-title='" . $title . "' data-toggle='tooltip' data-placement='top'
                                       data-html='true' title='" . $this->allowed_staff_tooltip . "'>" .
                                       $allowed_staff . " (users)
                                    </a>";
            } else {
                $access_html .= " <a class='$access_css link-icon-md' data-toggle='tooltip' data-placement='top'
                                     title='" . $this->allowed_staff_tooltip . "Allow&nbsp;Some&nbsp;Users&nbsp;Only'
                                     editid='" . $this->id .  "' type='" . $this->identifier . "'
                                     modal-title='" . $title . "'>
                                     $access_icon
                                  </a>";
            }
        }

        return $access_html;
    }

    /**
     * Get classified completion HTML according to completion percentage value.
     *
     * @return string
     */
    public function getClassifiedCompletionAttribute()
    {
        if ($this->completion_percentage >= 0 && $this->completion_percentage <= 30) {
            $css = 'cold';
        } elseif ($this->completion_percentage > 31 && $this->completion_percentage <= 70) {
            $css = 'warm';
        } elseif ($this->completion_percentage > 70 && $this->completion_percentage <= 100) {
            $css = 'hot';
        } else {
            $css = 'cold';
        }

        $tooltip = null;

        if ($this->completion_percentage > 0
            && ! is_null($this->total_completed_activity)
            && ! is_null($this->total_activity)
        ) {
            $tooltip = '<u>' . fill_up_space('Completed tasks and issues = ') . $this->total_completed_activity .
                       '</u><br>' . fill_up_space('Total no. of tasks and issues = ') . $this->total_activity;
        }

        return "<span class='{$css}' data-toggle='tooltip' data-placement='bottom' data-html='true' title='{$tooltip}'>" .
                    "<b class='counter' data-value='{$this->completion_percentage}'>" .
                        $this->completion_percentage .
                    '</b><i>%</i>' .
               '</span>';
    }

    /**
     * Get module overview tab details information show|hide status.
     *
     * @return bool
     */
    public function getHideInfoAttribute()
    {
        $session_var = $this->identifier . '_hide_details';

        return session()->has($session_var) && session()->get($session_var) ? true : false;
    }

    /**
     * Get module form modal size.
     *
     * @return string
     */
    public function getModalSizeAttribute()
    {
        return in_array($this->identifier, ['project', 'milestone']) ? 'medium' : 'large';
    }

    /**
     * Get social media exists status of the specified resource.
     *
     * @return bool
     */
    public function getHasSocialMediaAttribute()
    {
        return in_array($this->identifier, ['staff']);
    }

    /**
     * Get the last user who last updated the specified resource.
     *
     * @return \App\Models\Staff
     */
    public function updatedBy()
    {
        $last_updated = $this->revisionHistory->last();

        // If the specified resource has social media
        // then get the user who is responsible for the last social media update.
        if ($this->has_social_media && $this->socialmedia()->count()) {
            $social_last_updated = $this->socialmedia()->latest('id')->first()->revisionHistory->last();

            if (isset($social_last_updated)
                && $social_last_updated->updated_at > non_property_checker($last_updated, 'updated_at')
            ) {
                $last_updated = $social_last_updated;
            }
        }

        // Get the user who is responsible for allowed user updates.
        if ($this->allowedstaffs()->count()) {
            $allowed_staffs_updated = $this->allowedstaffs()->latest('id')->first()->revisionHistory->last();

            if (isset($allowed_staffs_updated)
                && $allowed_staffs_updated->updated_at > non_property_checker($last_updated, 'updated_at')
            ) {
                $last_updated = $allowed_staffs_updated;
            }
        }

        if (isset($last_updated)) {
            return $last_updated->userResponsible();
        }

        return \App\Models\Staff::superAdmin();
    }

    /**
     * Get the last modified time.
     *
     * @return \Carbon\Carbon
     */
    public function getModifiedAtAttribute()
    {
        $latest_updated_at = $this->updated_at;

        // If this resource has social media then get the last time when social media was updated.
        if ($this->has_social_media && $this->socialmedia()->count()) {
            $social_last_updated = $this->socialmedia()->latest('id')->first()->revisionHistory->last();

            if (isset($social_last_updated) && $social_last_updated->updated_at > $latest_updated_at) {
                $latest_updated_at = $social_last_updated->updated_at;
            }
        }

        // Get the last time when allowed user(s) was updated.
        if ($this->allowedstaffs()->count()) {
            $allowed_staffs_updated = $this->allowedstaffs()->latest('id')->first()->revisionHistory->last();

            if (isset($allowed_staffs_updated) && $allowed_staffs_updated->updated_at > $latest_updated_at) {
                $latest_updated_at = $allowed_staffs_updated->updated_at;
            }
        }

        return $latest_updated_at;
    }

    /**
     * Gat last modified time in AmPm format.
     *
     * @return string
     */
    public function getUpdatedAmpmAttribute()
    {
        return $this->modified_at->format('M j, Y g:i A');
    }

    /**
     * Get the end date field name of a calendar item period.
     *
     * @return string
     */
    public function getEndFieldNameAttribute()
    {
        if (in_array('due_date', $this->fillable)) {
            return 'due_date';
        }

        return 'end_date';
    }

    /**
     * Get readable start date HTML.
     *
     * @return string
     */
    public function getStartDateHtmlAttribute()
    {
        // Show readable time if start date in DateTime format.
        $show_time = validateDateFormat($this->start_date);

        return $this->readableDateHtml('start_date', $show_time);
    }

    /**
     * Get readable end date HTML.
     *
     * @return string
     */
    public function getEndDateHtmlAttribute()
    {
        $end_field = $this->end_field_name;
        // Show readable time if end date in DateTime format.
        $show_time = validateDateFormat($this->$end_field);

        return $this->readableDateHtml($end_field, $show_time);
    }

    /**
     * Get the specified resource duration.
     *
     * @return int
     */
    public function getDurationAttribute()
    {
        $end_date = $this->end_field_name;

        if (! is_null($this->start_date) && ! is_null($this->$end_date)) {
            $duration = $this->carbonDate($end_date)->diffInDays($this->carbonDate('start_date'), false);

            return abs($duration) + 1;
        }

        return null;
    }

    /**
     * Get duration HTML.
     *
     * @return string
     */
    public function getDurationHtmlAttribute()
    {
        return is_null($this->duration)
               ? "<span class='color-shadow l-space1'>--</span>"
               : $this->duration . ' ' . str_plural('day', $this->duration);
    }

    /**
     * Get duration tooltip.
     *
     * @return string
     */
    public function getDurationTooltipAttribute()
    {
        $end_date = $this->end_field_name;

        if (! is_null($this->start_date) && ! is_null($this->$end_date)) {
            $title = "Start Date: {$this->readableDateHtml('start_date')}<br>Duration: {$this->duration_html}";

            return "data-toggle='tooltip' data-placement='left' data-html='true' title='{$title}'";
        }

        return null;
    }

    /**
     * Get the specified resource days remaining to complete.
     *
     * @return int
     */
    public function getDaysRemainingAttribute()
    {
        $end_date = $this->end_field_name;

        if (! is_null($this->$end_date)) {
            $days_remaining = $this->carbonDate($end_date)->diffInDays(now(), false);

            if ($this->carbonDate($end_date) < now()) {
                return -$days_remaining;
            }

            return abs($days_remaining) + 1;
        }

        return 0;
    }

    /**
     * Get days remaining HTML.
     *
     * @return string
     */
    public function getDaysRemainingHtmlAttribute()
    {
        if ($this->days_remaining > 0 || $this->completion_percentage == 100) {
            $css = 'cold';
        } elseif ($this->days_remaining == 0) {
            $css = 'warm';
        } elseif ($this->days_remaining < 0) {
            $css = 'hot';
        } else {
            $css = 'cold';
        }

        return "<span class='{$css}'>" . min_zero($this->days_remaining) . '<i>d</i></span>';
    }

    /**
     * Get due date HTML.
     *
     * @return string
     */
    public function getDueDateHtmlAttribute()
    {
        return "<span {$this->duration_tooltip}>{$this->readableDateHtml($this->end_field_name)}</span>";
    }

    /**
     * Get due date staus 'CSS' class according to overdue days.
     *
     * @return string
     */
    public function getDueDateStatusAttribute()
    {
        $passed_val = $this->passedDateVal($this->end_field_name);

        if ($passed_val == 0) {
            $status = 'warning';
        } elseif ($passed_val < 0) {
            $status = 'success';
        } else {
            $status = 'danger';
        }

        return $status;
    }

    /**
     * Get overdue days value.
     *
     * @return int
     */
    public function getOverdueDaysAttribute()
    {
        $end_field = $this->end_field_name;
        $outcome   = $this->carbonDate($end_field)->diffInDays(now());

        // If the specified resource end date is less than today and is not closed.
        if ($this->$end_field < date('Y-m-d') && ! $this->closed_status) {
            return abs($outcome);
        }

        return $this->$end_field == date('Y-m-d') ? 0 : -($outcome + 1);
    }

    /**
     * Get overdue days HTML.
     *
     * @return string
     */
    public function getOverdueDaysHtmlAttribute()
    {
        if ($this->overdue_days > 0) {
            return "<p data-toggle='tooltip' data-placement='bottom' title='" . $this->readableDate('due_date') . "'>" .
                        "<span class='color-danger'>" .
                            fill_up_space('late by ' . $this->overdue_days . ' ' . str_plural('day', $this->overdue_days)) .
                        '</span>' .
                    '</p>';
        }

        return "<p class='color-shadow l-space1'>--</p>";
    }

    /**
     * Get notes HTML of this specified resource.
     *
     * @param int       $latest_id
     * @param bool|null $end_down
     *
     * @return string
     */
    public function getNotesHtmlAttribute($latest_id = null, $end_down = null)
    {
        $html = '';
        $i    = 0;

        // If this resource has notes then get the latest 10 notes.
        if ($this->notes->count()) {
            $notes = $this->notes()->wherePin(0);

            if (isset($latest_id)) {
                $notes = $notes->where('id', '<', $latest_id);
            }

            $notes = $notes->latest()->take(10)->get();

            foreach ($notes as $note) {
                $i++;
                $top   = $i == 1 ? true : null;
                $html .= $note->getNoteHtmlAttribute($top);
            }
        }

        // If has more notes then enable bottom load, else disable to load more.
        if (isset($end_down) && isset($latest_id)) {
            $end_down_disable = $this->notes()->where('id', '<', $latest_id)->count() < 11 ? 'disable' : '';
            $html .= "<div class='timeline-info end-down " . $end_down_disable . "'>
                        <i class='load-icon fa fa-circle-o-notch fa-spin'></i>
                        <div class='timeline-icon'><a class='load-timeline'><i class='fa fa-angle-down'></i></a></div>
                     </div>";
        }

        return $html;
    }

    /**
     * Get pin note HTML of this specified resource.
     *
     * @return string
     */
    public function getPinNoteHtmlAttribute()
    {
        $pin_note = $this->notes()->wherePin(1)->get();

        if ($pin_note->count()) {
            $pin_note = $pin_note->first();
            $this->notes()->where('id', '!=', $pin_note->id)->update(['pin' => 0]);

            return $pin_note->getNoteHtmlAttribute(null, null, true);
        }

        return null;
    }

    /**
     * Get users list who must notify any action related to this specified resource.
     *
     * @param bool|null $without_followers
     *
     * @return array
     */
    public function getNotifeesAttribute($without_followers = null)
    {
        $notifees   = [];
        $notifees[] = non_property_checker($this->owner, 'user_id');

        // If the module is "Milestone", then add related project owner as notifees.
        // If the module is "Project", then add all project members as notifees.
        // If the module is "Event", then add all event attendees as notifees.
        if ($this->identifier == 'milestone') {
            $notifees[] = $this->project->owner->user_id;
        } elseif ($this->identifier == 'project' && $this->members->count()) {
            $notifees = push_flatten($notifees, \App\Models\User::pluckTypeId('staff', $this->members->pluck('id')->toArray()));
        } elseif ($this->identifier == 'event' && $this->attendees->count()) {
            $notifees = push_flatten($notifees, $this->attendees->pluck('user_id')->toArray());
        }

        // Add allowed users as notifees.
        if (method_exists($this, 'allowedstaffs') && $this->allowedstaffs->count()) {
            $notifees = push_flatten($notifees, $this->allowedstaffs->pluck('user_id')->toArray());
        }

        if (! is_null($without_followers) && $without_followers == true) {
            return $notifees;
        } else {
            if (method_exists($this, 'followers') && $this->followers->count()) {
                $notifees = push_flatten($notifees, $this->followers()
                                                         ->get()
                                                         ->where('notifiable', true)
                                                         ->pluck('user_id')
                                                         ->toArray());
            }
        }

        return $notifees;
    }

    /**
     * Get staff list who must notify any action related to this specified resource.
     *
     * @param bool|null $without_followers
     *
     * @return array
     */
    public function getStaffNotifeesAttribute($without_followers = null)
    {
        $notifees   = [];
        $notifees[] = non_property_checker($this->owner, 'id');

        // If the module is "Milestone", then add related project owner as notifees.
        // If the module is "Project", then add all project members as notifees.
        // If the module is "Event", then add all event attendees as notifees.
        if ($this->identifier == 'milestone') {
            $notifees[] = $this->project->owner->id;
        } elseif ($this->identifier == 'project' && $this->members->count()) {
            $notifees = push_flatten($notifees, $this->members->pluck('id')->toArray());
        } elseif ($this->identifier == 'event' && $this->attendees->where('linked_type', 'staff')->count()) {
            $notifees = push_flatten($notifees, $this->attendees
                                                     ->where('linked_type', 'staff')
                                                     ->pluck('linked_id')
                                                     ->toArray());
        }

        // Add allowed staff as notifees.
        if ($this->allowedstaffs->count()) {
            $notifees = push_flatten($notifees, $this->allowedstaffs->pluck('staff_id')->toArray());
        }

        if (! is_null($without_followers) && $without_followers == true) {
            return $notifees;
        } else {
            if ($this->followers->count()) {
                $notifees = push_flatten($notifees, $this->followers()
                                                         ->get()
                                                         ->where('notifiable', true)
                                                         ->pluck('staff_id')
                                                         ->toArray());
            }
        }

        return $notifees;
    }

    /**
     * Get to know the auth user can follow or not.
     *
     * @return bool
     */
    public function getCanFollowAttribute()
    {
        if (in_array(auth()->user()->id, $this->getNotifeesAttribute(true))) {
            return false;
        }

        return $this->access != 'private' || ($this->access == 'private' && $this->auth_can_view);
    }

    /**
     * Get to know the auth user following status.
     *
     * @return bool
     */
    public function getFollowStatusAttribute()
    {
        return $this->followers->where('staff_id', auth_staff()->id)->count() ? true : false;
    }

    /**
     * Get sorted followers according to authenticate user, created date.
     *
     * @return array
     */
    public function getSortedFollowersAttribute()
    {
        return $this->followers()
                    ->whereNotIn('staff_id', $this->getStaffNotifeesAttribute(true))
                    ->orderBy('created_at')
                    ->groupBy('staff_id')
                    ->get()
                    ->sortByDesc(function ($follower, $key) {
                        if ($follower->staff_id == auth_staff()->id) {
                            return 1;
                        } else {
                            return 0;
                        }
                    })->flatten()->all();
    }

    /**
     * Get event attendees HTML.
     *
     * @param int $limit
     *
     * @return string
     */
    public function getFollowersHtmlAttribute($limit = 2)
    {
        $html  = '';
        $limit = isset($limit) ? $limit : 2;

        // By default show 3 followers and if more than three then show two and one link to show the rest followers.
        foreach ($this->sorted_followers as $key => $follower) {
            if ($key < $limit) {
                $html .= "<a href='{$follower->staff->show_route}' class='avatar-link' data-toggle='tooltip'
                            data-placement='top' title='" . fill_up_space($follower->staff->name) . "'>" .
                            "<img src='{$follower->staff->avatar}'>" .
                         '</a>';

                if ($key == ($limit - 1)) {
                    if (count($this->sorted_followers) == ($limit + 1)) {
                        $html .= "<a href='{$this->sorted_followers[$limit]->staff->show_route}' class='avatar-link'
                                    data-toggle='tooltip' data-placement='top'
                                    title='" . fill_up_space($this->sorted_followers[$limit]->staff->name) . "'>
                                    <img src='{$this->sorted_followers[$limit]->staff->avatar}'>" .
                                 '</a>';
                    } elseif (count($this->sorted_followers) > ($limit + 1)) {
                        $count = count($this->sorted_followers) - $limit;
                        $html .= "<a class='avatar-link further add-multiple' modal-sub-title='{$this->name}'
                                   data-action='' modal-title='{$this->identifier_call_name} Followers'
                                   modal-datatable='true' datatable-url='follower-data/{$this->identifier}/{$this->id}'
                                   data-content='user.partials.modal-follower' save-new='false-all' cancel-txt='Close'>"
                                   . $count .
                                 '</a>';
                    }

                    break;
                }
            }
        }

        return $html;
    }

    /**
     * Render followers container HTML.
     *
     * @return string
     */
    public function getDisplayFollowersAttribute()
    {
        if (count($this->sorted_followers)) {
            return "<div class='timeline-sidebox follower-container float-sm-auto-md-right'>
                        <h4>Followers (<span class='follower-count'>" . count($this->sorted_followers) . "</span>)</h4>
                        <span class='parallel-avatar follower-box'>{$this->followers_html}</span>
                    </div>";
        }

        return null;
    }

    /**
     * Get show page miscellaneous actions.
     *
     * @return string
     */
    public function getShowMiscActionsAttribute()
    {
        $html = '';

        // If the auth user can follow or delete the specified resource.
        if ($this->can_follow || $this->auth_can_delete) {
            $html .= "<a class='btn thiner btn-regular dropdown-toggle' animation='fadeIn|fadeOut'
                         data-toggle='dropdown' aria-expanded='false'><i class='mdi mdi-dots-vertical fa-md pe-va'></i>
                      </a>
                      <ul class='dropdown-menu up-caret'>";

            if ($this->can_follow) {
                $follow = 1;
                $icon   = 'mdi-eye';
                $text   = 'Follow';

                if ($this->follow_status) {
                    $follow = 0;
                    $icon   = 'mdi-eye-off-outline';
                    $text   = 'Unfollow';
                }

                $html .= "<li>
                            <a class='follow-record' data-type='{$this->identifier}' data-id='{$this->id}'
                               data-follow='{$follow}'><i class='mdi $icon'></i><span class='status-text'>$text</span>
                            </a>
                          </li>";
            }

            if ($this->auth_can_delete) {
                $html .= '<li>' .
                            \Form::open(['route' => ['admin.' . $this->identifier . '.destroy', $this->id], 'method' => 'delete']) .
                                \Form::hidden('id', $this->id) .
                                \Form::hidden('redirect', true) .
                                "<button type='submit' class='delete' data-item='{$this->identifier}'>
                                    <i class='mdi mdi-delete'></i> Delete
                                </button>" .
                            \Form::close() .
                         '</li>';
            }

            $html .= '</ul>';
        }

        return $html;
    }
}
