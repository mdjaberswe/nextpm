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

trait ActivityTrait
{
    /**
     * Get the specified resource name html.
     *
     * @return string
     */
    public function getNameHtmlAttribute()
    {
        $tooltip = '';

        if (strlen($this->name) > 55) {
            $tooltip = "data-toggle='tooltip' data-placement='top' title='{$this->name}'";
        }

        return "<a href='{$this->show_route}' class='status-checkbox-link {$this->status->category}' {$tooltip}>" .
                    $this->closed_open_checkbox . str_limit($this->name, 55) .
               '</a>';
    }

    /**
     * Get valid parent modules list.
     *
     * @return array
     */
    public static function relatedTypes()
    {
        return self::$related_types;
    }

    /**
     * Get related parent module.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRelatedAttribute()
    {
        return $this->linked;
    }

    /**
     * Get related parent module name.
     *
     * @return string
     */
    public function getRelatedNameAttribute()
    {
        return non_property_checker($this->linked, 'name');
    }

    /**
     * Get related parent module type.
     *
     * @return string
     */
    public function getRelatedTypeAttribute()
    {
        return $this->linked_type;
    }

    /**
     * Get related parent module id.
     *
     * @return int
     */
    public function getRelatedIdAttribute()
    {
        return $this->linked_id;
    }

    /**
     * Secure data for not to insert problematic data.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function secureDateAttributes()
    {
        if (not_null_empty($this->start_date) && not_null_empty($this->due_date)) {
            return $this;
        }

        // If both start and due date are null else one of them is null.
        if (is_null($this->start_date) && is_null($this->due_date)) {
            $date = now()->format('Y-m-d');
        } else {
            $date = ! is_null($this->start_date) ? $this->start_date : $this->due_date;
        }

        $this->start_date = $date;
        $this->due_date   = $date;

        return $this;
    }

    /**
     * Get closed status.
     *
     * @return bool
     */
    public function getClosedStatusAttribute()
    {
        return $this->status->category == 'closed';
    }

    /**
     * Get status name.
     *
     * @return string
     */
    public function getStatusNameAttribute()
    {
        return $this->status->name;
    }

    /**
     * Get status checkbox html.
     *
     * @return string
     */
    public function getClosedOpenCheckboxAttribute()
    {
        $disabled   = $this->auth_can_edit ? '' : 'disabled';
        $css        = $this->closed_status == true ? 'reopen mdi-check-circle' : 'complete mdi-check-circle-outline';
        $title      = $this->closed_status == true ? 'Reopen' : fill_up_space('Mark as Closed');
        $attributes = empty($disabled) ? "data-toggle='tooltip' data-placement='top' title='{$title}' data-url='" .
                                         route('admin.' . $this->identifier . '.closed.reopen', $this->id) . "'" : '';

        return "<span class='status-checkbox mdi $css $disabled' $attributes></span>";
    }

    /**
     * Get activity status.
     *
     * @return string
     */
    public function getActivityStatusHtmlAttribute()
    {
        return "<span class='activity-status'>" . non_property_checker($this->status, 'name') . '</span>';
    }

    /**
     * Get display plain priority.
     *
     * @return string
     */
    public function getPlainPriorityAttribute()
    {
        if (not_null_empty($this->priority)) {
            return ucfirst($this->priority);
        }

        return null;
    }

    /**
     * Get color according to priority.
     *
     * @return string
     */
    public function getColorByImportanceAttribute()
    {
        $default = 'rgba(170, 200, 245, 1)';

        // Return default calendar color if priority is null.
        if (is_null($this->priority)) {
            return $default;
        }

        // Return calendar color according to the specified resource prior level.
        switch ($this->priority) {
            case 'high':
                return 'rgba(255, 135, 30, 0.8)';
            case 'highest':
                return 'rgba(255, 65, 55, 0.8)';
            case 'low':
                return 'rgba(65, 155, 115, 1)';
            case 'lowest':
                return 'rgba(50, 175, 175, 1)';
            case 'normal':
                return 'rgba(115, 155, 200, 1)';
            default:
                return $default;
        }
    }

    /**
     * Get milestones array list according to the related project.
     *
     * @return array
     */
    public function getMilestoneListAttribute()
    {
        $outcome = ['' => '-None-'];

        // Get only the related project milestones list.
        if ($this->linked_type == 'project') {
            $milestone_list = $this->linked->milestones()->get(['id', 'name'])->pluck('name', 'id')->toArray();

            if (count($milestone_list)) {
                $outcome = $outcome + $milestone_list;
            }
        }

        return $outcome;
    }
}
