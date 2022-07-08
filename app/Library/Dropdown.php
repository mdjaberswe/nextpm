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

namespace App\Library;

class Dropdown
{
    /**
     * Get a dropdown array list of two columns.
     *
     * @param string $morph
     * @param string $order_field
     * @param array  $column
     *
     * @return array
     */
    public static function getArrayList($morph, $order_field = 'id', $column = ['id', 'name'])
    {
        return morph_to_model($morph)::orderBy($order_field)->get()->pluck($column[1], $column[0])->toArray();
    }

    /**
     * Get the parent array list according to child permission.
     *
     * @param string $parent
     * @param string $child
     * @param string $permission
     * @param array  $column
     *
     * @return array
     */
    public static function getPermittedList($parent, $child, $permission = 'create', $column = ['id', 'name'])
    {
        return morph_to_model($parent)::getAuthPermittedData($child, $permission)
                                      ->get()
                                      ->pluck($column[1], $column[0])
                                      ->toArray();
    }

    /**
     * Get mass updatable field list array where field name as key and field display name as value.
     *
     * @param string $morph
     *
     * @return array
     */
    public static function getMassFieldList($morph)
    {
        $model  = morph_to_model($morph);
        $except = array_diff(array_keys($model::fieldlist()), $model::massfieldlist());

        return array_except($model::fieldlist(), $except);
    }

    /**
     * Get all admin users list.
     *
     * @param array $select_item
     * @param array $not_in
     *
     * @return void
     */
    public static function getAdminUsersList($select_item = [], $not_in = [0])
    {
        return $select_item + \App\Models\Staff::orderBy('id')
                                               ->whereNotIn('id', $not_in)
                                               ->get()
                                               ->where('status', 1)
                                               ->pluck('name', 'id')
                                               ->toArray();
    }

    /**
     * Get at who long string data.
     *
     * @return string
     */
    public static function atWhoData()
    {
        return implode(',', \App\Models\Staff::orderBy('id')
                                             ->get(['id', 'first_name', 'last_name'])
                                             ->where('status', 1)
                                             ->pluck('name')
                                             ->toArray());
    }

    /**
     * Get roles dropdown list.
     *
     * @return array
     */
    public static function getRolesList()
    {
        return \App\Models\Role::onlyGeneral()
                               ->orderBy('id')
                               ->get()
                               ->pluck('display_name', 'id')
                               ->toArray();
    }


    /**
     * Get all timezone lists.
     *
     * @param array $select_item
     *
     * @return void
     */
    public static function getTimeZonesList($select_item = [])
    {
        return $select_item + time_zones_list();
    }

    /**
     * Get a module access dropdown list.
     *
     * @return array
     */
    public static function getAccessList()
    {
        return [
            'private'    => 'Private',
            'public'     => 'Public Read Only',
            'public_rwd' => 'Public Read/Write/Delete',
        ];
    }

    /**
     * Get a priority list of a module.
     *
     * @param array $select_item
     *
     * @return array
     */
    public static function getPriorityList($select_item = [])
    {
        return $select_item + [
            ''        => '-None-',
            'high'    => 'High',
            'highest' => 'Highest',
            'low'     => 'Low',
            'lowest'  => 'Lowest',
            'normal'  => 'Normal',
        ];
    }

    /**
     * The readable text format of the access field value.
     *
     * @param string $access
     *
     * @return string|null
     */
    public static function readableAccess($access)
    {
        if (array_key_exists($access, self::getAccessList())) {
            return self::getAccessList()[$access];
        }

        return null;
    }

    /**
     * Get a readable significant period list.
     *
     * @return array
     */
    public static function getTimePeriodList()
    {
        return [
            'any'            => 'Any time',
            'between'        => 'Is between',
            'yesterday'      => 'Yesterday',
            'today'          => 'Today',
            'tommorrow'      => 'Tommorrow',
            'last_month'     => 'Last Month',
            'current_month'  => 'Current Month',
            'next_month'     => 'Next Month',
            'last_7_days'    => 'Last 7 days',
            'last_30_days'   => 'Last 30 days',
            'last_60_days'   => 'Last 60 days',
            'last_90_days'   => 'Last 90 days',
            'last_120_days'  => 'Last 120 days',
            'last_6_months'  => 'Last 6 months',
            'last_12_months' => 'Last 12 months',
            'next_7_days'    => 'Next 7 days',
            'next_30_days'   => 'Next 30 days',
            'next_60_days'   => 'Next 60 days',
            'next_90_days'   => 'Next 90 days',
            'next_120_days'  => 'Next 120 days',
            'next_6_months'  => 'Next 6 months',
            'next_12_months' => 'Next 12 months',
        ];
    }
}
