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

namespace App\Http\Controllers\Admin;

use App\Models\AttachFile;
use App\Models\FilterView;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Controllers\HomeController;

class AdminBaseController extends HomeController
{
    protected $directory;
    protected $location;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth.type:staff');
        $this->setUploadDirectoryLocation();
    }

    /**
     * Set up upload directory location.
     *
     * @param string|null $type
     *
     * @return void
     */
    public function setUploadDirectoryLocation($type = null)
    {
        $this->directory = AttachFile::directoryRule($type);
        $this->location  = str_replace('.', '/', $this->directory['location']) . '/';
    }

    /**
     * Get positionable module resources dropdown list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dropdownList(Request $request)
    {
        $status = false;
        $error  = null;
        $items  = [];

        // If the request has 'source' then get a dropdown array list of the source.
        if (isset($request->source)) {
            $status   = true;
            $table    = $request->source;
            $order_by = isset($request->orderby) ? $request->orderby : 'id';
            $items    = \DB::table($table)->whereNull('deleted_at')->orderBy($order_by)->get(['id', 'name']);
        }

        return response()->json(['status' => $status, 'items' => $items, 'error' => $error]);
    }

    /**
     * Get a child append list on parent change.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $parent
     * @param string                   $child
     *
     * @return \Illuminate\Http\Response
     */
    public function dropdownAppendList(Request $request, $parent, $child)
    {
        // Get child array list by append dropdown method or where parent id field is equal to requested id.
        $status = false;
        $error  = null;
        $field  = $request->field;
        $id     = $request->id;
        $model  = morph_to_model($child);
        $childs = method_exists($model, 'getAppendDropdownList')
                  ? $model::getAppendDropdownList($field, $id)
                  : $model::where($field, $id)->get()->pluck('name', 'id');

        if (isset($childs)) {
            $status = true;
        } else {
            $error = 'Record not found.';
        }

        return response()->json(['status' => $status, 'selectOptions' => $childs, 'error' => $error]);
    }

    /**
     * Reorder resources position by up-down drag & drop row of the resource table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dropdownReorder(Request $request)
    {
        $status = false;
        $error  = null;
        $position_number = implode('', $request->positions);

        // If the request has "source", countable positions array, and a position number.
        if (isset($request->source)
            && isset($request->positions)
            && count($request->positions)
            && is_numeric($position_number)
        ) {
            $table = $request->source;
            $ids_array = \DB::table($table)->whereNull('deleted_at');

            if ($request->has('condition') && not_null_empty($request->condition)) {
                $conditions = explode('|', $request->condition);

                foreach ($conditions as $condition) {
                    $condition = explode(':', $condition);
                    $field     = $condition[0];
                    $value     = strpos($condition[0], 'id') !== false ? (int) $condition[1] : $condition[1];
                    $ids_array = $ids_array->where($field, $value);
                }
            }

            $ids_array = $ids_array->pluck('id');
            $ids_array = array_map('strval', $ids_array);
            $positions = $request->positions;

            sort($ids_array);
            sort($positions);

            // If resources database ids are equal to requested ids position.
            if ($ids_array == $positions) {
                $position = 1;

                foreach ($request->positions as $position_id) {
                    \DB::table($table)->where('id', $position_id)->update(['position' => $position]);
                    $position++;
                }

                $status = true;
            }
        }

        return response()->json(['status' => $status, 'error' => $error]);
    }

    /**
     * Reorder resources kanban card position.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function kanbanReorder(Request $request)
    {
        $status        = false;
        $errors        = [];
        $realtime      = [];
        $kanban_count  = [];
        $kanban_morphs = ['project', 'task', 'issue'];
        $parent        = null;
        $data          = $request->all();

        // If the request has source value and it is a valid kanban source.
        if (isset($request->source) && in_array($request->source, $kanban_morphs)) {
            $model = morph_to_model($request->source);
            $validation = $model::kanbanValidate($data);

            // If posted data passes kanban validation.
            if ($validation->passes()) {
                $kanban_item = $model::find($request->id);
                $field       = $request->field;
                $position    = $model::getKanbanDescPosition($request->picked);

                // If the kanban source is "Task" and the kanban card change one stage to another stage then
                // kanban card "Task" completion percentage will be according to the new stage completion percentage.
                if ($request->source == 'task' && $kanban_item->task_status_id != $request->stage) {
                    $new_status = \App\Models\TaskStatus::find($request->stage);
                    $kanban_item->completion_percentage = $new_status->completion_percentage;
                }

                $kanban_item->position = $position;
                $kanban_item->$field   = $request->stage;
                $kanban_item->update();

                if (not_null_empty($request->parent) && not_null_empty($request->parentid)) {
                    $parent = morph_to_model($request->parent)::find($request->parentid);
                }

                $status = true;
                $kanban_count = $model::getKanbanStageCount($parent);
            } else {
                $messages = $validation->getMessageBag()->toArray();
                foreach ($messages as $msg) {
                    $errors[] = $msg;
                }
            }
        }

        return response()->json([
            'status'       => $status,
            'errors'       => $errors,
            'realtime'     => $realtime,
            'kanbanCount'  => $kanban_count,
        ]);
    }

    /**
     * Secure image path.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $img
     *
     * @return \Illuminate\Http\Response
     */
    public function image(Request $request, $img)
    {
        try {
            $decrypt_img_path = decrypt($img);
            $storage_img_path = storage_path($decrypt_img_path);
            $image            = \Image::make($storage_img_path);

            return $image->response();
        } catch (DecryptException $e) {
            $image = \Image::make(public_path('img/placeholder.png'));

            return $image->response();
        }
    }

    /**
     * Render HTML content of a view request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function viewContent(Request $request)
    {
        $status = true;
        $html   = null;
        $info   = [];
        $view   = view_exists($request->viewContent, 'admin');

        // If the request has view content and view exists.
        if (isset($request->viewContent) && $view['status']) {
            $html = view($view['content'], ['form' => $request->viewType])->render();

            if (isset($request->default) && $request->default != '') {
                $default_data = explode('|', $request->default);

                foreach ($default_data as $single_data) {
                    $field_val    = explode(':', $single_data);
                    $field        = $field_val[0];
                    $value        = $field_val[1];
                    $info[$field] = $value;
                }
            }

            $info['show'] = [];
            $info['hide'] = [];

            // Show fields of a modal form.
            if (isset($request->showField) && $request->showField != '') {
                $show_field = explode('|', $request->showField);

                foreach ($show_field as $single_show) {
                    $info['show'][] = $single_show;
                }
            }

            // Hidden fields of a modal form.
            if (isset($request->hideField) && $request->hideField != '') {
                $hide_field = explode('|', $request->hideField);

                foreach ($hide_field as $single_hide) {
                    $info['hide'][] = $single_hide;
                }
            }
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'html' => $html, 'info' => $info]);
    }

    /**
     * Render tab HTML content.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     * @param int                      $module_id
     * @param string                   $tab
     *
     * @return \Illuminate\Http\Response
     */
    public function tabContent(Request $request, $module_name, $module_id, $tab)
    {
        $content     = null;
        $module_view = $module_name;
        $module_name = ($module_name == 'user') ? 'staff' : $module_name;
        $module      = morph_to_model($module_name)::withTrashed()->find($module_id);

        // If the module exists and the tab is valid then get tab view content.
        if (isset($module)
            && isset($module->id)
            && isset($request->type)
            && $tab == $request->type
            && $module->id == $request->id
            && array_key_exists($tab, $module::informationTypes($module))
        ) {
            $content = view('admin.' . $module_view . '.partials.tabs.tab-' . $tab, [$module_name => $module]);
        }

        return $content;
    }

    /**
     * Show page overview tab details information show|hide request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     *
     * @return \Illuminate\Http\Response
     */
    public function viewToggle(Request $request, $module_name)
    {
        if (isset($request->hide_details)) {
            session([$module_name . '_hide_details' => $request->hide_details]);

            return response()->json(['status' => session($module_name . '_hide_details')]);
        }
    }

    /**
     * Render filter form HTML content according to the module.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     *
     * @return \Illuminate\Http\Response
     */
    public function filterFormContent(Request $request, $module_name)
    {
        $status = true;
        $info   = null;
        $html   = null;

        // If the module name is valid.
        if (in_array($module_name, FilterView::getValidModuleList())) {
            $model              = morph_to_model($module_name);
            $filter_fields_list = [];
            $dropdown           = [];

            // Get all fields dropdown list and fields value dropdown list.
            if (class_exists($model)) {
                $filter_fields_list = $model::filterFieldDropDown();
                $dropdown           = method_exists($model, 'getFieldValueDropdownList')
                                      ? $model::getFieldValueDropdownList() : [];
            }

            $current_filter = FilterView::getCurrentFilter($module_name);
            $view = 'admin.' . $module_name . '.partials.filter-form';

            if (! view()->exists($view)) {
                $view = 'admin.' . $model::getRoute() . '.partials.filter-form';
            }

            $info = $current_filter->param_val_array;
            $html = view($view, [
                'filter_fields_list' => $filter_fields_list,
                'options_list'       => FilterView::getFieldConditionOptionsList(false),
                'dropdown'           => $dropdown,
                'current_filter'     => $current_filter,
            ])->render();
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
    }

    /**
     * Post module filter parameter for filtering data.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     *
     * @return \Illuminate\Http\Response
     */
    public function filterFormPost(Request $request, $module_name)
    {
        $status       = true;
        $errors       = null;
        $filter_count = null;
        $view_name    = null;
        $validation   = false;
        $realtime     = [];
        $kanban_count = [];

        // If the module name is valid and checks the proper sequence of fields, conditions, and values.
        if (in_array($module_name, FilterView::getValidModuleList())) {
            $model = morph_to_model($module_name);

            if (class_exists($model)) {
                $status = (! is_null($request->fields) &&
                           ! is_null($request->values) &&
                           ! is_null($request->conditions) &&
                           is_array($request->fields) &&
                           is_array($request->values) &&
                           is_array($request->conditions) &&
                           count($request->fields) == count($request->conditions));

                if ($status) {
                    $valid_fields = array_intersect($request->fields, $model::filterFieldList());
                    $status = count($valid_fields) == count($request->fields);

                    if (! $status) {
                        $errors = ['filter_fields' => 'Something went wrong! Please try again.'];
                    }
                } else {
                    $errors = ['filter_fields' => 'At least one field is required.'];
                }
            }

            if ($status) {
                $formatted_data = FilterView::getFormattedFieldParams($request, $module_name);
                $save_db_format = $formatted_data['formatted_params'];
                $data           = $formatted_data['data'];
                $filter_count   = count($save_db_format);
                $validation     = class_exists($model)
                                  ? $model::filterValidate($data)
                                  : FilterView::moduleFilterValidate($data, $module_name);

                // If filter validation passes and not save as new view just update current filter params.
                if ($validation->passes()) {
                    if (! isset($request->validationOnly)) {
                        $current_filter       = FilterView::getCurrentFilter($module_name);
                        $current_filter_param = $current_filter->param_array;
                        $view_name            = $current_filter->custom_view_name;

                        ksort($current_filter_param);
                        ksort($save_db_format);

                        if ($current_filter_param != $save_db_format) {
                            $auth_view   = $current_filter->staffs()->where('staff_id', auth_staff()->id);
                            $temp_params = ['temp_params' => json_encode($save_db_format)];
                            $view_name   = true;

                            if ($auth_view->get()->count()) {
                                \DB::table('staff_view')
                                    ->where('filter_view_id', $current_filter->id)
                                    ->where('staff_id', auth_staff()->id)
                                    ->update($temp_params);
                            } else {
                                $current_filter->staffs()->attach([auth_staff()->id], $temp_params);
                            }

                            if (method_exists($model, 'getKanbanStageCount')) {
                                $kanban_count = $model::getKanbanStageCount();
                            }

                            if (not_null_empty($request->timeperiod)) {
                                $realtime[] = ['timeperiod', $current_filter->optional_param['timeperiod_display']];
                            }
                        }
                    }
                } else {
                    $status = false;
                    $errors = $validation->getMessageBag()->toArray();
                }
            }
        } else {
            $status = false;
            $errors = ['filter_fields' => 'Invalid module.'];
        }

        return response()->json([
            'status'         => $status,
            'errors'         => $errors,
            'realtime'       => $realtime,
            'customViewName' => $view_name,
            'module'         => $module_name,
            'filterCount'    => $filter_count,
            'kanbanCount'    => $kanban_count,
        ]);
    }

    /**
     * Store a newly created 'Filter View' for reuses.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     *
     * @return \Illuminate\Http\Response
     */
    public function viewStore(Request $request, $module_name)
    {
        $status       = true;
        $errors       = null;
        $view_html    = null;
        $action_html  = null;
        $filter_count = null;
        $kanban_count = [];

        // If the module name is valid.
        if (in_array($module_name, FilterView::getValidModuleList())) {
            $data       = $request->all();
            $model      = morph_to_model($module_name);
            $validation = FilterView::viewValidate($data);

            // If filter view validation passes and saves posted data.
            if ($validation->passes()) {
                $filter_view               = new FilterView;
                $filter_view->view_name    = $request->view_name;
                $filter_view->module_name  = $request->module;
                $filter_view->visible_type = $request->visible_to;
                $filter_view->visible_to   = $request->visible_to == 'selected_users' && count($request->selected_users)
                                             ? json_encode($request->selected_users) : null;

                // If the request has filter data then get parameter from posted data
                // else get parameter from the current filter.
                if (isset($request->has_filter_data)) {
                    $field_info     = FilterView::getFormattedFieldParams($request, $module_name);
                    $save_db_format = $field_info['formatted_params'];
                    $filter_count   = count($save_db_format);
                    ksort($save_db_format);
                    $filter_view->filter_params = json_encode($save_db_format);
                } else {
                    $current_filter = FilterView::getCurrentFilter($request->module);
                    $filter_params  = is_null($current_filter->filter_temp_params)
                                      ? $current_filter->filter_params
                                      : $current_filter->filter_temp_params;
                    $filter_count   = count_if_countable(json_decode($filter_params, true));
                    $filter_view->filter_params = $filter_params;
                }

                $filter_view->save();

                // The auth user is associated only with the newly created module filter view
                // and detach the rest of all.
                $detach_views = auth_staff()->views()->where('module_name', $request->module)
                                                     ->pluck('filter_views.id')
                                                     ->toArray();

                if (count($detach_views)) {
                    auth_staff()->views()->detach($detach_views);
                }

                auth_staff()->views()->attach($filter_view->id);

                if (method_exists($model, 'getKanbanStageCount')) {
                    $kanban_count = $model::getKanbanStageCount();
                }

                $view_html   = $filter_view->option_html;
                $action_html = $filter_view->action_btns_html;
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        } else {
            $status = false;
        }

        return response()->json([
            'status'         => $status,
            'errors'         => $errors,
            'viewHtml'       => $view_html,
            'viewActionHtml' => $action_html,
            'module'         => $module_name,
            'filterCount'    => $filter_count,
            'kanbanCount'    => $kanban_count,
        ]);
    }

    /**
     * Show the form to edit the specified "Filter View".
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FilterView   $view
     *
     * @return \Illuminate\Http\Response
     */
    public function viewEdit(Request $request, FilterView $view)
    {
        $status = true;
        $info   = null;

        // If the specified "Filter View" is valid and view id is equal to posted id.
        if (isset($view) && isset($request->id) && $view->id == $request->id) {
            $info               = $view->toArray();
            $info['visible_to'] = $view->visible_type;
            $info['module']     = $view->module_name;
            $info['show']       = [];

            if (! is_null($view->visible_to)) {
                $info['show'][] = 'selected_users[]';
                $info['selected_users[]'] = json_decode($view->visible_to, true);
            }

            $info = (object) $info;
            $html = view('partials.modals.common-view-form', ['form' => 'edit'])->render();
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
    }

    /**
     * Update the specified "Filter View" in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FilterView   $view
     *
     * @return \Illuminate\Http\Response
     */
    public function viewUpdate(Request $request, FilterView $view)
    {
        $status    = true;
        $errors    = null;
        $view_name = null;
        $data      = $request->all();

        // If the specified "Filter View" is valid and view id is equal to posted id.
        if (isset($view) && isset($request->id) && $view->id == $request->id) {
            $validation = FilterView::viewValidate($data);

            // If validation passes and the auth user has permission to edit this view.
            if ($validation->passes() && $view->auth_can_edit) {
                $view->view_name    = $view_name = $request->view_name;
                $view->visible_type = $request->visible_to;
                $view->visible_to   = $request->visible_to == 'selected_users' && count($request->selected_users)
                                      ? json_encode($request->selected_users) : null;
                $view->update();
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        } else {
            $status = false;
        }

        return response()->json([
            'falseReload' => true,
            'status'      => $status,
            'errors'      => $errors,
            'viewName'    => $view_name,
            'viewId'      => $request->id,
        ]);
    }

    /**
     * Get specified module "Filter View" dropdown.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $filterview_id
     *
     * @return \Illuminate\Http\Response
     */
    public function viewDropdown(Request $request, $filterview_id)
    {
        $status       = true;
        $errors       = null;
        $view_id      = null;
        $action_html  = null;
        $filter_count = null;
        $kanban_count = [];
        $realtime     = [];
        $module_name  = $request->module;
        $view         = FilterView::find($filterview_id);

        // If the specified "Filter View" is valid, view id is equal to posted id
        // and the auth user has permission to view this "Filter View".
        if (isset($view) && isset($request->id) && $view->id == $request->id && $view->auth_can_view) {
            $detach_views = auth_staff()->views()->where('module_name', $view->module_name)
                                                 ->pluck('filter_views.id')
                                                 ->toArray();

            if (count($detach_views)) {
                auth_staff()->views()->detach($detach_views);
            }

            auth_staff()->views()->attach($view->id);

            $view_id      = $view->id;
            $module_name  = $view->module_name;
            $action_html  = $view->action_btns_html;
            $model        = morph_to_model($module_name);
            $filter_count = count_if_countable(json_decode($view->filter_params, true));

            // Update kanban stage items count on change breadcrumb filter view
            if (method_exists($model, 'getKanbanStageCount')) {
                $kanban_count = $model::getKanbanStageCount();
            }

            if (array_key_exists('timeperiod_display', $view->optional_param)) {
                $realtime[] = ['timeperiod', $view->optional_param['timeperiod_display']];
            }
        } else {
            if (in_array($module_name, FilterView::getValidModuleList())) {
                $view_id = $current_filter->id;
                $current_filter = FilterView::getCurrentFilter($module_name);
            }

            $status = false;
        }

        return response()->json([
            'status'         => $status,
            'errors'         => $errors,
            'viewId'         => $view_id,
            'realtime'       => $realtime,
            'module'         => $module_name,
            'viewActionHtml' => $action_html,
            'filterCount'    => $filter_count,
            'kanbanCount'    => $kanban_count,
        ]);
    }

    /**
     * Remove the specified "Filter View" from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FilterView   $view
     *
     * @return \Illuminate\Http\Response
     */
    public function viewDestroy(Request $request, FilterView $view)
    {
        $status          = true;
        $filter_count    = null;
        $deleted_view_id = null;
        $default_view_id = null;

        // Don't remove if the view is fixed or default or
        // the auth user doesn't have permission to delete or
        // view id is not equal to posted id.
        if ($view->is_fixed || $view->is_default || ! $view->auth_can_delete || $view->id != $request->id) {
            $status = false;
        }

        if ($status == true) {
            $default_view    = FilterView::where('module_name', $view->module_name)
                                         ->where('is_fixed', 1)
                                         ->where('is_default', 1)
                                         ->first();
            $default_view_id = $default_view->id;
            $deleted_view_id = $view->id;
            $filter_count    = count_if_countable(json_decode($default_view->filter_params, true));

            $view->staffs()->detach();
            $view->delete();
        }

        return response()->json([
            'status'        => $status,
            'filterCount'   => $filter_count,
            'deletedViewId' => $deleted_view_id,
            'defaultViewId' => $default_view_id,
            'module'        => $view->module_name,
        ]);
    }
}
