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

use App\Models\IssueType;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminIssueTypeController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:custom_dropdowns.issue_type.view', ['only' => ['index', 'issueTypeData']]);
        $this->middleware('admin:custom_dropdowns.issue_type.create', ['only' => ['store']]);
        $this->middleware('admin:custom_dropdowns.issue_type.edit', ['only' => ['edit', 'update']]);
        $this->middleware('admin:custom_dropdowns.issue_type.delete', ['only' => ['destroy']]);
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
            'title'             => 'Issue Type List',
            'item'              => 'Issue Type',
            'field'             => 'issue_types',
            'view'              => 'admin.issuetype',
            'route'             => 'admin.administration-dropdown-issuetype',
            'plain_route'       => 'admin.issuetype',
            'permission'        => 'custom_dropdowns.issue_type',
            'subnav'            => 'custom-dropdown',
            'modal_size'        => 'medium',
            'multi_section'     => true,
            'modal_bulk_delete' => false,
            'save_and_new'      => false,
        ];

        $table = IssueType::getTableFormat();
        $reset_position = IssueType::resetPosition();

        return view('admin.issuetype.index', compact('page', 'table', 'reset_position'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function issueTypeData(Request $request)
    {
        // Order by position and only show columns are selected.
        $data = IssueType::orderBy('position')->selectColumn()->get();

        return IssueType::getTableData($data, $request);
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
        $validation = IssueType::validate($data);
        $picked_position_id = $request->position;

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position_val            = IssueType::getTargetPositionVal($picked_position_id);
            $issue_type              = new IssueType;
            $issue_type->name        = $request->name;
            $issue_type->position    = $position_val;
            $issue_type->description = null_if_empty($request->description);
            $issue_type->save();

            // Response saved id to highlight new stored table row
            $save_id = $issue_type->id;
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
     * @param \App\Models\IssueType    $issue_type
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, IssueType $issue_type)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;

            // If the specified resource is valid.
            if (isset($issue_type) && isset($request->id) && $issue_type->id == $request->id) {
                $info = $issue_type->toArray();
                $info['position'] = $issue_type->prev_position_id;
                $info = (object) $info;
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info]);
        }

        return redirect()->route('admin.administration-dropdown-issuetype.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\IssueType    $issue_type
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IssueType $issue_type)
    {
        $status = true;
        $errors = null;
        $data   = $request->all();

        // If the specified resource is valid.
        if (isset($issue_type) && isset($request->id) && $issue_type->id == $request->id) {
            $validation = IssueType::validate($data);
            $picked_position_id = $request->position;

            if ($validation->passes()) {
                $position_val = IssueType::getTargetPositionVal($picked_position_id, $issue_type->id);
                $issue_type->name = $request->name;
                $issue_type->position = $position_val;
                $issue_type->description = null_if_empty($request->description);
                $issue_type->save();
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
     * @param \App\Models\IssueType    $issue_type
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, IssueType $issue_type)
    {
        $status = $issue_type->id == $request->id;

        // If the specified resource is valid.
        if ($status) {
            // Update all related parent module field value with null.
            $issue_type->issues()->update(['issue_type_id' => null]);
            $issue_type->delete();
        }

        return response()->json(['status' => $status]);
    }
}
