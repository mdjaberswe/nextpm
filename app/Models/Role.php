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

use App\Models\Traits\HistoryTrait;
use Zizaco\Entrust\Traits\EntrustRoleTrait;
use Zizaco\Entrust\Contracts\EntrustRoleInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends BaseModel implements EntrustRoleInterface
{
    use EntrustRoleTrait, SoftDeletes, HistoryTrait
    {
        EntrustRoleTrait::restore insteadof SoftDeletes;
        EntrustRoleTrait::boot insteadof HistoryTrait;
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'display_name', 'description', 'fixed', 'label'];

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
    protected $dontKeepRevisionOf = ['name', 'fixed', 'label'];

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFieldNames = ['display_name' => 'Name'];

    /**
     * Fields list array where the index is field's name and corresponding value as field's display name.
     *
     * @var array
     */
    protected static $fieldlist = ['description' => 'Description', 'display_name' => 'Name'];

    /**
     * Fields name array that can be mass updatable.
     *
     * @var array
     */
    protected static $mass_fieldlist = ['display_name', 'description'];

    /**
     * Fields name array that uses to filter data.
     *
     * @var array
     */
    protected static $filter_fieldlist = ['display_name', 'description'];

    /**
     * Role form validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        $unique_name = ! isset($data['id']) ? 'unique:roles,display_name' : 'unique:roles,display_name,' . $data['id'];

        return validator($data, [
            'name'        => 'required|max:200|' . $unique_name,
            'permissions' => 'array|exists:permissions,id',
            'description' => 'max:255',
        ]);
    }

    /**
     * Role filter data validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function filterValidate($data)
    {
        $fields = ['display_name', 'description'];
        $rules  = FilterView::filterRulesGenerator($data, $fields);

        return validator($data, $rules);
    }

    /**
     * Get field list array where array index is field name and value is the display name.
     *
     * @return array
     */
    public static function fieldlist()
    {
        return self::$fieldlist;
    }

    /**
     * Get a filterable field list array.
     *
     * @return array
     */
    public static function filterFieldList()
    {
        return self::$filter_fieldlist;
    }

    /**
     * Get a filterable field list array where field name as key and field display name as value.
     *
     * @return array
     */
    public static function filterFieldDropDown()
    {
        $except = array_diff(array_keys(self::fieldlist()), self::filterFieldList());

        return array_except(self::fieldlist(), $except);
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
     * Set resource mass actions.
     *
     * @return array
     */
    public function setMassAction()
    {
        return ['mass_delete'];
    }

    /**
     * Set resource selected columns that can be displayed in the data table.
     *
     * @return array
     */
    public function setSelectColumn()
    {
        return ['id', 'name', 'display_name', 'description', 'fixed'];
    }

    /**
     * Get resource data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        return [
            'thead'        => [
                'role name', 'description',
                ['total users', 'data_class' => 'center'],
                ['view users', 'data_class' => 'center', 'orderable' => 'false'],
            ],
            'checkbox'     => self::allowMassAction(),
            'action'       => self::allowAction(),
            'json_columns' => \DataTable::jsonColumn([
                'checkbox', 'display_name', 'description', 'total_users', 'view_users', 'action',
            ], self::hideColumns()),
        ];
    }

    /**
     * Get resource table data.
     *
     * @param \App\Models\Role         $roles
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($roles, $request)
    {
        return \DataTable::of($roles)->addColumn('checkbox', function ($role) {
            return $role->checkbox_html;
        })->editColumn('display_name', function ($role) {
            return $role->display_name_html;
        })->addColumn('total_users', function ($role) {
            return $role->total_users_html;
        })->addColumn('view_users', function ($role) {
            return $role->view_users_html;
        })->addColumn('action', function ($role) {
            return $role->getActionHtml('Role', 'admin.role.destroy', 'admin.role.edit', [
                'edit'   => permit('role.edit') && ! $role->fixed,
                'delete' => permit('role.delete') && ! $role->fixed,
            ]);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->globalSearch($request, ['display_name', 'description', 'total_users_count']);
            });
        })->make(true);
    }

    /**
     * Get admin role id.
     *
     * @return int
     */
    public static function getAdminRoleId()
    {
        return self::whereName('administrator')->first()->id;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * The query only gets general roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyGeneral($query)
    {
        return $query->whereLabel('general');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get mass action checkbox HTML.
     *
     * @param string    $css
     * @param bool|null $disabled
     *
     * @return string
     */
    public function getCheckboxHtmlAttribute($css = null, $disabled = null)
    {
        $disabled = null;

        if ($this->fixed) {
            $disabled = true;
        }

        return parent::getCheckboxHtmlAttribute($css, $disabled);
    }

    /**
     * Get display name HTML.
     *
     * @return string
     */
    public function getDisplayNameHtmlAttribute()
    {
        return "<a href='" . route('admin.role.show', $this->id) . "' class='role-name'>" .
                    $this->display_name .
               '</a>';
    }

    /**
     * Get total users HTML of the role.
     *
     * @return string
     */
    public function getTotalUsersHtmlAttribute()
    {
        $count = $this->users->count();
        $html  = "<a class='like-txt role-users' rowid='{$this->id}'>{$count}</a>";

        return  $count ? $html : $count;
    }

    /**
     * Get view users link of the role.
     *
     * @return string
     */
    public function getViewUsersHtmlAttribute()
    {
        return "<a class='tbl-btn role-users' rowid='{$this->id}' data-toggle='tooltip' data-placement='top'
                   title='Role Users'><i class='pe-7s-user pe-va lg'></i>
                </a>";
    }

    /**
     * Get total no of users.
     *
     * @return int
     */
    public function getTotalUsersCountAttribute()
    {
        return $this->users->count();
    }

    /**
     * Get all users list of the role.
     *
     * @return string
     */
    public function getUsersListHtmlAttribute()
    {
        $html = '';

        if ($this->users->count()) {
            foreach ($this->users as $user) {
                $html .= "<div class='plain-list'>{$user->linked->profile_plain_html}</div>";
            }
        } else {
            $html .= "<p class='center-lg'><i class='pe-7s-user pe-va lg'></i><br>No users found in this role</p>";
        }

        return $html;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A many-to-many relationship with User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * A many-to-many relationship with Permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }
}
