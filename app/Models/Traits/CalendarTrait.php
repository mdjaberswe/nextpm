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

trait CalendarTrait
{
    /**
     * Get the title of the calendar item.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return $this->attributes['name'];
    }

    /**
     * Get the name of the calendar item.
     *
     * @return string
     */
    public function getItemAttribute()
    {
        return $this->identifier;
    }

    /**
     * Get the start date of the calendar item period.
     *
     * @return string
     */
    public function getStartAttribute()
    {
        return $this->attributes['start_date'];
    }

    /**
     * Get the end date of the calendar item period.
     *
     * @return string
     */
    public function getEndAttribute()
    {
        if ($this->identifier == 'event') {
            return $this->attributes[$this->end_field_name];
        }

        // FullCalendar js plugin issue solved by adding 1 day.
        return get_date_from(1, $this->attributes[$this->end_field_name]);
    }

    /**
     * Get the color of the calendar item.
     *
     * @return string
     */
    public function getColorAttribute()
    {
        // If multiples modules (like task, issue, milestone, etc.) render in a combined calendar
        // then the item color represents individual module color.
        if (! is_null($this->combined) && $this->combined == true) {
            return module_color($this->identifier);
        }

        return $this->color_by_importance;
    }

    /**
     * Get the position update URL of the calendar item.
     *
     * @return string.
     */
    public function getPositionUrlAttribute()
    {
        return route('admin.' . $this->route . '.calendar.update.position');
    }

    /**
     * Get updated calendar item JSON formatted attributes.
     *
     * @return string
     */
    public function getUpdateCalendarAttribute()
    {
        return json_encode([
            'id'              => $this->id,
            'title'           => $this->name,
            'start'           => $this->start,
            'end'             => $this->end,
            'color'           => $this->color,
            'base_url'        => $this->base_url,
            'show_route'      => $this->show_route,
            'item'            => $this->identifier,
            'position_url'    => $this->position_url,
            'auth_can_edit'   => $this->auth_can_edit,
            'auth_can_delete' => $this->auth_can_delete,
        ]);
    }

    /**
     * Query within a period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $start
     * @param string                                $end
     * @param bool                                  $get_from_filterview
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithinPeriod($query, $start, $end, $get_from_filterview = false)
    {
        // Get start and end date from "Filter View" params.
        if ($get_from_filterview == true) {
            $current_filter = \App\Models\FilterView::getCurrentFilter('dashboard');
            $dates = time_period_dates($current_filter->getParamVal('timeperiod'));
            $start = $dates['start_date'];
            $end   = $dates['end_date'];
        }

        // If both dates are null or empty then don't query.
        if (null_or_empty($start) || null_or_empty($end)) {
            return $query;
        }

        // Query:   Start date within the period.
        // orWhere: End date within the period.
        // orWhere: The period between the start and end date.
        // orWhere: Both dates are null and created date within the period.
        return $query->where(function ($query) use ($start, $end) {
            $query->where($this->table . '.start_date', '>=', $start)
                  ->where($this->table . '.start_date', '<=', $end);
        })->orWhere(function ($query) use ($start, $end) {
            $query->where($this->table . '.' . $this->end_field_name, '>=', $start)
                  ->where($this->table . '.' . $this->end_field_name, '<=', $end);
        })->orWhere(function ($query) use ($start, $end) {
            $query->where($this->table . '.start_date', '<', $start)
                  ->where($this->table . '.' . $this->end_field_name, '>', $end);
        })->orWhere(function ($query) use ($start, $end) {
            $query->where(function ($query) {
                $query->where($this->table . '.start_date', null)
                      ->orWhere($this->table . '.' . $this->end_field_name, null);
            })->where('created_at', '>=', $start)->where('created_at', '<=', $end);
        });
    }

    /**
     * The query for overdue items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $today
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query, $today)
    {
        // if today is true, then the item end date is equal to today
        // else, the item end date is less than today.
        if ($today == true) {
            return $query->where($this->table . '.' . $this->end_field_name, date('Y-m-d'));
        } else {
            return $query->where($this->table . '.' . $this->end_field_name, '<', date('Y-m-d'));
        }

        return $query;
    }

    /**
     * Get Gantt horizontal bar information.
     *
     * @return string
     */
    public function getGanttInfoAttribute()
    {
        $info = str_limit($this->name, 55);
        $owner_field = $this->owner_field;

        if (not_null_empty($this->$owner_field)) {
            $info .= '<br>Owner: ' . str_limit($this->owner->name, 50);
        }

        if (not_null_empty($this->duration)) {
            $info .= '<br>Duration: ' . $this->duration_html;
        }

        return $info;
    }
}
