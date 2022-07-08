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

use Carbon\Carbon;
use App\Models\Traits\ChartTrait;
use App\Models\Traits\HistoryTrait;
use App\Models\Traits\ParentModuleTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends BaseModel
{
    use SoftDeletes;
    use ChartTrait;
    use HistoryTrait;
    use ParentModuleTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'staffs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'image', 'title', 'phone', 'date_of_birth', 'fax', 'website',
        'street', 'city', 'state', 'zip', 'country_code', 'signature', 'settings',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['name', 'email', 'status', 'closed_project_tasks_count', 'closed_project_issues_count'];

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
     * Don't keep history fields array list.
     *
     * @var array
     */
    protected $dontKeepRevisionOf = ['image', 'settings'];

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFields = [
        'role' => 'database:role|whereIn.id|display_name',
        'country_code' => 'helper:country_code_to_name',
    ];

    /**
     * Field custom display name.
     *
     * @var array
     */
    protected $revisionFormattedFieldNames = ['title' => 'Job Title', 'country_code' => 'Country'];

    /**
     * Fields list array where the index is field's name and corresponding value as field's display name.
     *
     * @var array
     */
    protected static $fieldlist = [
        'city'          => 'City',
        'country_code'  => 'Country',
        'date_of_birth' => 'Date of Birth',
        'email'         => 'Email',
        'fax'           => 'Fax',
        'first_name'    => 'First Name',
        'title'         => 'Job Title',
        'last_name'     => 'Last Name',
        'phone'         => 'Phone',
        'role'          => 'Role',
        'signature'     => 'Signature',
        'state'         => 'State',
        'street'        => 'Street',
        'website'       => 'Website',
        'zip'           => 'Zip Code',
    ];

    /**
     * Fields name array that can be mass updatable.
     *
     * @var array
     */
    protected static $mass_fieldlist = [
        'first_name', 'last_name', 'title', 'role', 'phone', 'date_of_birth',
        'fax', 'website', 'street', 'city', 'state', 'zip', 'country_code', 'signature',
    ];

    /**
     * Fields name array that uses to filter data.
     *
     * @var array
     */
    protected static $filter_fieldlist = [
        'first_name', 'last_name', 'title', 'role', 'email', 'phone',
        'fax', 'website', 'street', 'city', 'state', 'zip', 'country_code', 'signature',
    ];

    /**
     * User form validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        $unique_email = 'unique:users,email';
        $password     = 'required|min:6|max:60';
        $role         = 'required|exists:roles,id,deleted_at,NULL';

        if (isset($data['id']) && isset($data['user_id'])) {
            $unique_email .= ',' . $data['user_id'];
            $password      = 'min:6|max:60';

            if (auth_staff()->id == $data['id']) {
                $role = 'exists:roles,id,deleted_at,NULL';
            }
        }

        return validator($data, [
            'first_name' => 'max:200',
            'last_name'  => 'required|max:200',
            'email'      => 'required|email|' . $unique_email,
            'title'      => 'required|max:200',
            'phone'      => 'max:200',
            'role'       => $role,
            'password'   => $password,
        ]);
    }

    /**
     * Single field update validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function singleValidate($data)
    {
        $unique_email = array_key_exists('id', $data) ? "unique:users,email,{$data['id']}" : 'unique:users,email';
        $rules = [
            'first_name'    => 'max:200',
            'last_name'     => 'sometimes|required|max:200',
            'title'         => 'sometimes|required|max:200',
            'role'          => 'sometimes|required|exists:roles,id,deleted_at,NULL',
            'email'         => 'sometimes|required|email|' . $unique_email,
            'phone'         => 'max:200',
            'date_of_birth' => 'date',
            'fax'           => 'max:200',
            'website'       => 'max:200',
            'facebook'      => 'max:200',
            'twitter'       => 'max:200',
            'skype'         => 'max:200',
            'linkedin'      => 'max:200',
            'street'        => 'max:200',
            'city'          => 'max:200',
            'state'         => 'max:200',
            'zip'           => 'max:200',
            'country_code'  => 'in:' . valid_country_code(),
            'signature'     => 'max:65535',
        ];

        return validator($data, $rules);
    }

    /**
     * User filter data validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function filterValidate($data)
    {
        $fields = [
            'first_name', 'last_name', 'title', 'email', 'phone',
            'fax', 'website', 'street', 'city', 'state', 'zip', 'signature',
        ];

        $fields[] = [
            'name'      => 'role',
            'type'      => 'dropdown',
            'condition' => 'required|exists:roles,id,deleted_at,NULL',
        ];

        $fields[] = [
            'name'      => 'country_code',
            'type'      => 'dropdown',
            'condition' => 'required|array|in:' . valid_country_code(),
        ];

        $rules = FilterView::filterRulesGenerator($data, $fields);

        return validator($data, $rules);
    }

    /**
     * User settings validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function settingValidate($data)
    {
        $rules = ['key' => 'required|in:chat_sound'];

        if (array_key_exists('key', $data)) {
            if ($data['key'] == 'chat_sound') {
                $rules['value'] = 'required|in:on,off';
            }
        }

        return validator($data, $rules);
    }

    /**
     * Update user settings.
     *
     * @param array $data
     *
     * @return bool
     */
    public function settingUpdate($data)
    {
        if ($this->id == auth_staff()->id) {
            $setting_array = json_decode($this->settings, true);
            $setting_array[$data['key']] = $data['value'];
            $this->settings = json_encode($setting_array);
            $this->update();

            return true;
        }

        return false;
    }

    /**
     * Get user setting value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSettingVal($key)
    {
        if (! is_null($this->settings)) {
            $setting_array = json_decode($this->settings, true);

            if (array_key_exists($key, $setting_array)) {
                return $setting_array[$key];
            }
        }

        return null;
    }

    /**
     * Set resource route name.
     *
     * @return string
     */
    public function setRoute()
    {
        return 'user';
    }

    /**
     * Set resource permission name.
     *
     * @return string
     */
    public function setPermission()
    {
        return 'user';
    }

    /**
     * Set resource order type.
     *
     * @return string
     */
    public function setOrderType()
    {
        return 'asc';
    }

    /**
     * Get the specified resource readable call name.
     *
     * @return string
     */
    public function getIdentifierCallNameAttribute()
    {
        return ucfirst('user');
    }

    /**
     * Get show page tab array list.
     *
     * @param \App\Models\Staff|null $staff
     *
     * @return array
     */
    public static function informationTypes($staff = null)
    {
        $information_types = [
            'overview'       => 'Overview',
            'projects'       => 'Projects',
            'projectskanban' => ['display' => 'Projects Kanban', 'nav' => 0, 'parent' => 'projects'],
            'tasks'          => 'Tasks',
            'taskskanban'    => ['display' => 'Tasks Kanban', 'nav' => 0, 'parent' => 'tasks'],
            'milestones'     => 'Milestones',
            'issues'         => 'Issues',
            'issueskanban'   => ['display' => 'Issues Kanban', 'nav' => 0, 'parent' => 'issues'],
            'calendar'       => 'Calendar',
            'events'         => ['display' => 'Events', 'nav' => 0, 'parent' => 'calendar'],
            'notes'          => 'Notes',
            'files'          => 'Files',
            'history'        => 'History',
        ];

        // If the auth user doesn't have permission to view "Project" then remove the project tab.
        if (! permit('project.view')) {
            array_forget($information_types, 'projects');
        }

        // If the auth user doesn't have permission to view "Task" then remove the task tab.
        if (! permit('task.view')) {
            array_forget($information_types, 'tasks');
        }

        // If the auth user doesn't have permission to view "Milestone" then remove the milestone tab.
        if (! permit('milestone.view')) {
            array_forget($information_types, 'milestones');
        }

        // If the auth user doesn't have permission to view "Issue" then remove the issue tab.
        if (! permit('issue.view')) {
            array_forget($information_types, 'issues');
        }

        // If the auth user doesn't have permission to view "Event" then remove the event tab.
        if (! permit('event.view')) {
            array_forget($information_types, 'events');
        }

        // If the auth user doesn't have permission to view Milestone|Task|Issue|Event then remove the calendar tab.
        if (! (permit('milestone.view') || permit('task.view') || permit('issue.view') || permit('event.view'))) {
            array_forget($information_types, 'calendar');
        }

        // If the auth user doesn't have to view "Note" then remove the note tab.
        if (! permit('note.view')) {
            array_forget($information_types, 'notes');
        }

        // If the auth user doesn't have to view "Files" then remove the file tab.
        if (! permit('attachment.view')) {
            array_forget($information_types, 'files');
        }

        return $information_types;
    }

    /**
     * Get append users dropdown list.
     *
     * @param string                                   $field
     * @param int                                      $id
     * @param \Illuminate\Database\Eloquent\Model|null $obj
     *
     * @return array
     */
    public static function getAppendDropdownList($field, $id = null, $obj = null)
    {
        if (null_or_empty($id) && null_or_empty($obj)) {
            return self::orderBy('id')
                       ->get(['id', 'first_name', 'last_name'])
                       ->where('status', 1)
                       ->pluck('name', 'id')
                       ->toArray();
        }

        $dropdown = [];

        // For Project, the dropdown list divides into two parts project member and external users.
        if (strpos($field, 'project') !== false) {
            $project  = is_null($obj) ? Project::find($id) : $obj;
            $members  = collect($project->sorted_members)->pluck('name', 'id')->toArray();
            $dropdown = ['Project Members' => $members];
            $users    = Staff::orderBy('id')
                             ->whereNotIn('id', array_keys($members))
                             ->get(['id', 'first_name', 'last_name'])
                             ->where('status', 1)
                             ->pluck('name', 'id')
                             ->toArray();

            if (count($users)) {
                $dropdown['External Users'] = $users;
            }
        }

        return $dropdown;
    }

    /**
     * Get filter field formatted values array.
     *
     * @return array
     */
    public static function getFieldValueDropdownList()
    {
        $dropdown['country'] = countries_list();
        $dropdown['role']    = Role::onlyGeneral()
                                   ->orderBy('id')
                                   ->get(['id', 'display_name'])
                                   ->pluck('display_name', 'id')
                                   ->toArray();
        return $dropdown;
    }

    /**
     * Get resource data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        $columns = ['name', 'email', 'phone', 'last_login', 'status'];

        // Only admin users can be allowed for mass action checkbox select column.
        if (auth_staff()->admin) {
            $columns = array_prepend($columns, 'checkbox');
        }

        if (permit('user.edit')) {
            array_push($columns, 'action');
        }

        $table = [
            'thead'         => [
                ['name', 'style' => 'min-width: 280px'],
                'email', 'phone', 'last login',
                ['status', 'orderable' => 'false', 'data_class' => 'center'],
            ],
            'checkbox'      => auth_staff()->admin,
            'action'        => permit('user.edit'),
            'custom_filter' => true,
            'json_columns'  => \DataTable::jsonColumn($columns),
            'filter_input'  => [
                'status'    => ['type' => 'dropdown', 'options' => [1 => 'Active Users', 0 => 'Inactive Users']]
            ],
        ];

        return $table;
    }

    /**
     * Get resource table data.
     *
     * @param \App\Models\Staff        $staffs
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($staffs, $request)
    {
        return \DataTable::of($staffs)->addColumn('checkbox', function ($staff) {
            return $staff->checkbox_html;
        })->addColumn('name', function ($staff) {
            return $staff->name_html;
        })->addColumn('email', function ($staff) {
            return $staff->email;
        })->addColumn('phone', function ($staff) {
            return $staff->phone;
        })->addColumn('last_login', function ($staff) {
            return $staff->last_login_html;
        })->addColumn('status', function ($staff) {
            return $staff->status_html;
        })->addColumn('action', function ($staff) {
            // Logged in user can not delete on own.
            if ($staff->logged_in) {
                return $staff->getActionHtml('User', 'admin.user.destroy', null, [
                    'edit'   => permit('user.edit'),
                    'delete' => false,
                ]);
            }

            // Don't have edit permission of Super Admin or Admin by normal users.
            if ((auth_staff()->admin == false && $staff->admin == true) || $staff->super_admin == true) {
                return $staff->getActionHtml('User', 'admin.user.destroy', null);
            }

            return $staff->getActionHtml('User', 'admin.user.destroy', null, [
                'edit'   => permit('user.edit'),
                'delete' => permit('user.delete'),
            ]);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                $status = $row->globalSearch($request, ['name', 'title', 'email', 'phone']);

                if (! $request->has('status') && ! $row->status) {
                    $status = false;
                }

                if ($request->has('status') && $request->status != '' && $row->status != $request->status) {
                    $status = false;
                }

                return $status;
            });
        })->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * Query users by field, condition, and value.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $related_attribute
     * @param string                                $condition
     * @param mixed                                 $conditional_value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterViewQuery($query, $related_attribute, $condition, $conditional_value)
    {
        if (count_if_countable($query->getQuery()->joins) == 0) {
            $query = $query->join('users', 'staffs.id', '=', 'users.linked_id')
                           ->join('role_user', 'users.id', '=', 'role_user.user_id')
                           ->where('users.linked_type', 'staff');
        }

        if ($related_attribute == 'email') {
            $attribute = 'users.email';
        } elseif ($related_attribute == 'role') {
            $attribute = 'role_user.role_id';
        }

        return $query->conditionalFilterQuery($attribute, $condition, $conditional_value);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATOR
    |--------------------------------------------------------------------------
    */
    /**
     * Set the user's first name.
     *
     * @param string $value
     *
     * @return string
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = str_replace(["'", '"'], '', $value);
    }

    /**
     * Set the user's last name.
     *
     * @param string $value
     *
     * @return string
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = str_replace(["'", '"'], '', $value);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the user name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return trim($this->attributes['first_name'] . ' ' . $this->attributes['last_name']);
    }

    /**
     * Get the user's avatar.
     *
     * @param bool|null $photo
     *
     * @return string
     */
    public function getAvatarAttribute($photo = null)
    {
        if (isset($this->image) && file_exists(storage_path($this->image_path))) {
            return (string) \Image::make(storage_path($this->image_path))->encode('data-url');
        } elseif (isset($photo) && $photo == true) {
            return asset('img/avatar.png');
        } else {
            $name_array  = explode(' ', $this->name);
            $first_word  = array_first($name_array);
            $last_word   = end($name_array);
            $avatar_name = $first_word . ' ' . $last_word;

            return \Avatar::create($avatar_name)->toBase64();
        }
    }

    /**
     * Get the user image path.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        if (! is_null($this->image)) {
            return 'app/staffs/' . $this->image;
        }

        return null;
    }

    /**
     * Get user email.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    /**
     * Get user id.
     *
     * @return int
     */
    public function getUserIdAttribute()
    {
        return $this->user->id;
    }

    /**
     * Get user type with id.
     *
     * @return string
     */
    public function getIdTypeAttribute()
    {
        return 'staff-' . $this->id;
    }

    /**
     * Get to know user Super Admin status.
     *
     * @return bool
     */
    public function getSuperAdminAttribute()
    {
        $seniority = User::withRole('administrator')->orderBy('created_at')->orderBy('id')->first();

        if ($this->user->hasRole('administrator') && isset($seniority) && $this->user->id == $seniority->id) {
            return true;
        }

        return false;
    }

    /**
     * Get super admin user.
     *
     * @return \App\Models\User
     */
    public static function superAdmin()
    {
        $super_admin = User::onlyStaff()->withRole('administrator')->orderBy('created_at')->orderBy('id')->first();

        if (isset($super_admin)) {
            return $super_admin;
        }

        return null;
    }

    /**
     * Get to know user admin status.
     *
     * @return bool
     */
    public function getAdminAttribute()
    {
        if ($this->user->hasRole('administrator')) {
            return true;
        }

        return false;
    }

    /**
     * Get to know the user logged-in status.
     *
     * @return bool
     */
    public function getLoggedInAttribute()
    {
        if (auth()->user()->id == $this->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get user roles array list.
     *
     * @return array
     */
    public function getRolesListAttribute()
    {
        return $this->user->roles->pluck('id')->toArray();
    }

    /**
     * Get user roles name array list.
     *
     * @return array
     */
    public function getRolesNameListAttribute()
    {
        return $this->user->roles->pluck('display_name')->toArray();
    }

    /**
     * Get to know the user can edit 'role' or not.
     *
     * @return bool
     */
    public function getEditRoleAttribute()
    {
        // If the auth user is not an admin
        // Or the specified user is logged in
        // Or the auth user doesn't have permission to edit the specified user role.
        if (! auth_staff()->admin || $this->logged_in || ! $this->follow_command_rule) {
            return false;
        }

        return true;
    }

    /**
     * Get to know the user can edit email or not.
     *
     * @return bool
     */
    public function getEditEmailAttribute()
    {
        // If the auth user is not an admin and the specified user is not logged in
        // Or the auth user doesn't have permission to edit the specified user.
        if ((! auth_staff()->admin && ! $this->logged_in) || ! $this->follow_command_rule) {
            return false;
        }

        return true;
    }

    /**
     * Get to know the auth user can view data.
     *
     * @return bool
     */
    public function getAuthCanViewAttribute()
    {
        return permit('user.view');
    }

    /**
     * Get to know the auth user can edit resource status.
     *
     * @return bool
     */
    public function getAuthCanEditAttribute()
    {
        return permit('user.edit');
    }

    /**
     * Get to know the auth user can edit the specified user credentials status.
     *
     * @return bool
     */
    public function getEditCredentialAttribute()
    {
        // If the auth user is an admin or the specified user is logged in
        // And the specified user is not Super Admin or the auth user is Super Admin.
        if ((auth_staff()->admin || $this->logged_in) && (! $this->super_admin || auth_staff()->super_admin)) {
            return true;
        }

        return false;
    }

    /**
     * Get to know the auth user can edit the specified user's password or not.
     *
     * @return bool
     */
    public function getAuthCanEditPasswordAttribute()
    {
        if (auth_staff()->super_admin) {
            return true;
        }

        if ($this->super_admin) {
            return false;
        }

        if (auth_staff()->admin || $this->logged_in) {
            return true;
        }

        return false;
    }

    /**
     * Get to know the auth user can delete the specified resource or not.
     *
     * @return bool
     */
    public function getAuthCanDeleteAttribute()
    {
        if ($this->logged_in) {
            return false;
        }

        // If the auth user is not Admin and the specified user is admin
        // Or the specified user is Super Admin.
        if ((! auth_staff()->admin && $this->admin) || $this->super_admin) {
            return false;
        }

        return permit('user.delete');
    }

    /**
     * Get to know the auth user can edit the specified user status.
     *
     * @return bool
     */
    public function getEditStatusAttribute()
    {
        // The auth user is not Admin or the specified user is Super Admin or the specified user is logged in.
        if (! auth_staff()->admin || $this->super_admin || $this->logged_in) {
            return false;
        }

        return true;
    }

    /**
     * Get follow command rule status for the auth user.
     *
     * @return bool
     */
    public function getFollowCommandRuleAttribute()
    {
        if ($this->logged_in) {
            return true;
        }

        // The auth user is not an admin and the specified user is admin
        // Or the specified user is Super Admin
        // Or the auth user does not have user edit permission.
        if ((! auth_staff()->admin && $this->admin) || $this->super_admin || ! permit('user.edit')) {
            return false;
        }

        return true;
    }

    /**
     * Get user admin HTML status.
     *
     * @return string
     */
    public function getAdminHtmlAttribute()
    {
        $admin = '';

        if ($this->super_admin) {
            $admin = "<span class='btn btn-primary status ml10'>Super Admin</span> ";
        } elseif ($this->admin == true && $this->super_admin == false) {
            $admin = "<span class='btn btn-warning status ml10'>Administrator</span> ";
        }

        return $admin;
    }

    /**
     * Get user avatar HTML.
     *
     * @return string
     */
    public function getAvatarHtmlAttribute()
    {
        return "<a href='" . route('admin.user.show', $this->id) . "' class='avatar-link' data-toggle='tooltip'
                   data-placement='top' title='{$this->name}'><img src='{$this->avatar}'>
                </a>";
    }

    /**
     * Get user avatar according to the related project.
     *
     * @return string
     */
    public function getProjectAvatarHtmlAttribute()
    {
        $crown   = '';
        $tooltip = $this->name;

        if ($this->project_admin) {
            $crown = '<i class="crown mdi mdi-trophy-award"></i>';
            $tooltip = fill_up_space('Owner : ' . $this->name);
        }

        return "<a href='" . route('admin.user.show', $this->id) . "' class='avatar-link' data-toggle='tooltip'
                   data-placement='top' title='{$tooltip}'><img src='{$this->avatar}'>{$crown}
                </a>";
    }

    /**
     * Get the specified resource name HTML.
     *
     * @return string
     */
    public function getNameHtmlAttribute()
    {
        $admin   = $this->admin_html;
        $tooltip = strlen($this->name) > 17 ? "data-toggle='tooltip' data-placement='top' title='{$this->name}'" : '';

        return "<a href='" . route('admin.user.show', $this->id) . "' class='profile-link lg'>
                    <img src='{$this->avatar}'>
                    <p>
                        <span class='user-name' $tooltip>" . str_limit($this->name, 17, '.') . "</span>
                        {$admin}<br><span class='shadow inline-block max-overflow-ellipsis'>{$this->title}</span>
                    </p>
                </a>";
    }

    /**
     * Get the user profile HTML.
     *
     * @param string|null $status
     * @param bool|null   $only_avatar
     *
     * @return string
     */
    public function getProfileHtmlAttribute($status = null, $only_avatar = null)
    {
        $name_css = '';
        $tooltip  = '';
        $css      = 'only-avt';

        if (strlen($this->name) > 20) {
            $tooltip  = "data-toggle='tooltip' data-placement='top' title='{$this->name}'";
            $name_css = 'mt0-imp';
        }

        $img  = "<img src='{$this->avatar}' data-toggle='tooltip' data-placement='left' title='" . fill_up_space($this->name) . "'>";
        $text = '';

        if ($only_avatar == null) {
            $css  = is_null($status) ? '' : 'md';
            $img  = "<img src='{$this->avatar}'>";
            $text = "<p class='{$name_css}'><span $tooltip>" . str_limit($this->name, 20, '.') . "</span>$status</p>";
        }

        return "<a href='{$this->show_route}' class='profile-link sm {$css}'>" . $img . $text . '</a>';
    }

    /**
     * Get the user plain profile.
     *
     * @return string
     */
    public function getProfilePlainHtmlAttribute()
    {
        $tooltip  = strlen($this->name) > 17 ? "title='{$this->name}'" : '';
        $inactive = $this->user->status ? '' : "<span class='status'>Deactivated</span>";

        return "<a href='{$this->show_route}'>
                    <img src='{$this->avatar}'>
                    <p>
                        <span class='user-name' $tooltip>" . str_limit($this->name, 17, '.') . "</span>
                        <br><span class='shadow'>{$this->title}</span>
                    </p>
                </a>" . $inactive;
    }

    /**
     * Get the user profile details HTML.
     *
     * @return string
     */
    public function getProfileRenderAttribute()
    {
        $name_tooltip  = strlen($this->name) > 50 ? "data-toggle='tooltip' data-placement='top' title='{$this->name}'" : '';
        $title_tooltip = strlen($this->title) > 50 ? "data-toggle='tooltip' data-placement='top' title='{$this->title}'" : '';
        $email_tooltip = strlen($this->email) > 50 ? "data-toggle='tooltip' data-placement='top' title='{$this->email}'" : '';

        return "<span class='profile'>
                    <img src='" . $this->avatar . "'>
                    <span class='info'>
                        <span class='focus' $name_tooltip>" . str_limit($this->name, 50, '.') . "</span>
                        <br>
                        <span class='shadow' $title_tooltip>" . str_limit($this->title, 50, '.') . "</span>
                        <br>
                        <span class='shadow' $email_tooltip>" . str_limit($this->email, 50, '.') . "</span>
                    </span>
                </span>";
    }

    /**
     * Get the user's last login HTML.
     *
     * @param string|null $tooltip_position
     *
     * @return string
     */
    public function getLastLoginHtmlAttribute($tooltip_position = null)
    {
        $outcome = 'Never';
        $last_login = $this->user->last_login;
        $tooltip_position = ! is_null($tooltip_position) ? $tooltip_position : 'top';

        if ($last_login) {
            $outcome = "<span data-toggle='tooltip' data-placement='{$tooltip_position}'
                            title='{$this->readableDateAmPm('last_login')}'>" .
                            time_short_form($last_login->diffForHumans()) .
                       "</span>";
        }

        return $outcome;
    }

    /**
     * Get the user's last login time.
     *
     * @return string
     */
    public function getLastLoginAttribute()
    {
        return $this->user->last_login;
    }

    /**
     * Get the user's online status.
     *
     * @return bool
     */
    public function getIsOnlineAttribute()
    {
        return $this->user->isOnline();
    }

    /**
     * Get checkbox HTML for mass action select item.
     *
     * @param string    $css
     * @param bool|null $disabled
     *
     * @return string
     */
    public function getCheckboxHtmlAttribute($css = null, $disabled = null)
    {
        if (! auth_staff()->admin || $this->super_admin || $this->logged_in) {
            $disabled = 'disabled';
        }

        return "<div class='pretty info smooth'>
                    <input class='single-row' type='checkbox' name='{$this->table}[]' value='{$this->id}' $disabled>
                    <label><i class='mdi mdi-check'></i></label>
                </div>";
    }

    /**
     * Get the user status.
     *
     * @return bool
     */
    public function getStatusAttribute()
    {
        return (int) $this->user->status;
    }

    /**
     * Get the user status HTML.
     *
     * @param string|null $tooltip_position
     *
     * @return string
     */
    public function getStatusHtmlAttribute($tooltip_position = null)
    {
        $disabled = '';

        // If the auth user is not an admin or the specified user is Super Admin or logged in.
        if (! auth_staff()->admin || $this->super_admin || $this->logged_in) {
            $disabled = 'disabled';
        }

        $tooltip_position = isset($tooltip_position) ? $tooltip_position : 'top';
        $status = "<label class='switch user-status {$disabled}' data-placement='{$tooltip_position}'
                        data-toggle='tooltip' title='Inactive'><input type='checkbox' value='{$this->id}' {$disabled}>
                        <span class='slider round'></span>
                   </label>";

        if ($this->user->status) {
            $status = "<label class='switch user-status {$disabled}' data-placement='{$tooltip_position}'
                            data-toggle='tooltip' title='Active'>
                            <input type='checkbox' value='{$this->id}' checked {$disabled}>
                            <span class='slider round'></span>
                       </label>";
        }

        return $status;
    }

    /**
     * Get show page breadcrumb HTML.
     *
     * @return string
     */
    public function getShowPageBreadcrumbAttribute()
    {
        return breadcrumb("admin.user.index:Users|<span data-realtime='first_name'>{$this->name}</span>");
    }

    /**
     * Get the specified resource all actions HTML.
     *
     * @param string      $item
     * @param string      $delete_route
     * @param string|null $edit_route
     * @param array       $action_permission
     * @param bool        $common_modal
     *
     * @return string
     */
    public function getActionHtml(
        $item,
        $delete_route,
        $edit_route = null,
        $action_permission = [],
        $common_modal = false
    ) {
        $edit = '';
        $list = '';

        if (isset($action_permission['edit']) && $action_permission['edit'] == true) {
            $edit     = "<div class='inline-action'>";
            $edit_btn = "<a class='edit' editid='{$this->id}'><i class='fa fa-pencil'></i></a>";

            if ($edit_route != null) {
                $edit_btn = "<a href='" . route($edit_route, $this->id) . "'><i class='fa fa-pencil'></i></a>";
            }

            $edit .= $edit_btn . '</div>';

            // If the auth user is admin or the specified user is logged in.
            if (auth_staff()->admin || $this->logged_in) {
                $list .= "<li>
                            <a class='add-multiple' data-item='user' data-content='user.partials.modal-password'
                               data-action='" . route('admin.user.password', $this->id) . "' data-modalsize='tiny'
                               data-default='id:{$this->id}' save-new='false' modal-title='Change Password'
                               modal-sub-title='{$this->name}'><i class='mdi mdi-lock-open-outline'></i> Change Password
                            </a>
                          </li>";
            }
        }

        $complete_dropdown_menu = '';

        if (isset($action_permission['delete']) && $action_permission['delete'] == true) {
            $list .= "<li>" .
                        \Form::open(['route' => [$delete_route, $this->id], 'method' => 'delete']) .
                            \Form::hidden('id', $this->id) .
                            "<button type='submit' class='delete'><i class='mdi mdi-delete'></i> Delete</button>" .
                        \Form::close() .
                     '</li>';
        }

        if (isset($list) && $list != '') {
            $complete_dropdown_menu = "<ul class='dropdown-menu'>{$list}</ul>";
        }

        $toggle = 'dropdown';
        $toggle_class = '';
        $tooltip = '';

        if (empty($edit) && empty($complete_dropdown_menu)) {
            $toggle = '';
            $toggle_class = 'disable';
            $tooltip = "data-toggle='tooltip' data-placement='left' title='" . fill_up_space('Permission denied') . "'";
        }

        if (! empty($edit) && empty($complete_dropdown_menu)) {
            $toggle_class = 'inactive';
        }

        $open     = "<div class='action-box $toggle_class' $tooltip>";
        $dropdown = "<div class='dropdown'>
                        <a class='dropdown-toggle $toggle_class' data-toggle='{$toggle}'>
                            <i class='fa fa-ellipsis-v'></i>
                        </a>";
        $close    = '</div></div>';

        $action = $open . $edit . $dropdown . $complete_dropdown_menu . $close;

        return $action;
    }

    /**
     * Get to know the user has sent messages recently.
     *
     * @return bool
     */
    public function getHasRecentSentMsgAttribute()
    {
        // If the auth user sent messages within 1 min 40 sec from now.
        $now       = now()->format('Y-m-d H:i:s');
        $back_sec  = now()->subSeconds(100)->format('Y-m-d H:i:s');
        $msg_count = ChatSender::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_senders.chat_room_member_id'
        )
        ->where('chat_room_members.linked_type', 'staff')
        ->where('chat_room_members.linked_id', $this->id)
        ->where('chat_senders.created_at', '>', $back_sec)
        ->where('chat_senders.created_at', '<', $now)
        ->count();

        return $msg_count ? true : false;
    }

    /**
     * Get the user notifications.
     *
     * @return \Illuminate\Notifications\DatabaseNotificationCollection
     */
    public function getNotificationsAttribute()
    {
        return $this->user->notifications;
    }

    /**
     * Get the user unread notifications.
     *
     * @return \Illuminate\Notifications\DatabaseNotificationCollection
     */
    public function getUnreadNotificationsAttribute()
    {
        return $this->user->unreadNotifications;
    }

    /**
     * Get the user unread notifications count.
     *
     * @return int
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->unread_notifications->count();
    }

    /**
     * Get the user unread notifications ids array.
     *
     * @return array
     */
    public function getUnreadNotificationsIdAttribute()
    {
        return $this->unread_notifications->pluck('id')->toArray();
    }

    /**
     * Get to know the user has new notification(s).
     *
     * @return bool
     */
    public function getHasNewNotificationAttribute()
    {
        $outcome = false;

        if (auth_staff()->unread_notifications_count || auth_staff()->unread_messages_count) {
            $outcome = true;
        }

        if ((session()->has('unread_notifications_id')
            && session('unread_notifications_id') == auth_staff()->unread_notifications_id)
            && (session()->has('unread_messages_id')
            && session('unread_messages_id') == auth_staff()->unread_messages_id)
        ) {
            $outcome = false;
        }

        session(['unread_notifications_id' => auth_staff()->unread_notifications_id]);
        session(['unread_messages_id' => auth_staff()->unread_messages_id]);

        return $outcome;
    }

    /**
     * Get the user unread message count.
     *
     * @return int
     */
    public function getUnreadMessagesCountAttribute()
    {
        return ChatReceiver::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_receivers.chat_room_member_id'
        )
        ->where('chat_room_members.linked_type', 'staff')
        ->where('chat_room_members.linked_id', $this->id)
        ->whereNull('chat_receivers.read_at')
        ->count();
    }

    /**
     * Get the user unread message-ids array.
     *
     * @return array
     */
    public function getUnreadMessagesIdAttribute()
    {
        return ChatReceiver::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_receivers.chat_room_member_id'
        )
        ->where('chat_room_members.linked_type', 'staff')
        ->where('chat_room_members.linked_id', $this->id)
        ->whereNull('chat_receivers.read_at')
        ->pluck('chat_receivers.id')
        ->toArray();
    }

    /**
     * Get the user received messages.
     *
     * @param int|null $take
     *
     * @return \App\Models\ChatReceiver
     */
    public function getReceivedMessagesAttribute($take = null)
    {
        $messages = ChatReceiver::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_receivers.chat_room_member_id'
        )
        ->where('chat_room_members.linked_type', 'staff')
        ->where('chat_room_members.linked_id', $this->id)
        ->groupBy('chat_sender_id')
        ->latest('chat_receivers.id')
        ->select('chat_receivers.*', 'chat_room_members.chat_room_id');

        return is_null($take) ? $messages->get() : $messages->take($take)->get();
    }

    /**
     * Get online chat room ids array associated with the user.
     *
     * @return array
     */
    public function getOnlineChatroomIdsAttribute()
    {
        return $this->chat_rooms->where('is_online', true)->pluck('id')->toArray();
    }

    /**
     * Get chat room ids array associated with the user.
     *
     * @return array
     */
    public function getChatRoomsIdAttribute()
    {
        return ChatRoom::join('chat_room_members', 'chat_room_members.chat_room_id', '=', 'chat_rooms.id')
                       ->whereLinked_type('staff')
                       ->whereLinked_id($this->id)
                       ->groupBy('chat_rooms.id')
                       ->orderBy('chat_rooms.id')
                       ->pluck('chat_rooms.id')
                       ->toArray();
    }

    /**
     * Get dedicated chat room ids array associated with the user.
     *
     * @return array
     */
    public function getDedicatedChatRoomsIdAttribute()
    {
        return ChatRoom::join('chat_room_members', 'chat_room_members.chat_room_id', '=', 'chat_rooms.id')
                       ->whereType('dedicated')
                       ->whereLinked_type('staff')
                       ->whereLinked_id($this->id)
                       ->groupBy('chat_rooms.id')
                       ->pluck('chat_rooms.id')
                       ->toArray();
    }

    /**
     * Get chat rooms associated with the user.
     *
     * @param int|null $take
     *
     * @return \App\Models\ChatRoom
     */
    public function getChatRoomsAttribute($take = null)
    {
        $room = ChatRoom::join('chat_room_members', 'chat_room_members.chat_room_id', '=', 'chat_rooms.id')
                        ->leftJoin('chat_senders', 'chat_senders.chat_room_member_id', '=', 'chat_room_members.id')
                        ->whereIn('chat_rooms.id', $this->chat_rooms_id)
                        ->latest('chat_senders.updated_at')
                        ->latest('chat_senders.id')
                        ->select(
                            'chat_rooms.id',
                            'chat_rooms.name',
                            'chat_rooms.type',
                            'chat_room_members.linked_id',
                            'chat_room_members.linked_type',
                            'chat_senders.created_at',
                            'chat_rooms.created_at as room_created_at',
                            'chat_senders.message'
                        )->get()->unique('id');

        return is_null($take) ? $room : $room->take($take);
    }

    /**
     * Get the latest chat room id.
     *
     * @return int
     */
    public function getLatestChatIdAttribute()
    {
        $latest_chat_room = $this->chat_rooms->where('linked_type', 'staff')->where('linked_id', $this->id)->first();

        return is_null($latest_chat_room) ? $this->chat_rooms->first()->id : $latest_chat_room->id;
    }

    /**
     * Get the user's social data.
     *
     * @param string|null $media
     *
     * @return array|null
     */
    public function getSocialDataAttribute($media = null)
    {
        $data   = null;
        $social = is_null($media) ? $this->socialmedia->first() : $this->socialmedia->where('media', $media)->first();

        if (! is_null($social)) {
            $data = json_decode($social->data);
        }

        return $data;
    }

    /**
     * Get the social link of a media associated with the user.
     *
     * @param string $media
     * @param string $media_url
     *
     * @return string
     */
    public function getSocialLinkAttribute($media, $media_url = null)
    {
        $outcome = is_null($media_url) ? 'https://www.' . $media . '.com/' : $media_url;
        $link    = non_property_checker($this->getSocialDataAttribute($media), 'link');

        if (isset($link)) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $outcome = $link;
            } else {
                $outcome = $outcome . $link;
            }
        }

        return $outcome;
    }

    /**
     * Get the user projects where user can be owner or member.
     *
     * @param string $sort_type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelateProjectsAttribute($sort_type = 'desc')
    {
        return $this->ownprojects->merge($this->projects)->unique('id')->sortBy('id', $sort_type);
    }

    /**
     * Get the user total closed tasks count by dashboard filter param.
     *
     * @return int
     */
    public function getClosedTasksFilterCountAttribute()
    {
        return $this->tasks()->withinPeriod(null, null, true)->onlyClosed()->count();
    }

    /**
     * Get the user total closed issues count by dashboard filter param.
     *
     * @return int
     */
    public function getClosedIssuesFilterCountAttribute()
    {
        return $this->issues()->withinPeriod(null, null, true)->onlyClosed()->count();
    }

    /**
     * Get top user list according to closed tasks|issues performance.
     *
     * @param string      $type
     * @param array|owner $owner
     * @param int         $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTopList($type, $owner = null, $limit = 5)
    {
        if (in_array($type, ['task', 'issue'])) {
            $whereClause    = ! is_null($owner) ? 'whereIn' : 'whereNot';
            $type_attribute = 'closed_' . $type . 's_filter_count';

            return self::orderBy('id')
                       ->$whereClause('id', $owner)
                       ->get()
                       ->sortByDesc($type_attribute)
                       ->take($limit)
                       ->filter(function ($staff) use ($type_attribute) {
                            return $staff->$type_attribute > 0;
                       });
        }

        return collect();
    }

    /**
     * Get to know the user is the project owner or not.
     *
     * @return bool
     */
    public function getProjectAdminAttribute()
    {
        $project_id = non_property_checker($this->pivot, 'project_id');

        if (! is_null($project_id) && $this->pivot['table'] == 'project_member') {
            return ($this->pivot['parent']->project_owner == $this->id);
        }

        return false;
    }

    /**
     * Get to know the user is a project member and the auth user.
     *
     * @return bool
     */
    public function getAuthMemberAttribute()
    {
        $project_id = non_property_checker($this->pivot, 'project_id');

        if (! is_null($project_id) && $this->pivot['table'] == 'project_member') {
            $member_ids = $this->pivot['parent']->members->pluck('id')->toArray();

            return in_array(auth_staff()->id, $member_ids) && $this->id == auth_staff()->id;
        }

        return false;
    }

    /**
     * Get the user project member HTML.
     *
     * @return string
     */
    public function getProjectMemberHtmlAttribute()
    {
        $status = null;

        if ($this->project_admin) {
            $status = "<span class='btn btn-primary status ml10'>Owner</span>";
        }

        return $this->getProfileHtmlAttribute($status);
    }

    /**
     * Get the user project tasks.
     *
     * @param string|null $status
     *
     * @return \App\Models\Task
     */
    public function getProjectTasksAttribute($status = null)
    {
        $project_id = non_property_checker($this->pivot, 'project_id');

        if (! is_null($project_id) && $this->pivot['table'] == 'project_member') {
            $tasks = $this->tasks()->where('linked_type', 'project')->where('linked_id', $project_id);

            if ($status == 'open') {
                $tasks = $tasks->onlyOpen()->get();
            } elseif ($status == 'closed') {
                $tasks = $tasks->onlyClosed()->get();
            } else {
                $tasks = $tasks->get();
            }

            return $tasks;
        }

        return [];
    }

    /**
     * Get the user total no of project tasks.
     *
     * @return int
     */
    public function getProjectTasksCountAttribute()
    {
        return count($this->project_tasks);
    }

    /**
     * Get the user total closed project tasks count.
     *
     * @return int
     */
    public function getClosedProjectTasksCountAttribute()
    {
        return count($this->getProjectTasksAttribute('closed'));
    }

    /**
     * Get the user total open project tasks count.
     *
     * @return int
     */
    public function getOpenProjectTasksCountAttribute()
    {
        return count($this->getProjectTasksAttribute('open'));
    }

    /**
     * Get the user project task completion percentage.
     *
     * @return int
     */
    public function getProjectTasksCompletionPercentageAttribute()
    {
        $percentage = -1;

        if ($this->project_tasks_count > 0) {
            $percentage = floor($this->closed_project_tasks_count / $this->project_tasks_count * 100);
        }

        return $percentage;
    }

    /**
     * Get the user project tasks progress bar.
     *
     * @return string
     */
    public function getProjectTasksBarAttribute()
    {
        return \HtmlElement::renderProgressBar(
            $this->project_tasks_completion_percentage,
            'Task',
            $this->project_tasks_count,
            $this->closed_project_tasks_count,
            $this->open_project_tasks_count
        );
    }

    /**
     * Get the user project issues.
     *
     * @param string|null $status
     *
     * @return \App\Models\Issue
     */
    public function getProjectIssuesAttribute($status = null)
    {
        $project_id = non_property_checker($this->pivot, 'project_id');

        if (! is_null($project_id) && $this->pivot['table'] == 'project_member') {
            $issues = $this->issues()->where('linked_type', 'project')->where('linked_id', $project_id);

            if ($status == 'open') {
                $issues = $issues->onlyOpen()->get();
            } elseif ($status == 'closed') {
                $issues = $issues->onlyClosed()->get();
            } else {
                $issues = $issues->get();
            }

            return $issues;
        }

        return [];
    }

    /**
     * Get the user total no of project issues.
     *
     * @return int
     */
    public function getProjectIssuesCountAttribute()
    {
        return count($this->project_issues);
    }

    /**
     * Get the user total closed project issues count.
     *
     * @return int
     */
    public function getClosedProjectIssuesCountAttribute()
    {
        return count($this->getProjectIssuesAttribute('closed'));
    }

    /**
     * Get the user total open project issues count.
     *
     * @return int
     */
    public function getOpenProjectIssuesCountAttribute()
    {
        return count($this->getProjectIssuesAttribute('open'));
    }

    /**
     * Get the user project issues completion percentage.
     *
     * @return int
     */
    public function getProjectIssuesCompletionPercentageAttribute()
    {
        $percentage = -1;

        if ($this->project_issues_count > 0) {
            $percentage = floor($this->closed_project_issues_count / $this->project_issues_count * 100);
        }

        return $percentage;
    }

    /**
     * Get the user project issues progress bar.
     *
     * @return string
     */
    public function getProjectIssuesBarAttribute()
    {
        return \HtmlElement::renderProgressBar(
            $this->project_issues_completion_percentage,
            'Issue',
            $this->project_issues_count,
            $this->closed_project_issues_count,
            $this->open_project_issues_count
        );
    }

    /**
     * Get the user project member's actions HTML.
     *
     * @param string      $item
     * @param string      $delete_route
     * @param string|null $edit_route
     * @param array       $action_permission
     * @param bool        $common_modal
     *
     * @return string|null
     */
    public function getMemberActionHtml(
        $item,
        $delete_route,
        $edit_route = null,
        $action_permission = [],
        $common_modal = false
    ) {
        $project_id = non_property_checker($this->pivot, 'project_id');

        if (! is_null($project_id) && $this->pivot['table'] == 'project_member') {
            $edit = '';
            $list = '';

            if (isset($action_permission['edit'])) {
                $edit = "<div class='inline-action'>";
                $icon = 'fa fa-cog';
                $text = 'Edit';
                $view_attributes = '';

                if (! $action_permission['edit']) {
                    $icon = 'fa fa-eye';
                    $text = 'View';
                    $view_attributes = "save-hide='true' cancel-txt='Close'";
                }

                $edit .= "<a class='common-edit-btn' data-item='member' modal-small='medium' data-toggle='tooltip'
                            title='" . fill_up_space($text . ' Permissions') . "' editid='{$this->pivot->id}'
                            data-url='" . route('admin.member.edit', [$this->pivot->project_id, $this->id]) . "'
                            data-posturl='" . route('admin.member.update', [$this->pivot->project_id, $this->id]) . "'
                            modal-title='{$this->name}' modal-sub-title='{$this->pivot['parent']->name}'
                            {$view_attributes}><i class='{$icon}'></i>
                         </a></div>";
            }

            $complete_dropdown_menu = '';

            if (isset($action_permission['delete']) && $action_permission['delete'] == true) {
                $list .= "<li>" .
                            \Form::open([
                                'route'  => [$delete_route, $this->pivot->project_id, $this->id],
                                'method' => 'delete',
                            ]) .
                            \Form::hidden('id', $this->pivot->id) .
                            "<button type='submit' data-item='member' data-parentitem='project' class='delete'>
                                <i class='mdi mdi-delete'></i> Remove
                            </button>" .
                            \Form::close() .
                         '</li>';
            }

            if (isset($list) && $list != '') {
                $complete_dropdown_menu = "<ul class='dropdown-menu'>{$list}</ul>";
            }

            $toggle = 'dropdown';
            $toggle_class = '';
            $tooltip = '';

            if (empty($edit) && empty($complete_dropdown_menu)) {
                $toggle = '';
                $toggle_class = 'disable';
                $tooltip = "data-toggle='tooltip' data-placement='left' title='" . fill_up_space('Permission denied') . "'";
            }

            if (! empty($edit) && empty($complete_dropdown_menu)) {
                $toggle_class = 'inactive';
            }

            $open     = "<div class='action-box $toggle_class' $tooltip>";
            $dropdown = "<div class='dropdown'>
                            <a class='dropdown-toggle $toggle_class' data-toggle='{$toggle}'>
                                <i class='fa fa-ellipsis-v'></i>
                            </a>";
            $close    = '</div></div>';
            $action   = $open . $edit . $dropdown . $complete_dropdown_menu . $close;

            return $action;
        }

        return null;
    }

    /**
     * Get the initial route of the auth user.
     *
     * @return string
     */
    public function getInitialRouteAttribute()
    {
        $init_modules_routes = [
            'module.dashboard'        => ['module.dashboard' => 'admin.dashboard.index'],
            'module.project'          => ['project.view' => 'admin.project.index'],
            'module.task'             => ['task.view' => 'admin.task.index'],
            'module.issue'            => ['issue.view' => 'admin.issue.index'],
            'module.event'            => ['event.view' => 'admin.event.calendar'],
            'module.user'             => ['user.view' => 'admin.user.index'],
            'module.settings'         => [
                'settings.general'    => 'admin.administration-setting.general',
                'settings.email'      => 'admin.administration-setting.email',
            ],
            'module.custom_dropdowns' => [
                'custom_dropdowns.project_status.view' => 'admin.administration-dropdown-projectstatus.index',
                'custom_dropdowns.task_status.view'    => 'admin.administration-dropdown-taskstatus.index',
                'custom_dropdowns.issue_status.view'   => 'admin.administration-dropdown-issuestatus.index',
                'custom_dropdowns.issue_type.view'     => 'admin.administration-dropdown-issuetype.index',
            ],
            'module.role'             => ['role.view' => 'admin.role.index'],
        ];

        $initial_route = null;

        foreach ($init_modules_routes as $module => $permissions_routes) {
            if (permit($module)) {
                foreach ($permissions_routes as $permission => $route) {
                    if (permit($permission)) {
                        $initial_route = $route;

                        break 2;
                    }
                }
            }
        }

        return $initial_route;
    }

    /**
     * Get the initial sub nav route of the auth user.
     *
     * @param string $route
     * @param bool   $link
     *
     * @return string|null
     */
    public function getInitSubRoute($route, $link = true)
    {
        $route_permission_map = [
            'settings'           => [
                'general'        => [
                    'permission' => 'settings.general',
                    'route'      => 'admin.administration-setting.general',
                ],
                'email'          => [
                    'permission' => 'settings.email',
                    'route'      => 'admin.administration-setting.email',
                ],
            ],
            'custom_dropdowns'   => [
                'project_status' => [
                    'permission' => 'custom_dropdowns.project_status.view',
                    'route'      => 'admin.administration-dropdown-projectstatus.index',
                ],
                'task_status'    => [
                    'permission' => 'custom_dropdowns.task_status.view',
                    'route'      => 'admin.administration-dropdown-taskstatus.index',
                ],
                'issue_status'   => [
                    'permission' => 'custom_dropdowns.issue_status.view',
                    'route'      => 'admin.administration-dropdown-issuestatus.index',
                ],
                'issue_type'     => [
                    'permission' => 'custom_dropdowns.issue_type.view',
                    'route'      => 'admin.administration-dropdown-issuetype.index',
                ],
            ],
        ];

        if (array_key_exists($route, $route_permission_map)) {
            foreach ($route_permission_map[$route] as $route_permission) {
                if (permit($route_permission['permission'])) {
                    return $link ? route($route_permission['route']) : $route_permission['route'];
                }
            }
        }

        return null;
    }

    /**
     * Get all history ids associated with the user.
     *
     * @return array
     */
    public function getHistoryIdsAttribute()
    {
        $staff_history_ids = $this->histories()->latest('id')->pluck('id')->toArray();
        $user_history_ids  = $this->user->histories()->latest('id')->pluck('id')->toArray();
        $staff_history_ids = push_flatten($staff_history_ids, $user_history_ids);
        $history_by_me_ids = $this->user->revisions()->onlyValidType()->latest('id')->pluck('id')->toArray();
        $staff_history_ids = push_flatten($staff_history_ids, $history_by_me_ids);

        return $staff_history_ids;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A many-to-many relationship with Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_member')->withTimestamps()->groupBy('projects.id');
    }

    /**
     * A many-to-many relationship with FilterView.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function views()
    {
        return $this->belongsToMany(FilterView::class, 'staff_view')->withPivot('temp_params');
    }

    /**
     * A one-to-many relationship with Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownprojects()
    {
        return $this->hasMany(Project::class, 'project_owner');
    }

    /**
     * A one-to-many relationship with Task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'task_owner');
    }

    /**
     * A one-to-many relationship with Issue.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany(Issue::class, 'issue_owner');
    }

    /**
     * A one-to-many relationship with Milestone.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'milestone_owner');
    }

    /**
     * A one-to-many relationship with Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'event_owner');
    }

    /**
     * A one-to-many relationship with AllowedStaff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allows()
    {
        return $this->hasMany(AllowedStaff::class);
    }

    /**
     * A one-to-many relationship with Follower.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function follows()
    {
        return $this->hasMany(Follower::class);
    }

    /**
     * A polymorphic one-to-one relationship with User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function user()
    {
        return $this->morphOne(User::class, 'linked')->withTrashed();
    }

    /**
     * Polymorphic one-to-many relationship with SocialMedia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function socialmedia()
    {
        return $this->morphMany(SocialMedia::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with EventAttendee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function eventattendees()
    {
        return $this->morphMany(EventAttendee::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with ChatRoomMember.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function chatRoomMembers()
    {
        return $this->morphMany(ChatRoomMember::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with NoteInfo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function linearNotes()
    {
        return $this->morphMany(NoteInfo::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with Note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with AttachFile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachfiles()
    {
        return $this->morphMany(AttachFile::class, 'linked');
    }
}
