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

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class FilterView extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filter_views';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module_name', 'view_name', 'filter_params', 'visible_type', 'visible_to', 'is_fixed', 'is_default',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['auth_can_view', 'shared_viewable', 'auth_can_edit', 'auth_can_delete'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected static $valid_module = [
        'dashboard', 'staff', 'role', 'project', 'task', 'issue', 'event', 'notification',
    ];

    /**
     * Get 'Filter Views' of a module.
     *
     * @param string $module
     *
     * @return array
     */
    public static function getFilterViews($module)
    {
        $outcome['system_default'] = self::where('module_name', $module)->where('is_fixed', 1)->get();
        $outcome['my_views']       = self::where('module_name', $module)
                                         ->where('is_fixed', 0)
                                         ->createdByUser(auth()->user()->id)
                                         ->select('filter_views.*')
                                         ->get();
        $outcome['shared_views']   = self::where('module_name', $module)
                                         ->where('is_fixed', 0)
                                         ->createdByUser(auth()->user()->id, false)
                                         ->where('visible_type', '!=', 'only_me')
                                         ->select('filter_views.*')
                                         ->get()->where('shared_viewable', true);

        return $outcome;
    }

    /**
     * Get the current 'Filter View' of a module.
     *
     * @param string $module
     *
     * @return \App\Models\FilterView
     */
    public static function getCurrentFilter($module)
    {
        $current_filter = auth_staff()->views()->where('module_name', $module)->get();

        // If the current "Filter View" exists and the auth user has permission then return the current "Filter View"
        // else return to default "Filter View".
        if ($current_filter->count()) {
            $current_filter = $current_filter->first();

            if ($current_filter->auth_can_view) {
                return $current_filter;
            }
        }

        return self::where('module_name', $module)->where('is_default', 1)->first();
    }

    /**
     * Get the breadcrumb of a module.
     *
     * @param string $module
     * @param string $parent
     *
     * @return string
     */
    public static function getBreadcrumb($module, $parent = null)
    {
        // Get module filters, current filter, custom temp filter CSS class, and "save as view" option.
        $filter_views   = self::getFilterViews($module);
        $current_filter = self::getCurrentFilter($module);
        $action_btns    = null;
        $prestar        = $current_filter->custom_view_name ? 'prestar' : '';
        $save_as_view   = $current_filter->custom_view_name
                          ? "<a class='bread-link save-as-view' data-item='$module'>Save as View</a>" : '';

        // If the current filter is not system-defined then it has action buttons.
        if (! $current_filter->is_fixed && empty($prestar)) {
            $action_btns = $current_filter->action_btns_html;
        }

        $parent      = is_null($parent) ? $module : $parent;
        $breadcrumb  = "<ol class='breadcrumb dropdown-view'>";
        $breadcrumb .= "<li><a href='" . route("admin.$parent.index") . "'>" . ucfirst($parent) . "s</a></li>";
        $breadcrumb .= "<li class='active $prestar'>" .
                            \Form::open(['route' => 'admin.view.dropdown', 'method' => 'post']) .
                                "<select name='view' class='form-control breadcrumb-select' data-module='{$module}'>
                                    <optgroup label='SYSTEM'>";

        // Render system defined filter views.
        foreach ($filter_views['system_default'] as $system_view) {
            $selected = $system_view->id == $current_filter->id ? 'selected' : '';
            $reverse_ajax_load = strpos_array(['Closed', 'Archived'], $system_view->view_name) ? 'reverse' : 'true';
            $breadcrumb .= "<option value='{$system_view->id}' data-load-kanban='{$reverse_ajax_load}' $selected>" .
                                $system_view->view_name .
                           '</option>';
        }

        $breadcrumb .= '</optgroup>';

        // Render the auth user-defined filter views.
        if ($filter_views['my_views']->count()) {
            $breadcrumb .= "<optgroup label='MY VIEWS'>";

            foreach ($filter_views['my_views'] as $my_view) {
                $selected    = ($my_view->id == $current_filter->id) ? 'selected' : '';
                $breadcrumb .= "<option value='{$my_view->id}' $selected>{$my_view->view_name}</option>";
            }

            $breadcrumb .= '</optgroup>';
        }

        // Render shared filter views.
        if ($filter_views['shared_views']->count()) {
            $breadcrumb .= "<optgroup label='SHARED VIEWS'>";

            foreach ($filter_views['shared_views'] as $shared_view) {
                $selected    = ($shared_view->id == $current_filter->id) ? 'selected' : '';
                $breadcrumb .= "<option value='{$shared_view->id}' $selected>{$shared_view->view_name}</option>";
            }

            $breadcrumb .= '</optgroup>';
        }

        $breadcrumb .= '</select>' . \Form::close() .
                       "<div class='inline-block view-btns'>" . $save_as_view . $action_btns . '</div></li></ol>';

        return $breadcrumb;
    }

    /**
     * Get field filter conditions list.
     *
     * @param bool $none
     *
     * @return array
     */
    public static function getFieldFilterConditionsList($none = true)
    {
        // Available condition for the string type field.
        $condition_list['string'] = [
            'equal'       => ['is equal to', 'string'],
            'not_equal'   => ['not equal to', 'string'],
            'contain'     => ['contains', 'string'],
            'not_contain' => ['does not contain', 'string'],
            'empty'       => 'is empty',
            'not_empty'   => 'is not empty',
        ];

        // Available condition for the dropdown type field.
        $condition_list['dropdown'] = [
            'equal'     => ['is equal to', 'dropdown'],
            'not_equal' => ['not equal to', 'dropdown'],
            'empty'     => 'is empty',
            'not_empty' => 'is not empty',
        ];

        // Available condition for the numeric type field.
        $condition_list['numeric'] = [
            'equal'     => ['= is equal to', 'numeric'],
            'not_equal' => ['!= not equal to', 'numeric'],
            'less'      => ['< is less than', 'numeric'],
            'greater'   => ['> is greater than', 'numeric'],
        ];

        // Available condition for the date type field.
        $condition_list['date'] = [
            'before'    => ['is before', 'days'],
            'after'     => ['is after', 'days'],
            'last'      => ['in the last', 'days'],
            'next'      => ['in the next', 'days'],
            'empty'     => 'is empty',
            'not_empty' => 'is not empty',
        ];

        // Add empty value 'None' option on the top.
        if ($none) {
            $condition_list['string']   = array_merge(['' => '-None-'], $condition_list['string']);
            $condition_list['dropdown'] = array_merge(['' => '-None-'], $condition_list['dropdown']);
            $condition_list['numeric']  = array_merge(['' => '-None-'], $condition_list['numeric']);
            $condition_list['date']     = array_merge(['' => '-None-'], $condition_list['date']);
        }

        return $condition_list;
    }

    /**
     * Generate rules for filtering data.
     *
     * @param array $data
     * @param array $fields
     *
     * @return array
     */
    public static function filterRulesGenerator($data, $fields)
    {
        $outcome           = [];
        $rules['string']   = 'required|in:equal,not_equal,contain,not_contain,empty,not_empty';
        $rules['date']     = 'required|in:before,after,last,next,empty,not_empty';
        $rules['dropdown'] = 'required|in:equal,not_equal,empty,not_empty';
        $rules['numeric']  = 'required|in:equal,not_equal,less,greater';

        foreach ($fields as $field) {
            // We consider the most common field type is String,
            // if not then the field will have an array of information
            // where we get field type, name, and condition.
            if (is_array($field)) {
                $type      = $field['type'];
                $name      = $field['name'];
                $condition = $field['condition'];
            } else {
                $type      = 'string';
                $name      = $field;
                $condition = 'required|array|max:200';
            }

            $condition_name = $name . '_condition';

            if (array_key_exists($condition_name, $data)) {
                $outcome[$condition_name] = $rules[$type];

                if ($type == 'numeric') {
                    $outcome[$name] = $condition;
                } else {
                    if ($data[$condition_name] != 'empty' && $data[$condition_name] != 'not_empty') {
                        $outcome[$name] = $condition;
                    }
                }
            }
        }

        return $outcome;
    }

    /**
     * Get field filter condition to render options list.
     *
     * @param bool $none
     *
     * @return array
     */
    public static function getFieldConditionOptionsList($none = true)
    {
        $options_list = [];
        $condition_list = self::getFieldFilterConditionsList($none);

        foreach ($condition_list as $type => $options) {
            $options_list[$type] = \HtmlElement::renderSelectOptions($options);
        }

        return $options_list;
    }

    /**
     * Get a valid module array list.
     *
     * @return array
     */
    public static function getValidModuleList()
    {
        return self::$valid_module;
    }

    /**
     * 'Filter View' validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function viewValidate($data)
    {
        $valid_modules = implode(',', self::getValidModuleList());
        $required = $data['visible_to'] == 'selected_users' ? 'required' : '';

        $rules = [
            'view_name'      => 'required|max:200',
            'module'         => "required|in:{$valid_modules}",
            'visible_to'     => 'required|in:only_me,everyone,selected_users',
            'selected_users' => "{$required}|array|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL",
        ];

        return validator($data, $rules);
    }

    /**
     * Module 'Filter View' form validation.
     *
     * @param array  $data
     * @param string $module_name
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function moduleFilterValidate($data, $module_name)
    {
        $rules = [
            'timeperiod'      => 'required|in:' . implode(',', array_keys(time_period_list())),
            'owner_condition' => 'required|in:all,equal,not_equal,empty,not_empty',
        ];

        // "Dashboard" and "Notification" modules have special filter view validation rules.
        if ($module_name == 'dashboard') {
            $rules['widget_prefix'] = 'max:50';
            $rules['auto_refresh']  = 'in:5,15,30,60';
        } elseif ($module_name == 'notification') {
            $rules['related_condition'] = 'in:project,task,milestone,issue,event';

            if (array_key_exists('related_condition', $data) && ! empty($data['related_condition'])) {
                $rules['related'] = "exists:{$data['related_condition']}s,id,deleted_at,NULL";
            }
        }

        // If posted data has period then add start and end date validation rules.
        if (array_key_exists('timeperiod', $data) && $data['timeperiod'] == 'between') {
            $rules['start_date'] = 'required|date';
            $rules['end_date']   = 'required|after:' . date('Y-m-d', strtotime($data['start_date'] . ' -1 day'));
        }

        // If posted data has owner condition then add owner validation rules.
        if (array_key_exists('owner_condition', $data) && in_array($data['owner_condition'], ['equal', 'not_equal'])) {
            $valid_admins   = implode(',', User::onlyStaff()->where('status', 1)->pluck('linked_id')->toArray());
            $valid_admins   = $module_name == 'notification' ? $valid_admins : '0,' . $valid_admins;
            $rules['owner'] = 'required|array|in:' . $valid_admins;
        }

        return validator($data, $rules);
    }

    /**
     * Get module formatted field params.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     *
     * @return array
     */
    public static function getModuleFormattedFieldParams($request, $module_name)
    {
        $owner_value = null;

        if (in_array($request->owner_condition, ['equal', 'not_equal'])) {
            $owner_value = (array) $request->owner;
        }

        if ($request->timeperiod == 'between') {
            $formatted_params['start_date'] = ['condition' => null, 'value' => $request->start_date];
            $formatted_params['end_date']   = ['condition' => null, 'value' => $request->end_date];
        }

        $formatted_params['timeperiod'] = ['condition' => null, 'value' => $request->timeperiod];
        $formatted_params['owner']      = ['condition' => $request->owner_condition, 'value' => $owner_value];

        if ($module_name == 'dashboard') {
            $formatted_params['widget_prefix'] = ['condition' => null, 'value' => $request->widget_prefix];
            $formatted_params['auto_refresh']  = ['condition' => null, 'value' => null_if_empty($request->auto_refresh)];
        }

        if ($module_name == 'notification') {
            $formatted_params['related'] = [
                'condition' => null_if_empty($request->related_condition),
                'value'     => null_if_empty($request->related),
            ];
        }

        return ['formatted_params' => $formatted_params, 'data' => $request->all()];
    }

    /**
     * Get commonly formatted field params.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     *
     * @return array
     */
    public static function getFormattedFieldParams($request, $module_name = null)
    {
        if (! is_null($module_name) && in_array($module_name, ['dashboard', 'notification'])) {
            $outcome = self::getModuleFormattedFieldParams($request, $module_name);

            return $outcome;
        }

        $data = [];
        $formatted_params = [];

        if (count($request->fields)) {
            foreach ($request->fields as $key => $field) {
                $condition        = $field . '_condition';
                $data[$condition] = array_key_exists($key, $request->conditions) ? $request->conditions[$key] : null;
                $data[$field]     = array_key_exists($key, $request->values) ? $request->values[$key] : null;
                $save_value       = $data[$field];

                if ($field == 'linked_type' && strpos($data[$field], '|') !== false) {
                    $linked_data         = explode('|', $data['linked_type']);
                    $data['linked_id']   = $linked_data[1];
                    $data['linked_type'] = $linked_data[0];
                    $save_value          = ['linked_type' => $linked_data[0], 'linked_id' => $linked_data[1]];
                }

                $formatted_params[$field] = ['condition' => $data[$condition], 'value' => $save_value];
            }
        }

        return ['formatted_params' => $formatted_params, 'data' => $data];
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the auth user permission status to view the specified resource.
     *
     * @return bool
     */
    public function getAuthCanViewAttribute()
    {
        // Viewable if "Filter View" is system-defined or, the auth user is the creator or, visible type to everyone
        // Not Viewable if the auth user is not the creator and visible type only for the creator.
        // or, visible type for selected users and the auth user is one of them.
        if ($this->is_fixed) {
            return true;
        } elseif ($this->auth_is_creator || $this->visible_type == 'everyone') {
            return true;
        } elseif (! $this->auth_is_creator && $this->visible_type == 'only_me') {
            return false;
        } elseif ($this->visible_type == 'selected_users' && in_array(auth_staff()->id, $this->allowed_users)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the auth user permission status to edit the specified resource.
     *
     * @return bool
     */
    public function getAuthCanEditAttribute()
    {
        // Not editable if "Filter View" is system-defined or the auth user is not the creator.
        if ($this->is_fixed || ! $this->auth_is_creator) {
            return false;
        }

        return true;
    }

    /**
     * Get the auth user permission status to delete the specified resource.
     *
     * @return bool
     */
    public function getAuthCanDeleteAttribute()
    {
        // Deletable if "Filter View" is not system-defined and the auth user is admin|creator.
        if (! $this->is_fixed && (auth_staff()->admin || $this->auth_is_creator)) {
            return true;
        }

        return false;
    }

    /**
     * Get the specified resource is shared viewable or not.
     *
     * @return bool
     */
    public function getSharedViewableAttribute()
    {
        // Shared viewable if the visible type is everyone or,
        // visible type for selected users and the auth user is one of them.
        if ($this->visible_type == 'everyone') {
            return true;
        } elseif ($this->visible_type == 'selected_users') {
            return in_array(auth()->user()->id, $this->allowed_users);
        }

        return false;
    }

    /**
     * Get allowed users of the specified resource.
     *
     * @return array
     */
    public function getAllowedUsersAttribute()
    {
        if ($this->visible_type == 'selected_users' && $this->visible_to != null) {
            return json_decode($this->visible_to, true);
        }

        return [];
    }

    /**
     * Get custom|modified view name status.
     *
     * @return bool
     */
    public function getCustomViewNameAttribute()
    {
        if (! is_null($this->filter_temp_params)) {
            return true;
        }

        return false;
    }

    /**
     * Get filter temp params.
     *
     * @return string
     */
    public function getFilterTempParamsAttribute()
    {
        $auth_view = $this->staffs()->where('staff_id', auth_staff()->id)->get();

        if ($auth_view->count()) {
            $filter_temp_params = $auth_view->first()->pivot->temp_params;

            if (! is_null($filter_temp_params)) {
                return $filter_temp_params;
            }
        }

        return null;
    }

    /**
     * Get the specified resource is the current filter status.
     *
     * @return bool
     */
    public function getIsCurrentAttribute()
    {
        $has_current = auth_staff()->views()->where('module_name', $this->module_name)->get();

        // The specified "Filter View" is current if this is associated with the auth user.
        if ($has_current->count()) {
            return ($has_current->first()->id == $this->id);
        }

        return ($this->is_default == 1);
    }

    /**
     * Get the specified resource param array.
     *
     * @return array
     */
    public function getParamArrayAttribute()
    {
        if (is_null($this->filter_temp_params)) {
            return ! is_null($this->filter_params) ? json_decode($this->filter_params, true) : [];
        }

        return json_decode($this->filter_temp_params, true);
    }

    /**
     * Get the specified resource params value array.
     *
     * @return array
     */
    public function getParamValArrayAttribute()
    {
        $outcome = [];

        if (count($this->param_array)) {
            foreach ($this->param_array as $key => $info) {
                $outcome[$key] = $info['value'];
                $outcome[$key . '_condition'] = $info['condition'];

                if ($key == 'linked_type' && is_array($info['value'])) {
                    $outcome['linked_type'] = $info['value']['linked_type'];
                    $outcome['linked_id']   = $info['value']['linked_id'];
                }
            }
        }

        return $outcome;
    }

    /**
     * Get counted the specified resource total params.
     *
     * @return int
     */
    public function getParamCountAttribute()
    {
        return count($this->param_array);
    }

    /**
     * Get parameter conditions.
     *
     * @param string $param
     *
     * @return string
     */
    public function getParamCondition($param)
    {
        if (array_key_exists($param, $this->param_array)) {
            return $this->param_array[$param]['condition'];
        }

        return null;
    }

    /**
     * Get parameter condition value.
     *
     * @param string $param
     *
     * @return mixed
     */
    public function getParamVal($param)
    {
        if (array_key_exists($param, $this->param_array)) {
            return $this->param_array[$param]['value'];
        }

        return null;
    }

    /**
     * Get optional params.
     *
     * @return array
     */
    public function getOptionalParamAttribute()
    {
        $optional   = [];
        $start_date = $this->getParamVal('start_date');
        $end_field  = array_key_exists('end_date', $this->param_array) ? 'end_date' : 'due_date';
        $end_date   = $this->getParamVal($end_field);
        $timeperiod = $this->getParamVal('timeperiod');

        if (not_null_empty($timeperiod)) {
            $optional['timeperiod_display'] = timeperiod_display($timeperiod, $start_date, $end_date);
        } else {
            if (not_null_empty($start_date) && not_null_empty($end_date)) {
                $optional['timeperiod_display'] = timeperiod_display('between', $start_date, $end_date);
            }
        }

        return $optional;
    }

    /**
     * Get "Filter View" option HTML.
     *
     * @return string
     */
    public function getOptionHtmlAttribute()
    {
        return "<option value='{$this->id}'>{$this->view_name}</option>";
    }

    /**
     * Get the specified resource actions HTML.
     *
     * @return string
     */
    public function getActionBtnsHtmlAttribute()
    {
        $action_btns = '';

        // If the auth user can edit the "Filter View".
        if ($this->auth_can_edit) {
            $action_btns .= "<a class='breadcrumb-action first common-edit-btn' data-toggle='tooltip'
                                data-placement='bottom' title='Edit' data-item='view' editid='{$this->id}'
                                data-url='" .  route('admin.view.edit', $this->id) . "' modal-small='true'
                                data-posturl='" . route('admin.view.update', $this->id) . "' modal-delete='false'>
                                <i class='pe-va fa fa-pencil'></i>
                            </a>";
        }

        // If the auth user can delete the "Filter View".
        if ($this->auth_can_delete) {
            $action_btns .= \Form::open([
                'route'  => ['admin.view.destroy', $this->id],
                'method' => 'delete',
                'class'  => 'inline-block',
            ]) .
                \Form::hidden('id', $this->id) .
                "<a class='breadcrumb-action delete last' data-toggle='tooltip' data-placement='bottom'
                    title='Delete' data-item='view' data-associated='false'
                    modal-sub-title='{$this->view_name}'><i class='pe-va mdi mdi-delete'></i>
                </a>" .
            \Form::close();
        }

        return $action_btns;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A many-to-many relationship with Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staffs()
    {
        return $this->belongsToMany(Staff::class, 'staff_view')->withPivot('temp_params');
    }
}
