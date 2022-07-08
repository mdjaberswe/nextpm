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

class HtmlElement
{
    /**
     * Render breadcrumbs HTML.
     *
     * @param string $str_breadcrumb
     *
     * @return string
     */
    public static function renderBreadcrumb($str_breadcrumb)
    {
        // Breakdown formatted breadcrumb string to an array.
        $breadcrumb_array = explode('|', $str_breadcrumb);
        $items_bread      = count($breadcrumb_array);
        $current_bread    = 0;
        $render           = "<ol class='breadcrumb'>";

        foreach ($breadcrumb_array as $breadcrumb) {
            $pos          = strpos($breadcrumb, ':');
            $route_params = [];

            // If the condition is satisfied then the breadcrumb element has a link else simple text.
            if ($pos !== false) {
                $text            = substr($breadcrumb, $pos + 1);
                $route           = substr($breadcrumb, 0, $pos);
                $route_has_param = strpos($route, ',');

                if ($route_has_param !== false) {
                    $route_params[] = substr($route, $route_has_param + 1);
                    $route          = substr($route, 0, $route_has_param);
                }
            } else {
                $text = $breadcrumb;
            }

            if (++$current_bread == $items_bread) {
                $render .= "<li class='active'>{$text}</li>";
            } else {
                $render .= '<li>' . link_to_route($route, $text, $route_params) . '</li>';
            }
        }

        $render .= '</ol>';

        return $render;
    }

    /**
     * Get a permissions summary.
     *
     * @param string $string
     *
     * @return string
     */
    public static function permissionSummary($string)
    {
        $summary = snake_to_space($string);

        return ucwords($summary);
    }

    /**
     * Permission checked if the auth user has permission.
     *
     * @param bool $has_permission
     *
     * @return string
     */
    public static function permissionChecked($has_permission)
    {
        return $has_permission == true ? 'checked' : '';
    }

    /**
     * Render module permission HTML tags.
     *
     * @param array $module_permissions
     * @param bool  $is_disabled
     *
     * @return string
     */
    public static function renderModulePermissions($module_permissions, $is_disabled = false)
    {
        $outcome              = '';
        $permission_summary   = "<p class='para-soft comma'>";
        $permission_details   = "<div class='permission-details'>";
        $single_permissions   = '';
        $permission_block_has = false;
        $container_show       = false;
        $disabled             = '';

        if ($is_disabled == true) {
            $disabled = ' disabled';
        }

        // Module permission loop through $key as permission summary and $value as permission data.
        foreach ($module_permissions as $key => $value) {
            $status                  = '';
            $this_disabled           = '';
            $disabled_summary        = "enabled='true'";
            $show_permission_summary = 0;
            $display_none            = '';

            // If $value is a single element array then It is the parent module
            // else $value has multiple array elements then it is a sub-module.
            if (count($value) == 1) {
                $this_disabled            = $value[0]['type'] == 'preserve' && empty($disabled) ? ' disabled' : $disabled;
                $disabled_summary         = $value[0]['type'] == 'preserve' ? "disabled='true'" : $disabled_summary;
                $single_permissions      .= "<p class='para-checkbox pretty info smooth'>
                                                <input type='checkbox' name='permissions[]'
                                                parent='" . self::permissionSummary($key) . "'
                                                value='{$value[0]['id']}' " .
                                                self::permissionChecked($value[0]['has_permission']) . $this_disabled . ">
                                                <label><i class='mdi mdi-check'></i></label>
                                                <span>{$value[0]['display_name']}</span>
                                            </p>";
                $status                  .= $value[0]['id'];
                $show_permission_summary += $value[0]['has_permission'];
            } else {
                $permission_block_has  = true;
                $permission_details   .= "<div class='permission-details-box'>";
                $permission_details   .= '<h3>' . snake_to_space($key) . '</h3>';

                foreach ($value as $permission) {
                    $this_disabled            = $permission['type'] == 'preserve'  && empty($disabled) ? ' disabled' : $disabled;
                    $permission_details      .= "<p class='para-checkbox pretty info smooth'>
                                                    <input type='checkbox' name='permissions[]'
                                                    parent='" . self::permissionSummary($key) . "'
                                                    value='" . $permission['id'] . "' " .
                                                    self::permissionChecked($permission['has_permission']) . $this_disabled . ">
                                                    <label><i class='mdi mdi-check'></i></label>
                                                    <span>" . $permission['display_name'] . '</span>
                                                </p>';
                    $status                  .= $permission['id'];
                    $show_permission_summary += $permission['has_permission'];
                }

                $permission_details .= '</div>';
            }

            if ($show_permission_summary == 0) {
                $display_none = "style='display: none'";
            } else {
                $container_show = true;
            }

            $permission_summary .= "<span id='" . self::permissionSummary($key) . '-' . $status . "' status='{$status}'
                                        name='" . self::permissionSummary($key) . "' " . $display_none . " $disabled_summary>" .
                                        self::permissionSummary($key) .
                                   '</span>';
        }

        if ($single_permissions != '') {
            if ($permission_block_has == true) {
                $permission_details .= "<div class='permission-details-single-box'>";
                $permission_details .= '<h3>Others</h3>';
                $permission_details .= $single_permissions;
                $permission_details .= '</div>';
            } else {
                $permission_details .= "<div class='permission-details-single-box mb0-imp'>";
                $permission_details .= $single_permissions;
                $permission_details .= '</div>';
            }
        }

        if ($permission_block_has == true) {
            $taglen = strlen("<div class='permission-details'>");
            $permission_details = substr_replace($permission_details, "<div class='permission-details double'>", 0, $taglen);
        }

        if ($container_show == true) {
            $start_container = "<div class='col-xs-12 col-sm-6 col-md-8 col-lg-8 permission-summary block'>";
        } else {
            $start_container = "<div class='col-xs-12 col-sm-6 col-md-8 col-lg-8 permission-summary'>";
        }

        $end_container       = '</div>';
        $permission_summary .= '</p>';
        $permission_details .= '</div>';
        $outcome            .= $permission_summary;
        $outcome            .= "<p class='para-soft'><span class='pe-7s-angle-down pe-2x pe-va'></span></p>";
        $outcome            .= "<div class='line'></div>";
        $outcome            .= $permission_details;
        $outcome             = $start_container . $outcome . $end_container;

        return $outcome;
    }

