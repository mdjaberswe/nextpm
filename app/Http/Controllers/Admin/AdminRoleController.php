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

use App\Models\Role;
use App\Models\Permission;
use App\Models\FilterView;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminRoleController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:role.view', ['only' => ['index', 'roleData', 'show', 'usersList']]);
        $this->middleware('admin:role.create', ['only' => ['create', 'store']]);
        $this->middleware('admin:role.edit', ['only' => ['edit', 'update']]);
        $this->middleware('admin:role.delete', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('admin:mass_delete.role', ['only' => ['bulkDestroy']]);

        // Demo mode middleware
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy', 'bulkDestroy']]);
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
        // and resource table format(heading, columns).
        $page = [
            'title'             => 'Roles List',
            'item'              => 'Role',
            'field'             => 'roles',
            'view'              => 'admin.role',
            'route'             => 'admin.role',
            'permission'        => 'role',
            'modal_create'      => false,
            'modal_edit'        => false,
            'script'            => true,
            'filter'            => true,
            'breadcrumb'        => FilterView::getBreadcrumb('role'),
            'current_filter'    => FilterView::getCurrentFilter('role'),
            'modal_bulk_delete' => permit('mass_delete.role'),
        ];

        $table = Role::getTableFormat();

        return view('admin.role.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function roleData(Request $request)
    {
        // Filter by only general type and current filter parameter.
        $roles = Role::onlyGeneral()->filterViewData()->selectColumn()->orderBy('id')->get();

        return Role::getTableData($roles, $request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $permissions_groups = Permission::getPermissionsGroups(null, false);
        $page = ['title' => 'Add New Role', 'item_title' => breadcrumb('admin.role.index:Roles|Add New Role')];

        return view('admin.role.create', compact('page', 'permissions_groups'));
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
        $data = $request->all();
        $validation = Role::validate($data);

        // If request ajax and validation fails then respond error without loading page.
        if ($request->ajax()) {
            $status = $validation->fails() ? false : true;
            $errors = $status ? null : $validation->getMessageBag()->toArray();

            return response()->json(['status' => $status, 'errors' => $errors, 'btnDisabled' => $status]);
        }

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation);
        }

        // Validation passes now save posted data.
        $role               = new Role;
        $role->name         = trim_lower_snake($request->name);
        $role->display_name = $request->name;
        $role->description  = $request->description;
        $role->save();

        // Check posted permissions with DB permissions value.
        if (count($request->permissions)) {
            $valid_permission = Permission::whereIn('id', $request->permissions)->notPreserve()->pluck('id')->toArray();
            $role->permissions()->attach($valid_permission);
        }

        $success_message = 'Role has been created.';

        if (isset($request->add_new) && $request->add_new == 1) {
            return redirect(route('admin.role.create', [], false))->with('success_message', $success_message);
        }

        return redirect(route('admin.role.show', $role->id, false))->with('success_message', $success_message);
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role         $role
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Role $role)
    {
        $permissions_groups = Permission::getPermissionsGroups($role, false);
        $page = [
            'title' => 'Role: ' . $role->display_name,
            'item_title' => breadcrumb('admin.role.index:Roles|' . $role->display_name),
        ];

        return view('admin.role.show', compact('page', 'role', 'permissions_groups'));
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role         $role
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Role $role)
    {
        // If the role is fixed then can not be editable.
        if ($role->fixed == true) {
            return redirect()->route('admin.role.index');
        }

        $permissions_groups = Permission::getPermissionsGroups($role, false);
        $breadcrumb = 'admin.role.index:Roles|admin.role.show,' .$role->id . ':' . $role->display_name . '|Edit';
        $page = ['title' => 'Edit Role: ' . $role->display_name, 'item_title' => breadcrumb($breadcrumb)];

        return view('admin.role.edit', compact('page', 'role', 'permissions_groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role         $role
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->all();
        $validation = Role::validate($data);

        // If request ajax and validation fails then respond error without loading page.
        if ($request->ajax()) {
            $status = $validation->fails() ? false : true;
            $errors = $status ? null : $validation->getMessageBag()->toArray();

            return response()->json(['status' => $status, 'errors' => $errors, 'btnDisabled' => $status]);
        }

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation);
        }

        // If the role is fixed then can not be editable.
        if ($role->fixed == true) {
            return redirect()->route('admin.role.index');
        }

        // Posted id validation.
        if ($role->id != $request->id) {
            $warning_message = 'Sorry, Something went wrong! Please try again.';

            return redirect()->back()->with('warning_message', $warning_message);
        }

        // Validation passes now updated posted data.
        $role->name         = trim_lower_snake($request->name);
        $role->display_name = $request->name;
        $role->description  = $request->description;
        $role->update();

        // If has posted permissions then check posted permissions with DB permissions value.
        if (count($request->permissions)) {
            $valid_permission = Permission::whereIn('id', $request->permissions)->notPreserve()->pluck('id')->toArray();
            $role->permissions()->sync($valid_permission);
        } else {
            $role->permissions()->detach();
        }

        $success_message = 'Role has been updated.';

        return redirect(route('admin.role.show', $role->id, false))->with('success_message', $success_message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role         $role
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Role $role)
    {
        $status        = true;
        $redirect      = null;
        $standard_role = Role::whereName('standard')->whereFixed(1)->first();

        // If the role is fixed or posted id is invalid or fixed standard role not found then return false.
        if ($role->fixed == true || $role->id != $request->id || ! isset($standard_role)) {
            $status = false;
        }

        if ($status == true) {
            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                if (isset($role->next_record)) {
                    $redirect = route('admin.role.show', $role->next_record->id);
                } elseif (isset($role->prev_record)) {
                    $redirect = route('admin.role.show', $role->prev_record->id);
                } else {
                    $redirect = route('admin.role.index');
                }
            }

            // All users of the deleted role updated with the standard role.
            if ($role->users->count()) {
                foreach ($role->users as $user) {
                    if ($user->roles->count() <= 1) {
                        $user->roles()->attach($standard_role->id);
                    }
                }
            }

            $role->delete();
        }

        return response()->json(['status' => $status, 'redirect' => $redirect]);
    }

    /**
     * Remove mass resources from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(Request $request)
    {
        $roles         = $request->roles;
        $standard_role = Role::whereName('standard')->whereFixed(1)->first();
        $status        = true;

        // If $request has roles and the fixed standard role has been found.
        if (isset($roles) && count($roles) > 0 && isset($standard_role)) {
            $query_roles = Role::whereFixed(0)->whereIn('id', $roles);

            // Deleted role users move to the standard role.
            foreach ($query_roles->get() as $query_role) {
                if ($query_role->users->count()) {
                    foreach ($query_role->users as $user) {
                        if ($user->roles->count() <= 1) {
                            $user->roles()->attach($standard_role->id);
                        }
                    }
                }
            }

            $query_roles->delete();
        } else {
            $status = false;
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Get a user list of the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Role         $role
     *
     * @return \Illuminate\Http\Response
     */
    public function usersList(Request $request, Role $role)
    {
        $status = true;
        $info   = null;

        // If role is valid then get all users associated with the role.
        if (isset($role) && isset($request->id) && $role->id == $request->id) {
            $users_list = $role->users_list_html;
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'users' => $users_list]);
    }
}
