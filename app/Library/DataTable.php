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

use Yajra\Datatables\Datatables as BaseDataTable;

class DataTable extends BaseDataTable
{
    /**
     * Get data table proper columns JSON format.
     *
     * @param array $columns
     * @param array $hide_columns
     * @param array $default_hide_columns
     *
     * @return string
     */
    public static function jsonColumn($columns, $hide_columns = [], $default_hide_columns = [])
    {
        $json_columns = '';

        foreach ($columns as $index => $column) {
            // If the column is an array and index not in hide columns
            // Or, the column is not an array and column not in hide columns.
            if (is_array($column)
                && ! in_array($index, $hide_columns)
                || ! is_array($column)
                && ! in_array($column, $hide_columns)
            ) {
                if (is_array($column)) {
                    $column_parameter = ['data' => $index, 'visible' => ! in_array($index, $default_hide_columns)];

                    foreach ($column as $addition_parameter => $value) {
                        $column_parameter[$addition_parameter] = $value;
                    }
                } else {
                    $column_parameter = ['data' => $column, 'visible' => ! in_array($column, $default_hide_columns)];
                }

                $json_columns .= json_encode($column_parameter) . ',';
            }
        }

        return $json_columns;
    }

    /**
     * Datatable show and hide columns in proper JSON format.
     *
     * @param array $table
     *
     * @return string
     */
    public static function showhideColumn($table)
    {
        $json_columns = '';

        // By default, the checkbox in show hide columns, but
        // if the table has a checkbox key then the checkbox key value should be true.
        if (! isset($table['checkbox']) || (isset($table['checkbox']) && $table['checkbox'] == true)) {
            $json_columns .= json_encode(['text' => 'CHECKBOX', 'className' => 'show-hide']) . ',';
        }

        // Take all table head in show hide columns.
        foreach ($table['thead'] as $thead) {
            if (is_array($thead)) {
                $json_columns .= json_encode(['text' => $thead[0], 'className' => 'show-hide']) . ',';
            } else {
                $json_columns .= json_encode(['text' => $thead, 'className' => 'show-hide']) . ',';
            }
        }

        // By Default, action in show hide columns, but
        // if the table has an action key then the action key value should be true.
        if (! isset($table['action']) || (isset($table['action']) && $table['action'] == true)) {
            $json_columns .= json_encode(['text' => 'ACTION', 'className' => 'show-hide']);
        }

        return $json_columns;
    }

    /**
     * Render data table custom filter HTML.
     *
     * @param array  $filter_input
     * @param string $item
     * @param bool   $white
     *
     * @return string
     */
    public static function filterHtml($filter_input, $item, $white = false)
    {
        $filter_html = '';

        // Add all filter input array in filter HTML.
        foreach ($filter_input as $input_name => $input) {
            if ($input['type'] == 'dropdown') {
                $class       = isset($input['no_search']) ? 'select-type-single-b' : 'select-type-single';
                $class       = $white ? 'white-' . $class : $class;
                $filter_html .= "<select name='" . $input_name . "' id='" . strtolower($item) . '-'
                                                 . $input_name . "' class='" . $class . "'>";

                foreach ($input['options'] as $key => $display) {
                    $filter_html .= "<option value='" . $key . "'>" . $display . "</option>";
                }

                $filter_html .= "</select>";
            }
        }

        return $filter_html;
    }
}
