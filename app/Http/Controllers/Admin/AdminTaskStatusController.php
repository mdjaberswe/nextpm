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

use App\Models\TaskStatus;
use App\Jobs\SyncTaskFilterFixedView;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminTaskStatusController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:custom_dropdowns.task_status.view', ['only' => ['index', 'taskstatusData']]);
        $this->middleware('admin:custom_dropdowns.task_status.create', ['only' => ['store']]);
        $this->middleware('admin:custom_dropdowns.task_status.edit', ['only' => ['edit', 'update']]);
        $this->middleware('admin:custom_dropdowns.task_status.delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Page information like title, user permission, current filter, breadcrumb,
        // and resource table format(heading, columns), reset all resource data positions.
        $page = [
            'title'             => 'Task Status List',
            'item'              => 'Task Status',
            'field'             => 'task_status',
            'view'              => 'admin.taskstatus',
            'route'             => 'admin.administration-dropdown-taskstatus',
            'plain_route'       => 'admin.taskstatus',
            'permission'        => 'custom_dropdowns.task_status',
            'subnav'            => 'custom-dropdown',
            'modal_size'        => 'medium',
            'multi_section'     => true,
            'modal_bulk_delete' => false,
            'save_and_new'      => false,
        ];

        $table = TaskStatus::getTableFormat();
        $reset_position = TaskStatus::resetPosition();

        return view('admin.taskstatus.index', compact('page', 'table', 'reset_position'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function taskStatusData(Request $request)
    {
        // Order by position and only show columns are selected.
        $data = TaskStatus::orderBy('position')->selectColumn()->get();

        return TaskStatus::getTableData($data, $request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status     = true;
        $errors     = null;
        $save_id    = null;
        $data       = $request->all();
        $validation = TaskStatus::validate($data);
        $picked_position_id = $request->position;

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position_val             = TaskStatus::getTargetPositionVal($picked_position_id);
            $task_status              = new TaskStatus;
            $task_status->name        = $request->name;
            $task_status->position    = $position_val;
            $task_status->category    = $request->category;
            $task_status->description = null_if_empty($request->description);
            $task_status->completion_percentage = $request->category == 'open' ? $request->completion_percentage : 100;
            $task_status->save();

            // Response saved id to highlight new stored table row
            $save_id = $task_status->id;

            // Sync with the parent module filter view.
            dispatch(new SyncTaskFilterFixedView);
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'saveId' => $save_id]);
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TaskStatus   $task_status
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, TaskStatus $task_status)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;

            // If the specified resource is valid.
            if (isset($task_status) && isset($request->id) && $task_status->id == $request->id) {
                $info = $task_status->toArray();
                $info['position'] = $task_status->prev_position_id;
                $info['freeze']   = [];

                // If the specified resource is fixed then the category field can not be changed but fixed.
                if ($task_status->fixed) {
                    $info['freeze'][] = 'category';
                }

                $info = (object) $info;
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info]);
        }

        return redirect()->route('admin.administration-dropdown-taskstatus.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TaskStatus   $task_status
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaskStatus $task_status)
    {
        $status = true;
        $errors = null;
        $data   = $request->all();

        // If the specified resource is valid.
        if (isset($task_status) && isset($request->id) && $task_status->id == $request->id) {
            $validation = TaskStatus::validate($data, $task_status);
            $picked_position_id = $request->position;

            if ($validation->passes()) {
                $position_val = TaskStatus::getTargetPositionVal($picked_position_id, $task_status->id);
                $category = $task_status->category;
                $task_status->name = $request->name;
                $task_status->position = $position_val;
                $task_status->description = null_if_empty($request->description);

                // If the specified resource is not fixed then update the category field.
                if (! $task_status->fixed) {
                    $task_status->category = $request->category;
                    $category = $request->category;
                }

                $task_status->completion_percentage = $category == 'open' ? $request->completion_percentage : 100;
                $task_status->update();

                // Sync with the parent module filter view.
                dispatch(new SyncTaskFilterFixedView);
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'saveId' => $request->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TaskStatus   $task_status
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, TaskStatus $task_status)
    {
        $status = ($task_status->id == $request->id && ! $task_status->fixed);

        // If the specified resource is valid and not fixed.
        if ($status) {
            $lower_status = TaskStatus::whereCategory($task_status->category)
                                      ->where('id', '!=', $task_status->id)
                                      ->where('position', '<', $task_status->position);

            if ($lower_status->count()) {
                $replace_status_id = $lower_status->latest('position')->first()->id;
            } else {
                $replace_status_id = TaskStatus::whereCategory($task_status->category)
                                               ->where('id', '!=', $task_status->id)
                                               ->orderBy('position')
                                               ->first()->id;
            }

            // Update all related parent module field with an alternative value.
            $task_status->tasks()->update(['task_status_id' => $replace_status_id]);
            $task_status->delete();

            // Sync with the parent module filter view.
            dispatch(new SyncTaskFilterFixedView);
        }

        return response()->json(['status' => $status]);
    }
}
