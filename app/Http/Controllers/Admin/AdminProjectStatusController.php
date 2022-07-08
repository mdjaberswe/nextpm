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

use App\Models\ProjectStatus;
use App\Jobs\SyncProjectFilterFixedView;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminProjectStatusController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:custom_dropdowns.project_status.view', ['only' => ['index', 'projectStatusData']]);
        $this->middleware('admin:custom_dropdowns.project_status.create', ['only' => ['store']]);
        $this->middleware('admin:custom_dropdowns.project_status.edit', ['only' => ['edit', 'update']]);
        $this->middleware('admin:custom_dropdowns.project_status.delete', ['only' => ['destroy']]);
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
            'title'             => 'Project Status List',
            'item'              => 'Project Status',
            'field'             => 'project_status',
            'view'              => 'admin.projectstatus',
            'route'             => 'admin.administration-dropdown-projectstatus',
            'plain_route'       => 'admin.projectstatus',
            'permission'        => 'custom_dropdowns.project_status',
            'subnav'            => 'custom-dropdown',
            'modal_size'        => 'medium',
            'multi_section'     => true,
            'save_and_new'      => false,
            'modal_bulk_delete' => false,
        ];

        $table = ProjectStatus::getTableFormat();
        $reset_position = ProjectStatus::resetPosition();

        return view('admin.projectstatus.index', compact('page', 'table', 'reset_position'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function projectStatusData(Request $request)
    {
        // Order by position and only show columns are selected.
        $data = ProjectStatus::orderBy('position')->selectColumn()->get();

        return ProjectStatus::getTableData($data, $request);
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
        $validation = ProjectStatus::validate($data);
        $picked_position_id = $request->position;

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position_val                = ProjectStatus::getTargetPositionVal($picked_position_id);
            $project_status              = new ProjectStatus;
            $project_status->name        = $request->name;
            $project_status->position    = $position_val;
            $project_status->category    = $request->category;
            $project_status->description = null_if_empty($request->description);
            $project_status->save();

            // Response saved id to highlight new stored table row
            $save_id = $project_status->id;

            // Sync with the parent module filter view.
            dispatch(new SyncProjectFilterFixedView);
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'saveId' => $save_id]);
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\ProjectStatus $project_status
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, ProjectStatus $project_status)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;

            // If the specified resource is valid.
            if (isset($project_status) && isset($request->id) && $project_status->id == $request->id) {
                $info = $project_status->toArray();
                $info['position'] = $project_status->prev_position_id;
                $info['freeze']   = [];

                // If the specified resource is fixed then the category field can not be changed but fixed.
                if ($project_status->fixed) {
                    $info['freeze'][] = 'category';
                }

                $info = (object) $info;
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info]);
        }

        return redirect()->route('admin.administration-dropdown-projectstatus.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\ProjectStatus $project_status
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProjectStatus $project_status)
    {
        $status = true;
        $errors = null;
        $data   = $request->all();

        // If the specified resource is valid.
        if (isset($project_status) && isset($request->id) && $project_status->id == $request->id) {
            $validation = ProjectStatus::validate($data, $project_status);
            $picked_position_id = $request->position;

            if ($validation->passes()) {
                $position_val = ProjectStatus::getTargetPositionVal($picked_position_id, $project_status->id);
                $project_status->name = $request->name;
                $project_status->position = $position_val;
                $project_status->description = null_if_empty($request->description);

                // If the specified resource is not fixed then update the category field.
                if (! $project_status->fixed) {
                    $project_status->category = $request->category;
                }

                $project_status->update();

                // Sync with the parent module filter view.
                dispatch(new SyncProjectFilterFixedView);
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
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\ProjectStatus $project_status
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ProjectStatus $project_status)
    {
        $status = ($project_status->id == $request->id && ! $project_status->fixed);

        // If the specified resource is valid and not fixed.
        if ($status) {
            $lower_status = ProjectStatus::whereCategory($project_status->category)
                                         ->where('id', '!=', $project_status->id)
                                         ->where('position', '<', $project_status->position);

            if ($lower_status->count()) {
                $replace_status_id = $lower_status->latest('position')->first()->id;
            } else {
                $replace_status_id = ProjectStatus::whereCategory($project_status->category)
                                                  ->where('id', '!=', $project_status->id)
                                                  ->orderBy('position')
                                                  ->first()->id;
            }

            // Update all related parent module field with an alternative value.
            $project_status->projects()->update(['project_status_id' => $replace_status_id]);
            $project_status->delete();

            // Sync with the parent module filter view.
            dispatch(new SyncProjectFilterFixedView);
        }

        return response()->json(['status' => $status]);
    }
}