    /**
     * Generate completion percentage progress bar.
     *
     * @param int    $completion
     * @param string $item
     * @param int    $total_count
     * @param int    $left_count
     * @param int    $right_count
     * @param string $left_status
     * @param string $right_status
     * @param bool   $show_stat
     *
     * @return string
     */
    public static function renderProgressBar(
        $completion,
        $item,
        $total_count,
        $left_count,
        $right_count,
        $left_status = 'Closed',
        $right_status = 'Open',
        $show_stat = true
    ) {
        $stat            = '';
        $css_class       = 'has-num';
        $statement       = $completion . '%';
        $statement_title = fill_up_space($statement . ' (' . $left_count . ' of ' . $total_count . ' ' . $item . 's Completed)');

        // If the progress bar needs to show numeric statistics then render the following HTML.
        if ($show_stat) {
            $stat = "<span class='num left' data-toggle='tooltip'
                        title='" . fill_up_space($left_count . ' ' . $left_status . ' ' . str_plural($item, $left_count)) . "'>" .
                        $left_count .
                    "</span>
                    <span class='num right' data-toggle='tooltip'
                        title='" . fill_up_space($right_count . ' ' . $right_status . ' ' . str_plural($item, $right_count)) . "'>" .
                        $right_count .
                    "</span>";
        }

        // If the progress bar doesn't have single data.
        if ($completion == -1) {
            $completion      = 0;
            $statement       = "No {$item}s";
            $statement_title = fill_up_space("0 $right_status $item");
            $stat            = '';
        }

        $html = "<a class='completion-bar'>
                    <div class='progress $css_class'>
                        <div class='progress-bar color-success' role='progressbar' aria-valuenow='{$completion}'
                             aria-valuemin='0' aria-valuemax='100' style='width: {$completion}%'>
                            <span class='sr-only'>{$completion}% Complete</span>
                        </div>
                        <span class='shadow' data-toggle='tooltip' title='{$statement_title}'>"
                            . $statement .
                        '</span>' . $stat . '
                    </div>
                </a>';

        return $html;
    }

    /**
     * Render options HTML tag those contain numeric value only.
     *
     * @param int      $min
     * @param int      $max
     * @param int      $step
     * @param int|null $default
     *
     * @return string
     */
    public static function renderNumericOptions($min = 0, $max = 100, $step = 10, $default = null)
    {
        $options = '';

        // Don't stop looping till it is less than max,
        // add new option tag and every end of loop add given $step value.
        while ($min <= $max) {
            $selected  = (! is_null($default) && $min == $default) ? 'selected' : '';
            $options  .= "<option value='" . $min . "' $selected>" . $min . "</option>";
            $min       = $min + $step;
        }

        return $options;
    }

