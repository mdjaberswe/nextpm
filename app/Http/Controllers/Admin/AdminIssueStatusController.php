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

use App\Models\IssueStatus;
use App\Jobs\SyncIssueFilterFixedView;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminIssueStatusController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:custom_dropdowns.issue_status.view', ['only' => ['index', 'issueStatusData']]);
        $this->middleware('admin:custom_dropdowns.issue_status.create', ['only' => ['store']]);
        $this->middleware('admin:custom_dropdowns.issue_status.edit', ['only' => ['edit', 'update']]);
        $this->middleware('admin:custom_dropdowns.issue_status.delete', ['only' => ['destroy']]);
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
            'title'             => 'Issue Status List',
            'item'              => 'Issue Status',
            'field'             => 'issue_status',
            'view'              => 'admin.issuestatus',
            'route'             => 'admin.administration-dropdown-issuestatus',
            'plain_route'       => 'admin.issuestatus',
            'permission'        => 'custom_dropdowns.issue_status',
            'subnav'            => 'custom-dropdown',
            'modal_size'        => 'medium',
            'multi_section'     => true,
            'modal_bulk_delete' => false,
            'save_and_new'      => false,
        ];

        $table = IssueStatus::getTableFormat();
        $reset_position = IssueStatus::resetPosition();

        return view('admin.issuestatus.index', compact('page', 'table', 'reset_position'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function issueStatusData(Request $request)
    {
        // Order by position and only show columns are selected.
        $data = IssueStatus::orderBy('position')->selectColumn()->get();

        return IssueStatus::getTableData($data, $request);
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
        $validation = IssueStatus::validate($data);
        $picked_position_id = $request->position;

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position_val              = IssueStatus::getTargetPositionVal($picked_position_id);
            $issue_status              = new IssueStatus;
            $issue_status->name        = $request->name;
            $issue_status->position    = $position_val;
            $issue_status->category    = $request->category;
            $issue_status->description = null_if_empty($request->description);
            $issue_status->save();

            $save_id = $issue_status->id;
            // Sync with the parent module filter view.
            dispatch(new SyncIssueFilterFixedView);
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
     * @param \App\Models\IssueStatus  $issue_status
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, IssueStatus $issue_status)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;

            // If the specified resource is valid.
            if (isset($issue_status) && isset($request->id) && $issue_status->id == $request->id) {
                $info = $issue_status->toArray();
                $info['position'] = $issue_status->prev_position_id;
                $info['freeze']   = [];

                // If the specified resource is fixed then the category field can not be changed but fixed.
                if ($issue_status->fixed) {
                    $info['freeze'][] = 'category';
                }

                $info = (object) $info;
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info]);
        }

        return redirect()->route('admin.administration-dropdown-issuestatus.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\IssueStatus  $issue_status
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IssueStatus $issue_status)
    {
        $status = true;
        $errors = null;
        $data   = $request->all();

        // If the specified resource is valid.
        if (isset($issue_status) && isset($request->id) && $issue_status->id == $request->id) {
            $validation = IssueStatus::validate($data, $issue_status);
            $picked_position_id = $request->position;

            if ($validation->passes()) {
                $position_val = IssueStatus::getTargetPositionVal($picked_position_id, $issue_status->id);
                $issue_status->name  = $request->name;
                $issue_status->position = $position_val;
                $issue_status->description = null_if_empty($request->description);

                // If the specified resource is not fixed then update the category field.
                if (! $issue_status->fixed) {
                    $issue_status->category = $request->category;
                }

                $issue_status->update();

                // Sync with the parent module filter view.
                dispatch(new SyncIssueFilterFixedView);
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
     * @param \App\Models\IssueStatus  $issue_status
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, IssueStatus $issue_status)
    {
        $status = ($issue_status->id == $request->id && ! $issue_status->fixed);

        // If the specified resource is valid and not fixed.
        if ($status) {
            $lower_status = IssueStatus::whereCategory($issue_status->category)
                                       ->where('id', '!=', $issue_status->id)
                                       ->where('position', '<', $issue_status->position);

            if ($lower_status->count()) {
                $replace_status_id = $lower_status->latest('position')->first()->id;
            } else {
                $replace_status_id = IssueStatus::whereCategory($issue_status->category)
                                                ->where('id', '!=', $issue_status->id)
                                                ->orderBy('position')
                                                ->first()->id;
            }

            // Update all related parent module field with an alternative value.
            $issue_status->issues()->update(['issue_status_id' => $replace_status_id]);
            $issue_status->delete();

            // Sync with the parent module filter view.
            dispatch(new SyncIssueFilterFixedView);
        }

        return response()->json(['status' => $status]);
    }
}
