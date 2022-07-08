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

use Notification;
use App\Models\User;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Follower;
use App\Models\Revision;
use App\Models\ChatRoom;
use App\Models\FilterView;
use App\Models\ChatSender;
use App\Models\ChatReceiver;
use App\Models\AllowedStaff;
use App\Models\ChatRoomMember;
use App\Events\UserCreated;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminUserController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:user.view', ['only' => ['index', 'userData']]);
        $this->middleware('admin:user.create', ['only' => ['store']]);
        $this->middleware(['admin:user.delete', 'command.chain:delete'], ['only' => ['destroy']]);
        $this->middleware(['admin:user.delete', 'admin:mass_delete.user'], ['only' => ['bulkDestroy']]);
        $this->middleware(['admin:user.edit', 'command.chain:edit'], ['only' => [
            'edit', 'update', 'updatePassword', 'updateStatus'
        ]]);

        // Demo mode middleware
        $this->middleware('demo', ['only' => [
            'store', 'update', 'updatePassword', 'updateStatus', 'bulkStatus', 'destroy', 'bulkDestroy'
        ]]);
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
            'title'             => 'Users List',
            'item'              => 'User',
            'field'             => 'staffs',
            'view'              => 'admin.user',
            'route'             => 'admin.user',
            'permission'        => 'user',
            'add_icon'          => 'fa fa-user-plus',
            'modal_size'        => 'auto',
            'bulk'              => 'status:active|inactive',
            'script'            => true,
            'filter'            => true,
            'current_filter'    => FilterView::getCurrentFilter('staff'),
            'breadcrumb'        => FilterView::getBreadcrumb('staff', 'user'),
            'modal_bulk_delete' => permit('mass_delete.user'),
        ];

        $table = Staff::getTableFormat();

        return view('admin.user.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function userData(Request $request)
    {
        // Response resource data in JSON format and filter by current filter parameter.
        $staffs = Staff::filterViewData()
                       ->filterMask()
                       ->orderBy('staffs.id')
                       ->get()
                       ->sortByDesc('super_admin')
                       ->sortByDesc('admin')
                       ->sortBy('id');

        return Staff::getTableData($staffs, $request);
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
        $data       = $request->all() + ['staffs_id' => Staff::pluck('id')->toArray()];
        $validation = Staff::validate($data);

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $staff             = new Staff;
            $staff->first_name = $request->first_name;
            $staff->last_name  = $request->last_name;
            $staff->title      = $request->title;
            $staff->phone      = $request->phone;
            $staff->settings   = json_encode(['chat_sound' => 'on']);
            $staff->save();

            $user              = new User;
            $user->email       = $request->email;
            $user->password    = bcrypt($request->password);
            $user->linked_id   = $staff->id;
            $user->linked_type = 'staff';
            $user->save();
            $user->roles()->attach($request->role);

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'saveId' => $staff->id]);

            // User-created event.
            event(new UserCreated($staff, $data));
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     * @param string|null              $infotype
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Staff $staff, $infotype = null)
    {
        // Check user view permission and valid resource id.
        if (! permit('user.view') && auth_staff()->id != $staff->id) {
            return redirect()->to(valid_app_url(url()->previous(), route('home')));
        }

        // Pass $page variable with title, breadcrumb, and tabs information.
        $page       = [
            'title'        => 'User: ' . $staff->name,
            'item_title'   => $staff->show_page_breadcrumb,
            'item'         => 'User',
            'view'         => 'admin.user',
            'tabs'         => [
                'list'     => Staff::informationTypes(),
                'default'  => Staff::defaultInfoType($infotype),
                'tab_item' => 'Staff',
                'item_id'  => $staff->id,
                'url'      => 'tab/user',
            ],
        ];

        return view('admin.user.show', compact('page', 'staff'));
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Staff $staff)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;

            // If the specified resource is valid then follow the next execution.
            if (isset($staff) && isset($request->id) && $staff->id == $request->id) {
                $info           = $staff->toArray();
                $info['email']  = $staff->email;
                $info['role[]'] = $staff->roles_list;
                $info['freeze'] = [];

                // If the auth user is not an admin and the specified resource user is not logged in
                // then the email field can not be editable.
                if (auth_staff()->admin == false && isset($staff) && $staff->logged_in == false) {
                    $info['freeze'][] = 'email';
                }

                // If the auth user is not an admin or the specified resource user is logged in
                // then the role field can not be editable.
                if (auth_staff()->admin == false || isset($staff) && $staff->logged_in == true) {
                    $info['freeze'][] = 'role[]';
                }

                $info = (object) $info;
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info]);
        }

        return redirect()->route('admin.user.show', $staff->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Staff $staff)
    {
        $status = true;
        $errors = null;
        $data   = $request->all() + ['user_id' => $staff->user->id];

        // If the specified resource is valid then follow the next execution.
        if (isset($staff) && isset($request->id) && $staff->id == $request->id) {
            $validation = Staff::validate($data);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                $staff->first_name = $request->first_name;
                $staff->last_name  = $request->last_name;
                $staff->title      = $request->title;
                $staff->phone      = $request->phone;
                $staff->save();

                // If the auth user is admin or this specified resource user is logged in.
                if (auth_staff()->admin || $staff->logged_in) {
                    $user = $staff->user;
                    $user->email = $request->email;
                    $user->save();
                }

                // If the auth user is admin and this specified resource user is not logged in.
                if (auth_staff()->admin && ! $staff->logged_in) {
                    if (count($request->role)) {
                        $old_roles = $staff->roles_list;
                        $user->roles()->sync($request->role);

                        // Record role change history.
                        if ($old_roles != $request->role) {
                            Revision::create([
                                'revisionable_type' => 'staff',
                                'revisionable_id'   => $staff->id,
                                'user_id'           => auth()->user()->id,
                                'key'               => 'role',
                                'old_value'         => json_encode($old_roles),
                                'new_value'         => json_encode($request->role),
                            ]);
                        }
                    } else {
                        $user->roles()->detach();
                    }
                }
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
     * Update a single field of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function singleUpdate(Request $request, Staff $staff)
    {
        $status        = true;
        $errors        = null;
        $html          = null;
        $history       = null;
        $updated_by    = null;
        $last_modified = null;
        $modal_title   = null;
        $realtime      = [];
        $real_replace  = [];
        $data          = $request->all();

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($staff) && permit('user.edit')) {
            $data['id'] = $staff->id;
            $validation = Staff::singleValidate($data);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                $update_data = replace_null_if_empty($request->all());
                $staff->update($update_data);

                if (isset($request->email) && $staff->edit_email) {
                    $staff->user->update($update_data);
                }

                if (isset($request->role) && $staff->edit_role) {
                    $old_roles  = $staff->roles_list;
                    $staff->user->roles()->sync($request->role);
                    $html       = implode(', ', $staff->fresh()->roles_name_list);
                    $realtime[] = ['admin_status', $staff->admin_html];

                    if ($old_roles != $request->role) {
                        Revision::create([
                            'revisionable_type' => 'staff',
                            'revisionable_id'   => $staff->id,
                            'user_id'           => auth()->user()->id,
                            'key'               => 'role',
                            'old_value'         => json_encode($old_roles),
                            'new_value'         => json_encode($request->role),
                        ]);
                    }
                }

                $media_exists = array_intersect(array_keys($data), ['facebook', 'twitter', 'skype', 'linkedin']);

                if (count($media_exists)) {
                    $media = $media_exists[0];
                    $staff->socialmedia()->whereMedia($media)->forceDelete();
                    $staff->socialmedia()->create([
                        'media' => $media,
                        'data'  => json_encode(['link' => $request->$media]),
                    ]);

                    if ($media == 'skype') {
                        $html = non_property_checker($staff->getSocialDataAttribute($media), 'link');
                    } else {
                        $html = "<a href='" . $staff->getSocialLinkAttribute($media) . "' target='_blank'>" .
                                    non_property_checker($staff->getSocialDataAttribute($media), 'link') .
                                "</a>";
                    }
                }

                // Realtime HTML content changes on the page according to the updated field.
                if (isset($request->first_name)) {
                    $html = $staff->name;
                } elseif (isset($request->date_of_birth)) {
                    $html = not_null_empty($staff->date_of_birth) ? $staff->readableDate('date_of_birth') : '';
                } elseif (isset($request->website)) {
                    $html = "<a href='" . quick_url($staff->website) . "' target='_blank'>" .
                                $staff->website .
                            "</a>";
                }

                $history = $staff->recent_history_html;
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        } else {
            $status = false;
        }

        return response()->json([
            'status'       => $status,
            'errors'       => $errors,
            'updatedBy'    => $updated_by,
            'modalTitle'   => $modal_title,
            'lastModified' => $last_modified,
            'realReplace'  => $real_replace,
            'realtime'     => $realtime,
            'history'      => $history,
            'html'         => $html,
        ]);
    }

    /**
     * Update the user password.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, Staff $staff)
    {
        $status = true;
        $errors = null;
        $data   = $request->all();

        // If the specified resource is valid
        // and the auth user is admin or the specified resource user is logged in.
        if (isset($staff)
            && isset($request->id)
            && $staff->id == $request->id
            && (auth_staff()->admin || $staff->logged_in)
        ) {
            $rules = [
                'password' => 'required|min:6|max:60|confirmed',
                'password_confirmation' => 'required|min:6|max:60',
            ];

            $validation = validator($data, $rules);

            // If validation passes and the auth user can edit this user's password.
            if ($validation->passes() && $staff->auth_can_edit_password) {
                $user = $staff->user;
                $user->password = bcrypt($request->password);
                $user->save();
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
    }

    /**
     * Update user status.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Staff $staff)
    {
        $status  = false;
        $checked = null;

        // If the specified resource is valid,
        // the auth user is an admin,
        // this user is not the super admin and not logged in.
        if (isset($staff)
            && isset($request->id)
            && $staff->id == $request->id
            && isset($request->checked)
            && auth_staff()->admin
            && ! $staff->super_admin
            && ! $staff->logged_in
        ) {
            $checked = $request->checked ? 1 : 0;
            $staff->user->update(['status' => $checked]);
            $status  = true;
        }

        return response()->json(['status' => $status, 'checked' => $checked]);
    }

    /**
     * Add users to the 'allowed users' list table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function allowedUserData(Request $request)
    {
        $status  = true;
        $errors  = null;
        $html    = '';
        $message = ['max' => 'The :attribute may not have more than :max items at a time.'];
        $rules   = [
            'staffs' => 'required|array|max:10|exists:users,linked_id,linked_type,staff,deleted_at,NULL',
            'serial' => 'required|integer|min:0',
        ];

        $validation = validator($request->all(), $rules, $message);

        // Add users to the "Allowed Users" table if validation passes.
        if ($validation->passes()) {
            $serial = $request->serial;

            // Loop through posted users and render HTML.
            foreach ($request->staffs as $id) {
                $staff = Staff::find($id);
                $html .= "
                <tr data-staff='" . $staff->id . "'>
                    <td>" . ++$serial . "</td>
                    <td>" . $staff->profile_render . "</td>
                    <td>
                        <input type='hidden' name='allowed_staffs[]' value='" . $staff->id . "'>
                        <span class='pretty single info smooth'>
                            <input type='checkbox' name='can_read_" . $staff->id . "' value='1' checked disabled>
                            <label><i class='mdi mdi-check'></i></label>
                        </span>
                    </td>
                    <td>
                        <span class='pretty single info smooth'>
                            <input type='checkbox' name='can_write_" . $staff->id . "' value='1'>
                            <label><i class='mdi mdi-check'></i></label>
                        </span>
                    </td>
                    <td>
                        <span class='pretty single info smooth'>
                            <input type='checkbox' name='can_delete_" . $staff->id . "' value='1'>
                            <label><i class='mdi mdi-check'></i></label>
                        </span>
                    </td>
                    <td>
                        <button class='close' data-toggle='tooltip' data-placement='top' title='Remove'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </td>
                </tr>";
            }
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json(compact('status', 'errors', 'html'));
    }

    /**
     * Get a list of allowed users of a related module type.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $type
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function allowedTypeData(Request $request, $type, $id)
    {
        $status   = false;
        $dropdown = [];
        $html     = '';

        // If the module type is valid.
        if (isset($type)
            && isset($request->type)
            && $type == $request->type
            && isset($id)
            && isset($request->id)
            && $id == $request->id
        ) {
            $type_model  = morph_to_model($type)::find($id);
            $table       = $type . 's';
            $valid_types = AllowedStaff::getValidTypes();
            $rules       = [
                'id'   => "required|exists:$table,id,deleted_at,NULL",
                'type' => "required|in:$valid_types",
            ];

            $validation = validator($request->all(), $rules);

            // If validation passes then render all allowed users list table associated with the related module.
            if ($validation->passes() && isset($type_model)) {
                $dropdown = $type_model->getOwnerList($dropdown, []);

                foreach ($type_model->allowedstaffs as $key => $allowed) {
                    $staff_id   = $allowed->staff->id;
                    $can_edit   = $allowed->can_edit ? 'checked' : '';
                    $can_delete = $allowed->can_delete ? 'checked' : '';
                    $html .= "
                    <tr data-staff='" . $allowed->staff->id . "'>
                        <td>" . ++$key . "</td>
                        <td>" . $allowed->staff->profile_render . "</td>
                        <td>
                            <input type='hidden' name='allowed_staffs[]' value='" . $staff_id . "'>
                            <span class='pretty single info smooth'>
                                <input type='checkbox' name='can_read_" . $staff_id . "' value='1' checked disabled>
                                <label><i class='mdi mdi-check'></i></label>
                            </span>
                        </td>
                        <td>
                            <span class='pretty single info smooth'>
                                <input type='checkbox' name='can_write_" . $staff_id . "' value='1' $can_edit>
                                <label><i class='mdi mdi-check'></i></label>
                            </span>
                        </td>
                        <td>
                            <span class='pretty single info smooth'>
                                <input type='checkbox' name='can_delete_" . $staff_id . "' value='1' $can_delete>
                                <label><i class='mdi mdi-check'></i></label>
                            </span>
                        </td>
                        <td>
                            <button class='close' data-toggle='tooltip' data-placement='top' title='Remove'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                        </td>
                    </tr>";
                }

                $status = true;
            }
        }

        return response()->json(['status' => $status, 'html' => $html, 'list' => $dropdown]);
    }

    /**
     * Update 'Allowed users' list with permissions associated with a module type.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $type
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function postAllowedUser(Request $request, $type, $id)
    {
        // If valid module type.
        if (isset($id)
            && isset($request->id)
            && $id == $request->id
            && isset($type)
            && isset($request->type)
            && $type == $request->type
        ) {
            $type_model  = morph_to_model($type)::find($id);
            $table       = $type . 's';
            $valid_types = AllowedStaff::getValidTypes();
            $rules = [
                'type' => 'required|in:' . $valid_types,
                'id'   => 'required|exists:' . $table . ',id,deleted_at,NULL',
                'allowed_staffs' => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL|distinct',
            ];

            $validation = validator($request->all(), $rules);

            // If validation passes then save allowed users with posted permissions.
            if ($validation->passes() && isset($type_model)) {
                $old_allowed = $type_model->allowedstaffs->pluck('staff_id')->toArray();
                $type_model->allowedstaffs()->forceDelete();

                if (isset($request->allowed_staffs)) {
                    foreach ($request->allowed_staffs as $staff_id) {
                        $can_write  = 'can_write_' . $staff_id;
                        $can_delete = 'can_delete_' . $staff_id;

                        // Save allowed user with permissions
                        $allowed_staff              = new AllowedStaff;
                        $allowed_staff->staff_id    = $staff_id;
                        $allowed_staff->linked_id   = $id;
                        $allowed_staff->linked_type = $type;
                        $allowed_staff->can_edit    = isset($request->$can_write) ? 1 : 0;
                        $allowed_staff->can_delete  = isset($request->$can_delete) ? 1 : 0;
                        $allowed_staff->save();
                    }
                }

                // Realtime HTML content changes on page
                $html         = $type_model->fresh()->access_html;
                $inner_html   = [];
                $inner_html[] = ['.follower-container-box', $type_model->fresh()->display_followers];
                $inner_html[] = ['.show-misc-actions', $type_model->fresh()->show_misc_actions];
                $updated_by   = "<p class='compact'>" . $type_model->updatedByName() . "<br>
                                    <span class='color-shadow sm'>" . $type_model->updated_ampm . "</span>
                                </p>";

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'    => true,
                    'html'      => $html,
                    'updatedBy' => $updated_by,
                    'innerHtml' => $inner_html,
                ]);

                $old_allowed   = User::pluckTypeId('staff', $old_allowed);
                $saved_allowed = $type_model->allowedstaffs()->get()->pluck('user_id')->toArray();
                $new_allowed   = array_diff($saved_allowed, $old_allowed);
                $removed       = array_diff($old_allowed, $saved_allowed);

                // Notify all users associated with this record.
                if (! is_null($type_model->owner) && $type_model->owner_id != auth_staff()->id) {
                    if (count($removed)) {
                        $type_model->owner->user->notify(new CrudNotification($type . '_observer_removed', $id, [
                            'count' => count($removed)
                        ]));
                    }

                    if (count($new_allowed)) {
                        $type_model->owner->user->notify(new CrudNotification($type . '_observer_added', $id, [
                            'count' => count($new_allowed)
                        ]));
                    }
                }
            } else {
                return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
            }
        } else {
            return response()->json(['status' => false, 'errors' => null]);
        }
    }

    /**
     * Update followers associated with a module type.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $type
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function postFollower(Request $request, $type, $id)
    {
        $status     = false;
        $errors     = [];
        $follow     = null;
        $count      = null;
        $html       = null;
        $case       = null;
        $data       = $request->all();
        $validation = Follower::validate($data);

        // Update the following status of the auth user with the related module if validation passes.
        if ($validation->passes()) {
            $related_module = morph_to_model($data['type'])::find($data['id']);

            // If the auth user can follow the specified related module.
            if ($related_module->can_follow) {
                if ($request->follow) {
                    if (! $related_module->follow_status) {
                        $case = $type . '_follower_added';
                        Follower::create([
                            'linked_type' => $data['type'],
                            'linked_id'   => $data['id'],
                            'staff_id'    => auth_staff()->id,
                        ]);
                    }
                } else {
                    $case = $type . '_follower_removed';
                    $ids  = Follower::where('linked_type', $data['type'])
                                    ->where('linked_id', $data['id'])
                                    ->where('staff_id', auth_staff()->id)
                                    ->pluck('id')
                                    ->toArray();

                    Follower::whereIn('id', $ids)->delete();
                    Revision::secureHistory('follower', $ids, 'deleted_at', null, date('Y-m-d H:i:s'));
                }

                if (! is_null($case)
                    && ! is_null($related_module->owner)
                    && $related_module->owner_id != auth_staff()->id
                ) {
                    $related_module->owner->user->notify(
                        new CrudNotification($case, $id, [
                            'name' => auth_staff()->name,
                            'id'   => auth_staff()->id,
                        ])
                    );
                }

                $follow = $related_module->fresh()->follow_status;
                $html   = $related_module->fresh()->followers_html;
                $count  = count($related_module->fresh()->sorted_followers);
                $status = true;
            } else {
                $errors[] = 'You are not allowed to follow the specified ' . $data['type'];
            }
        } else {
            $messages = $validation->getMessageBag()->toArray();

            foreach ($messages as $msg) {
                $errors[] = $msg;
            }
        }

        return response()->json([
            'status' => $status,
            'errors' => $errors,
            'follow' => $follow,
            'count'  => $count,
            'html'   => $html,
        ]);
    }

    /**
     * JSON format followers data.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     * @param int                      $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function followerData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::find($module_id);

        // If the module exists then get all followers data related to the module.
        if (isset($module)) {
            return Follower::getFollowersData($request, $module);
        }

        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Staff $staff)
    {
        $status   = true;
        $redirect = null;

        // Valid specified resource and user delete permission checker.
        if ($staff->id != $request->id || ! $staff->auth_can_delete) {
            $status = false;
        }

        if ($status == true) {
            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                if (isset($staff->next_record)) {
                    $redirect = route('admin.user.show', $staff->next_record->id);
                } elseif (isset($staff->prev_record)) {
                    $redirect = route('admin.user.show', $staff->prev_record->id);
                } else {
                    $redirect = route('admin.user.index');
                }
            }

            $staff->user->update(['status' => 0]);
            $staff->delete();
            $staff->user->delete();
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
        $status = true;
        $staffs = $request->staffs;

        // Count requested resource data checker,
        // only user permitted data will be deleted.
        if (isset($staffs) && count($staffs)) {
            foreach ($staffs as $staff_id) {
                $staff = Staff::find($staff_id);

                if (isset($staff) && $staff->auth_can_delete) {
                    $staff->user->update(['status' => 0]);
                    $staff->delete();
                    $staff->user->delete();
                }
            }
        } else {
            $status = false;
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Update bulk user status.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkStatus(Request $request)
    {
        $status      = true;
        $staffs      = $request->staffs;
        $bulk_status = $request->status;
        $bulk_status_array = ['active', 'inactive'];

        // Count requested resource data checker,
        // valid bulk status checker.
        if (isset($staffs)
            && count($staffs)
            && isset($bulk_status)
            && in_array($bulk_status, $bulk_status_array)
        ) {
            foreach ($staffs as $staff_id) {
                $staff = Staff::find($staff_id);

                // If this is a valid user,
                // the auth user is admin,
                // the specified user is not the super admin and not logged in.
                if (isset($staff) && auth_staff()->admin && ! $staff->super_admin && ! $staff->logged_in) {
                    $staff->user->update(['status' => $bulk_status == 'active' ? 1 : 0]);
                }
            }
        } else {
            $status = false;
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Send message.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function message(Request $request)
    {
        $status = true;
        $errors = null;
        $data   = $request->all();
        $rules  = [
            'receiver' => 'required|exists:users,linked_id,linked_id,!' . auth_staff()->id . ',linked_type,staff,' .
                          'status,1,deleted_at,NULL',
            'message'  => 'required|max:65535',
        ];

        $validation = validator($data, $rules);

        // Send a message if validation passes.
        if ($validation->passes()) {
            // Loop through all receivers, send messages according to sender and receiver chat room.
            foreach ($request->receiver as $receiver) {
                $sender_chat_rooms_id = auth_staff()->dedicated_chat_rooms_id;
                $receiver = (int) $receiver;

                // If the receiver is not equal to the auth user.
                if ($receiver != auth_staff()->id) {
                    $chat_room = ChatRoom::join(
                        'chat_room_members',
                        'chat_room_members.chat_room_id',
                        '=',
                        'chat_rooms.id'
                    )
                    ->whereIn('chat_rooms.id', $sender_chat_rooms_id)
                    ->whereLinked_type('staff')
                    ->whereLinked_id($receiver)
                    ->select('chat_rooms.*')
                    ->first();

                    // If the chat room is found.
                    if (isset($chat_room)) {
                        $sender_member_id = $chat_room->members->where('linked_id', auth_staff()->id)->first()->id;
                        $receiver_member_id = $chat_room->members->where('linked_id', $receiver)->first()->id;

                        $chat_sender = new ChatSender;
                        $chat_sender->message = $request->message;
                        $chat_sender->chat_room_member_id = $sender_member_id;
                        $chat_sender->save();

                        $chat_receiver = new ChatReceiver;
                        $chat_receiver->chat_sender_id = $chat_sender->id;
                        $chat_receiver->chat_room_member_id = $receiver_member_id;
                        $chat_receiver->save();
                    }
                }
            }
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
    }

    /**
     * Update the auth user's settings.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function setting(Request $request)
    {
        $status     = false;
        $errors     = [];
        $data       = $request->all();
        $validation = Staff::settingValidate($data);

        // Update the auth user's settings if validation passes.
        if ($validation->passes()) {
            auth_staff()->settingUpdate($data);
            $status = true;
        } else {
            $messages = $validation->getMessageBag()->toArray();

            foreach ($messages as $msg) {
                $errors[] = $msg;
            }
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
    }
}