    /**
     * Render tab nav HTML.
     *
     * @param array $tabs
     *
     * @return string
     */
    public static function renderTabNav($tabs)
    {
        $menu_css      = count($tabs['list']) > 15 ? 'high-density' : '';
        $html          = "<ul id='item-tab' class='menu-h $menu_css'>";
        $dropdown      = "<ul class='dropdown-menu up-caret'>";
        $total_li      = count($tabs['list']);
        $plain_values  = array_flatten($tabs['list']);
        $repeat_values = array_count_values($plain_values);

        if (array_key_exists(0, $repeat_values)) {
            $total_li = $total_li - $repeat_values[0];
        }

        if (is_array($tabs['list'][$tabs['default']])) {
            $active_key = $tabs['list'][$tabs['default']]['parent'];
        } else {
            $active_key = $tabs['default'];
        }

        // Add responsive class according to the total nav list count.
        if ($total_li > 9 && $total_li <= 13) {
            $dropdown_class = 'hide-lim-md';
        } elseif ($total_li > 7 && $total_li <= 9) {
            $dropdown_class = 'hide-lim-sm';
        } elseif ($total_li > 5 && $total_li <= 7) {
            $dropdown_class = 'hide-lim-xs';
        } elseif ($total_li > 3 && $total_li <= 5) {
            $dropdown_class = 'hide-lim-xxs';
        } elseif ($total_li >= 1 && $total_li <= 3) {
            $dropdown_class = 'none';
        } else {
            $dropdown_class = 'block';
        }

        $i = 1;

        // Loop through tab list and list element show hide depend on its order.
        foreach ($tabs['list'] as $key => $value) {
            $class = ($key == $active_key) ? 'active' : null;

            if ($i > 9 && $i <= 13) {
                $li_class          = 'display-lim-lg';
                $dropdown_li_class = 'hide-lim-md';
            } elseif ($i > 7 && $i <= 9) {
                $li_class          = 'display-lim-md';
                $dropdown_li_class = 'hide-lim-sm';
            } elseif ($i > 5 && $i <= 7) {
                $li_class          = 'display-lim-sm';
                $dropdown_li_class = 'hide-lim-xs';
            } elseif ($i > 3 && $i <= 5) {
                $li_class          = 'display-lim-xs';
                $dropdown_li_class = 'hide-lim-xxs';
            } elseif ($i >= 1 && $i <= 3) {
                $li_class          = 'display-lim-xxs';
                $dropdown_li_class = 'none';
            } else {
                $li_class          = 'none';
                $dropdown_li_class = 'block';
            }

            if (is_array($value) && $value['nav'] == 0) {
                continue;
            }

            $html     .= "<li class='$li_class'><a class='$class' tabkey='$key'>$value</a></li>";
            $dropdown .= "<li class='$dropdown_li_class'><a class='tab-link $class' tabkey='$key'>$value</a></li>";

            $i++;
        }

        $dropdown .= "</ul>";
        $html     .= "<li class='dropdown clean $dropdown_class'>
                        <a class='not-load dropdown-toggle' animation='fadeIn|fadeOut'
                            data-toggle='dropdown'aria-expanded='false'>
                            <i class='mdi mdi-dots-horizontal fa-md pe-va'></i>
                        </a>
                        $dropdown
                     </li>";
        $html     .= "</ul>";

        return $html;
    }

    /**
     * Render option HTML tag with attributes.
     *
     * @param array      $opt_array
     * @param int|string $default
     *
     * @return string
     */
    public static function renderSelectOptions($opt_array, $default = null)
    {
        $html = '';

        // Options array render HTML where $key consider as option value and
        // $display consider as options display text also add 'for' attribute if value is an array.
        foreach ($opt_array as $key => $display) {
            $selected = (! is_null($default) && $key == $default) ? 'selected' : '';

            if (is_array($display)) {
                $html .= "<option value='{$key}' for='{$display[1]}' $selected>{$display[0]}</option>";
            } else {
                $html .= "<option value='{$key}' $selected>$display</option>";
            }
        }

        return $html;
    }
}
