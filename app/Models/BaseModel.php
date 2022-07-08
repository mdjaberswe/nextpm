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
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $icon;
    protected $route;
    protected $action;
    protected $identifier;
    protected $order_type;
    protected $permission;
    protected $mass_action;
    protected $select_column;
    protected $mass_permission;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->icon            = $this->setIcon();
        $this->route           = $this->setRoute();
        $this->action          = $this->setAction();
        $this->order_type      = $this->setOrderType();
        $this->identifier      = $this->setIdentifier();
        $this->permission      = $this->setPermission();
        $this->mass_action     = $this->setMassAction();
        $this->select_column   = $this->setSelectColumn();
        $this->mass_permission = $this->setMassPermission();
    }

    /**
     * Set resource icon.
     *
     * @return string
     */
    public function setIcon()
    {
        return module_icon(substr($this->table, 0, -1));
    }

    /**
     * Set resource order type.
     *
     * @return string
     */
    public function setOrderType()
    {
        return 'desc';
    }

    /**
     * Set resource identifier name.
     *
     * @return string
     */
    public function setIdentifier()
    {
        return substr($this->table, 0, -1);
    }

    /**
     * Get resource identifier name.
     *
     * @return string
     */
    public static function getIdentifier()
    {
        return with(new static)->identifier;
    }

    /**
     * Set resource route name.
     *
     * @return string
     */
    public function setRoute()
    {
        return substr($this->table, 0, -1);
    }

    /**
     * Get resource route name.
     *
     * @return string
     */
    public static function getRoute()
    {
        return with(new static)->route;
    }

    /**
     * Set resource permission name.
     *
     * @return string
     */
    public function setPermission()
    {
        return substr($this->table, 0, -1);
    }

    /**
     * Get resource permission name.
     *
     * @return string
     */
    public static function getPermission()
    {
        return with(new static)->permission;
    }

    /**
     * Set resource mass permission name.
     *
     * @return string
     */
    public function setMassPermission()
    {
        return substr($this->table, 0, -1);
    }

    /**
     * Get resource mass permission name.
     *
     * @return string
     */
    public static function getMassPermission()
    {
        return with(new static)->mass_permission;
    }

    /**
     * Set resource actions.
     *
     * @return array
     */
    public function setAction()
    {
        return ['edit', 'delete'];
    }

    /**
     * Get resource actions.
     *
     * @return array
     */
    public static function getAction()
    {
        return with(new static)->action;
    }

    /**
     * Get the auth user to have at least one action permission status.
     *
     * @return bool
     */
    public static function allowAction()
    {
        $actions = self::getAction();
        $basic = ['edit', 'delete'];
        $permission_key = self::getPermission();

        foreach ($actions as $action) {
            $permission = in_array($action, $basic) ? $permission_key . '.' . $action : $action . '.' . $permission_key;

            if (permit($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set resource mass actions.
     *
     * @return array
     */
    public function setMassAction()
    {
        return ['mass_update', 'mass_delete'];
    }

    /**
     * Get resource mass actions.
     *
     * @return array
     */
    public static function getMassAction()
    {
        return with(new static)->mass_action;
    }

    /**
     * Get the auth user to have at least one mass action permission status.
     *
     * @return bool
     */
    public static function allowMassAction()
    {
        $actions = self::getMassAction();
        $permission_key = self::getMassPermission();

        // Mass action (ex. mass edit, mass delete) will only execute
        // if the auth user has primary permission (ex. edit, delete) of the module.
        foreach ($actions as $action) {
            $permission         = $action . '.' . $permission_key;
            $primary_action     = str_replace('update', 'edit', $action);
            $primary_permission = $permission_key . '.' . str_replace('mass_', '', $primary_action);

            if (permit($permission) && permit($primary_permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set resource selected columns that can be displayed in the data table.
     *
     * @return array
     */
    public function setSelectColumn()
    {
        return ['id', 'name'];
    }

    /**
     * Get hidden columns according to user action permission status.
     *
     * @return array
     */
    public static function hideColumns()
    {
        $hide_columns = [];

        // Hide action columns if the auth user doesn't have any action permission.
        if (! static::allowAction()) {
            $hide_columns[] = 'action';
        }

        // Hide mass checkbox columns if the auth user doesn't have any mass action permission.
        if (! static::allowMassAction()) {
            $hide_columns[] = 'checkbox';
        }

        return $hide_columns;
    }

    /**
     * Get the specified resource morph name.
     *
     * @return string
     */
    public function getMorphNameAttribute()
    {
        return substr($this['table'], 0, -1);
    }

    /**
     * Get the specified resource readable call name.
     *
     * @return string
     */
    public function getIdentifierCallNameAttribute()
    {
        return ucfirst($this->identifier);
    }

    /**
     * Check the specified resource has an attribute or not.
     *
     * @param string $attr
     *
     * @return bool
     */
    public function hasAttribute($attr)
    {
        return in_array($attr, $this->fillable);
    }

    /**
     * Get prev record of the specified resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getPrevRecordAttribute()
    {
        return $this->order_type == 'desc'
               ? self::where('id', '>', $this->id)->first()
               : self::where('id', '<', $this->id)->latest('id')->first();
    }

    /**
     * Get next record of the specified resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getNextRecordAttribute()
    {
        return $this->order_type == 'desc'
               ? self::where('id', '<', $this->id)->latest('id')->first()
               : self::where('id', '>', $this->id)->first();
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
        $css = isset($css) ? $css : 'info';
        $disabled = isset($disabled) && ($disabled == true) ? 'disabled' : null;

        return "<div class='pretty $css smooth'><input class='single-row' type='checkbox'
                    name='{$this->table}[]' value='{$this->id}' $disabled>
                    <label><i class='mdi mdi-check'></i></label>
                </div>";
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

        // If the auth user has "edit" permission.
        if (isset($action_permission['edit']) && $action_permission['edit'] == true) {
            $edit = "<div class='inline-action'>";
            $edit_attribute = "class='edit'";

            // If a common edit modal then adds common edit modal attributes.
            if ($common_modal) {
                $edit_attribute  = "class='common-edit-btn' data-item='{$this->identifier}' " .
                                   "data-url='" . route('admin.' . $this->route . '.edit', $this->id) . "' " .
                                   "data-posturl='" . route('admin.' . $this->route . '.update', $this->id) . "'";

                if (is_array($common_modal)) {
                    foreach ($common_modal as $attribute => $value) {
                        $edit_attribute .= " $attribute='$value'";
                    }
                }
            }

            $edit_btn = "<a editid='{$this->id}' " . $edit_attribute . "><i class='fa fa-pencil'></i></a>";

            // Plain page edit form not in the modal.
            if ($edit_route != null) {
                $edit_route_param = is_null($this->parent_module)
                                    ? $this->id : [$this->id, 'parent_module' => $this->parent_module];
                $edit_btn = "<a href='" . route($edit_route, $edit_route_param) . "'><i class='fa fa-pencil'></i></a>";
            }

            $edit .= $edit_btn . '</div>';
        }

        $complete_dropdown_menu = '';
        $edit_permission = isset($action_permission['edit']) ? $action_permission['edit'] : false;
        $dropdown_menu = $this->extendActionHtml($edit_permission);

        // If the auth user has "delete" permission
        if (isset($action_permission['delete']) && $action_permission['delete'] == true) {
            $delete_attribute = '';

            // Common delete confirmation alert modal.
            if ($common_modal) {
                $delete_attribute .= "data-item='" . snake_to_space($this->identifier) . "'";
            }

            $dropdown_menu .= '<li>' .
                                \Form::open(['route' => [$delete_route, $this->id], 'method' => 'delete']) .
                                    \Form::hidden('id', $this->id) .
                                    \Form::hidden('delete_all', true) .
                                    "<button type='submit' class='delete' $delete_attribute>
                                        <i class='mdi mdi-delete'></i> Delete
                                    </button>" .
                                \Form::close() .
                              '</li>';
        }

        if (isset($dropdown_menu) && $dropdown_menu != '') {
            $complete_dropdown_menu = "<ul class='dropdown-menu'>$dropdown_menu</ul>";
        }

        $toggle         = 'dropdown';
        $toggle_class   = '';
        $toggle_tooltip = '';

        // If the auth user doesn't have edit and other permissions then disable dropdown action buttons.
        if (empty($edit) && empty($complete_dropdown_menu)) {
            $toggle         = '';
            $toggle_class   = 'disable';
            $toggle_tooltip = "data-toggle='tooltip' data-placement='left' title='Permission&nbsp;denied'";
        }

        if (! empty($edit) && empty($complete_dropdown_menu)) {
            $toggle_class = 'inactive';
        }

        $open     = "<div class='action-box $toggle_class' $toggle_tooltip>";
        $dropdown = "<div class='dropdown'>
                        <a class='dropdown-toggle $toggle_class' data-toggle='$toggle'>
                            <i class='fa fa-ellipsis-v'></i>
                        </a>";
        $close    = "</div></div>";
        $action   = $open . $edit . $dropdown . $complete_dropdown_menu . $close;

        return $action;
    }

    /**
     * Get the specified resource to extend actions HTML.
     *
     * @param bool $edit_permission
     *
     * @return null
     */
    public function extendActionHtml($edit_permission = true)
    {
        return null;
    }

    /**
     * Get the specified resource 'remove' action HTML.
     *
     * @return string
     */
    public function getRemoveHtmlAttribute()
    {
        return "<button type='button' class='close' data-toggle='tooltip' data-placement='top' title='Remove'>
                    <span aria-hidden='true'>&times;</span>
                </button>";
    }

    /**
     * Get the base URL of the specified resource.
     *
     * @return string
     */
    public function getBaseUrlAttribute()
    {
        return \Route::has('admin.' . $this->route . '.index')
               ? route('admin.' . $this->route . '.index')
               : url('admin/' . $this->route);
    }

    /**
     * Get show page URL of the specified resource.
     *
     * @param array|null $param
     *
     * @return string
     */
    public function getShowRouteAttribute($param = null)
    {
        $param = is_null($param) ? $this->id : $param;

        return route('admin.' . $this->route . '.show', $param);
    }

    /**
     * Get show page breadcrumb HTML.
     *
     * @return string
     */
    public function getShowPageBreadcrumbAttribute()
    {
        $list_page = "admin.{$this->route}.index:" . ucfirst($this->identifier) . 's';
        $show_page = "<span data-realtime='name'>" . str_limit($this->name, 50) . '</span>';

        return breadcrumb($list_page . '|' . $show_page);
    }

    /**
     * Get the delete route and URL of the specified resource.
     *
     * @return array
     */
    public function getDelRouteAttribute()
    {
        return [
            'name' => 'admin.' . $this->route . '.destroy',
            'url'  => route('admin.' . $this->route . '.destroy', $this->id),
        ];
    }

    /**
     * Get the show page link with a display name of the specified resource.
     *
     * @return string
     */
    public function getNameHtmlAttribute()
    {
        return "<a href='{$this->show_route}'>{$this->name}</a>";
    }

    /**
     * Get the show page link with an icon and display name of the specified resource.
     *
     * @return string
     */
    public function getNameIconHtmlAttribute()
    {
        $icon = method_exists($this, 'getIconAttribute') ? $this->getIconAttribute() : $this->icon;

        return "<a href='{$this->show_route}' class='link-icon'><span class='icon $icon' data-toggle='tooltip'
                    data-placement='top' title='" . ucfirst($this->identifier) . "'></span> {$this->name}" .
               '</a>';
    }

    /**
     * Get plain show page link with a display name of the specified resource.
     *
     * @return string
     */
    public function getNameLinkAttribute()
    {
        return "<a href='{$this->show_route}' class='like-txt'>{$this->name}</a>";
    }

    /**
     * Get plain show page link with an icon and display name of the specified resource.
     *
     * @return string
     */
    public function getNameLinkIconAttribute()
    {
        return "<a href='{$this->show_route}' class='like-txt'>
                    <span class='icon {$this->icon}' data-toggle='tooltip' data-placement='top' title='" .
                    ucfirst($this->identifier) . "'></span> {$this->name}" .
               '</a>';
    }

    /**
     * Display the name with icon HTML of the specified resource.
     *
     * @return string
     */
    public function getNameWithIconAttribute()
    {
        return "<span class='like-txt'>
                    <span class='icon {$this->icon}' data-toggle='tooltip' data-placement='top' title='" .
                    ucfirst($this->identifier) . "'></span> {$this->name}" .
               '</span>';
    }

    /**
     * Get icon HTML of the specified resource.
     *
     * @return string
     */
    public function getIconHtmlAttribute()
    {
        return "<span class='icon {$this->icon} icon-{$this->identifier}'></span>";
    }

    /**
     * Get the show page link with name of the specified resource.
     *
     * @return string
     */
    public function getShowLinkAttribute()
    {
        return "<a href='{$this->show_route}'>{$this->name}</a>";
    }

    /**
     * Get a SQL formatted date.
     *
     * @param \Carbob\Carbon|string $date
     *
     * @return string
     */
    public function sqlDate($date)
    {
        if (is_null($this->$date)) {
            return null;
        }

        return is_object($this->$date)
               ? $this->$date->format('Y-m-d H:i:s')
               : date('Y-m-d H:i:s', strtotime($this->$date));
    }

    /**
     * Get the timestamp value of a date.
     *
     * @param \Carbon\Carbon|string $date
     *
     * @return numeric
     */
    public function dateTimestamp($date)
    {
        return strtotime($this->sqlDate($date));
    }

    /**
     * Get readable ampm formatted date.
     *
     * @param \Carbon\Carbon|string $date
     *
     * @return string
     */
    public function readableDateAmPm($date)
    {
        if (isset($this->$date)) {
            return is_object($this->$date)
                   ? $this->$date->format('M j, Y g:i A')
                   : date('M j, Y g:i A', strtotime($this->$date));
        }

        return null;
    }

    /**
     * Get readable formatted date.
     *
     * @param \Carbon\Carbon|string $date
     * @param string                $format
     *
     * @return string
     */
    public function readableDate($date, $format = null)
    {
        if (isset($this->$date)) {
            $format = is_null($format) ? 'M j, Y' : $format;

            return is_object($this->$date)
                   ? $this->$date->format($format)
                   : date($format, strtotime($this->$date));
        }

        return null;
    }

    /**
     * Get carbon instance of a date.
     *
     * @param string $date
     *
     * @return \Carbon\Carbon
     */
    public function carbonDate($date)
    {
        return new Carbon($this->$date);
    }

    /**
     * Get overdue days from now.
     *
     * @param \Carbon\Carbon|string $date
     *
     * @return int
     */
    public function passedDateVal($date)
    {
        $date_obj = is_object($this->$date) ? $this->$date : $this->carbonDate($date);

        return $date_obj->diffInDays(now(), false);
    }

    /**
     * Get readable date HTML.
     *
     * @param \Carbon\Carbon|string $date
     * @param bool                  $time
     *
     * @return string
     */
    public function readableDateHtml($date, $time = false)
    {
        $html = '';

        if (isset($this->$date)) {
            $readable_date = is_object($this->$date)
                             ? $this->$date->format('M j, Y') : date('M j, Y', strtotime($this->$date));
            $html .= '<span>' . fill_up_space($readable_date) . '</span>';

            if ($time == true) {
                $readable_time = is_object($this->$date)
                                 ? $this->$date->format('g:i A') : date('g:i A', strtotime($this->$date));
                $html .= "<br><span class='shadow'>{$readable_time}</span>";
            }
        }

        return $html;
    }

    /**
     * Get long ids string of resources separated by commas.
     *
     * @param array|null $push
     *
     * @return string
     */
    public static function commaSeparatedIds($push = null)
    {
        $ids = self::get(['id'])->pluck('id')->toArray();

        if (is_array($push)) {
            foreach ($push as $single_push) {
                array_push($ids, $single_push);
            }
        }

        return implode(',', $ids);
    }

    /**
     * Get created at field value in AM|PM formatted date.
     *
     * @return string
     */
    public function getCreatedAmpmAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get readable AM|PM formatted date of created at field value.
     *
     * @return string
     */
    public function getReadableCreatedFullAmpmAttribute()
    {
        return $this->created_at->format('D, M j, Y g:i A');
    }

    /**
     * Get updated at field value in AM|PM formatted date.
     *
     * @return string
     */
    public function getUpdatedAmpmAttribute()
    {
        return $this->updated_at->format('M j, Y g:i A');
    }

    /**
     * Get modified at attribute value that comes from updated at field value.
     *
     * @return string
     */
    public function getModifiedAtAttribute()
    {
        return $this->updated_at;
    }

    /**
     * Get deleted at field value in AM|PM formatted date.
     *
     * @return string
     */
    public function getDeletedAmpmAttribute()
    {
        return $this->deleted_at->format('M j, Y g:i A');
    }

    /**
     * Get a short readable formatted date of created at field value.
     *
     * @param bool $today
     *
     * @return string
     */
    public function getCreatedShortFormatAttribute($today = false)
    {
        if (isset($today) && $today == true && $this->created_at->isToday()) {
            return $this->created_time_ampm;
        } elseif ($this->created_at->format('Y') == date('Y')) {
            return $this->created_at->format('M j');
        } else {
            return $this->created_at->format('M j, Y');
        }
    }

    /**
     * Get readable AM|PM formatted date of created at field value.
     *
     * @param bool $today
     *
     * @return string
     */
    public function getCreatedTimeAmpmAttribute($today = false)
    {
        if (isset($today) && $today == true && $this->created_at->isToday()) {
            return 'Today';
        }

        return $this->created_at->format('g:i A');
    }

    /**
     * Get short readable display day formatted date of created at field value.
     *
     * @return string
     */
    public function getCreatedShortDayAttribute()
    {
        $diff_days = $this->created_at->diff(now())->days;

        if ($this->created_at->isToday()) {
            $day = $this->created_at->format('g:i A');

            return $day;
        } elseif ($diff_days < 8) {
            $day = $this->created_at->format('D g:i A');
            $day = strtoupper(substr($day, 0, 3)).substr($day, 3);
        } elseif ($this->created_at->format('Y') == date('Y')) {
            $day = $this->created_at->format('M j, g:i A');
        } else {
            $day = $this->created_at->format('M j, Y, g:i A');
        }

        return $day;
    }

    /**
     * Get the user who is responsible for creating the specified resource.
     *
     * @return \App\Models\User
     */
    public function createdBy()
    {
        $first = $this->revisionHistory->first();

        if (isset($first) && $first->key == 'created_at') {
            return $first->userResponsible();
        }

        return Staff::superAdmin();
    }

    /**
     * Get the user who is responsible for updating the specified resource.
     *
     * @return \App\Models\User
     */
    public function updatedBy()
    {
        $last = $this->revisionHistory->last();

        if (isset($last)) {
            return $last->userResponsible();
        }

        return Staff::superAdmin();
    }

    /**
     * Get the user linked id who is responsible for creating the specified resource.
     *
     * @return int
     */
    public function getCreatedByAttribute()
    {
        return $this->createdBy()->linked_id;
    }

    /**
     * Get the user linked id who is responsible for updating the specified resource.
     *
     * @return int
     */
    public function getUpdatedByAttribute()
    {
        return $this->updatedBy()->linked_id;
    }

    /**
     * Get to know the auth user is the creator or not of the specified resource.
     *
     * @return bool
     */
    public function getAuthIsCreatorAttribute()
    {
        return $this->created_by == auth_staff()->id;
    }

    /**
     * Get the user name who is responsible for creating the specified resource.
     *
     * @return string
     */
    public function createdByName()
    {
        return is_null($this->createdBy()) ? null : $this->createdBy()->linked->name;
    }

    /**
     * Get the user show page link who is responsible for creating the specified resource.
     *
     * @return string
     */
    public function createdByNameLink()
    {
        return is_null($this->createdBy()) ? null : $this->createdBy()->linked->name_link;
    }

    /**
     * Get the user profile link who is responsible for creating the specified resource.
     *
     * @return string
     */
    public function createdByProfile()
    {
        return is_null($this->createdBy()) ? null : $this->createdBy()->linked->profile_html;
    }

    /**
     * Get the user linked id who is responsible for updating the specified resource.
     *
     * @return int
     */
    public function getModifiedByAttribute()
    {
        return $this->updatedBy()->linked_id;
    }

    /**
     * Get the user name who is responsible for updating the specified resource.
     *
     * @return string
     */
    public function updatedByName()
    {
        return is_null($this->updatedBy()) ? null : $this->updatedBy()->linked->name;
    }

    /**
     * Get the user avatar who is responsible for creating the specified resource.
     *
     * @return string
     */
    public function createdByAvatar()
    {
        return is_null($this->createdBy()) ? null : $this->createdBy()->linked->avatar;
    }

    /**
     * Datatable global search filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $attributes
     *
     * @return bool
     */
    public function globalSearch($request, array $attributes)
    {
        // If the resource data table common input is not empty.
        if ($request->has('globalSearch') && $request->globalSearch != '') {
            foreach ($attributes as $attribute) {
                $attribute_lower_val = strtolower(strip_tags($this->$attribute));
                $attribute_lower_val = str_replace('&nbsp;', ' ', $attribute_lower_val);
                $search_lower_val    = strtolower($request->globalSearch);

                // If attribute contain common search input value then return true.
                if (str_contains($attribute_lower_val, $search_lower_val)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * The query for getting resource data where a list of users responsible for creation resource data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|int                             $user_id
     * @param bool                                  $created_by_user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedByUser($query, $user_id, $created_by_user = true)
    {
        $identifier = $this->identifier;
        $whereUser  = $created_by_user ? 'whereIn' : 'whereNotIn';
        $user_id    = is_array($user_id) ? $user_id : [$user_id];

        return $query->leftjoin('revisions', $this->table . '.id', '=', 'revisions.revisionable_id')
                     ->where(function ($query) use ($identifier, $user_id, $whereUser) {
                        $query->whereRevisionable_type($identifier)
                              ->$whereUser('user_id', $user_id)
                              ->wherekey('created_at');
                     });
    }

    /**
     * The query for select display column of resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelectColumn($query)
    {
        return $query->select($this->select_column);
    }

    /**
     * Erase risk from joined table columns collision.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterMask($query)
    {
        return $query->select($this->table . '.*')->groupBy($this->table . '.id');
    }

    /**
     * Query resource data according to the current filter parameter.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterViewData($query)
    {
        $filter_view = FilterView::getCurrentFilter($this->identifier);

        // If the current "Filter View" has parameter then query data according to filter conditions and values.
        if (count($filter_view->param_array)) {
            foreach ($filter_view->param_array as $attribute => $condition_val) {
                $condition         = $condition_val['condition'];
                $conditional_value = $condition_val['value'];

                // The special query for "Owner" and "Linked Module" parameters.
                if (strpos($attribute, 'owner') !== false
                    && is_array($conditional_value)
                    && in_array('0', $conditional_value)
                ) {
                    $param_key = array_search('0', $conditional_value);
                    $conditional_value[$param_key] = auth_staff()->id;
                } elseif ($attribute == 'linked_type' && in_array($condition, ['equal', 'not_equal'])) {
                    $sub_attribute       = 'linked_id';
                    $sub_attribute_value = (int) $conditional_value['linked_id'];
                    $conditional_value   = $conditional_value['linked_type'];

                    if ($condition == 'equal') {
                        $query = $query->conditionalFilterQuery($sub_attribute, $condition, $sub_attribute_value);
                    } elseif ($condition == 'not_equal') {
                        $query = $query->where(function ($query) use ($conditional_value, $sub_attribute_value) {
                            $query->where('linked_type', $conditional_value)
                                  ->where('linked_id', '!=', $sub_attribute_value);
                        })->orWhere('linked_type', '!=', $conditional_value)->orWhere('linked_type', null);

                        return $query;
                    }
                }

                if ($this->hasAttribute($attribute)) {
                    $query = $query->conditionalFilterQuery($attribute, $condition, $conditional_value);
                } else {
                    $query = $query->filterViewQuery($attribute, $condition, $conditional_value);
                }
            }
        }

        return $query;
    }

    /**
     * Query resource data according to filter param condition.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $attribute
     * @param string                                $condition
     * @param mixed                                 $conditional_value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConditionalFilterQuery($query, $attribute, $condition, $conditional_value)
    {
        if (in_array($condition, ['equal', 'not_equal', 'contain', 'not_contain'])) {
            $conditional_value = (array) $conditional_value;
        }

        switch ($condition) {
            case 'equal':
                return $query->whereIn($attribute, $conditional_value);
            case 'not_equal':
                return $query->whereNotIn($attribute, $conditional_value);
            case 'contain':
                return $query->where(function ($query) use ($attribute, $conditional_value) {
                    foreach ($conditional_value as $single_conditional_value) {
                        $query->orWhere($attribute, 'LIKE', '%' . $single_conditional_value . '%');
                    }
                });
            case 'not_contain':
                return $query->where(function ($query) use ($attribute, $conditional_value) {
                    foreach ($conditional_value as $single_conditional_value) {
                        $query->where($attribute, 'NOT LIKE', '%' . $single_conditional_value . '%');
                    }
                });
            case 'empty':
                return $query->where($attribute, '')->orWhereNull($attribute);
            case 'not_empty':
                return $query->where($attribute, '!=', '')->whereNotNull($attribute);
            case 'less':
                return $query->where($attribute, '<', $conditional_value);
            case 'greater':
                return $query->where($attribute, '>', $conditional_value);
            case 'before':
                $before_date = date('Y-m-d H:i:s', strtotime('-' . $conditional_value . ' days'));

                return $query->where($attribute, '<', $before_date);
            case 'after':
                $after_date = date('Y-m-d H:i:s', strtotime('+' . $conditional_value . ' days'));

                return $query->where($attribute, '>', $after_date);
            case 'last':
                $today     = date('Y-m-d H:i:s');
                $last_date = date('Y-m-d H:i:s', strtotime('-' . $conditional_value . ' days'));

                return $query->where($attribute, '<=', $today)->where($attribute, '>=', $last_date);
            case 'next':
                $today     = date('Y-m-d H:i:s');
                $next_date = date('Y-m-d H:i:s', strtotime('+' . $conditional_value . ' days'));

                return $query->where($attribute, '>=', $today)->where($attribute, '<=', $next_date);
            default:
                return $query;
        }
    }

    /**
     * Query within a period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $date_field
     * @param string                                $start_date
     * @param string                                $end_date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyWithinDate($query, $date_field, $start_date, $end_date)
    {
        return $query->where($this->table . '.' . $date_field, '>=', $start_date)
                     ->where($this->table . '.' . $date_field, '<=', $end_date);
    }

    /**
     * Query 'created_at' field within a period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $start_date
     * @param string                                $end_date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithinCreated($query, $start_date, $end_date)
    {
        return $query->onlyWithinDate('created_at', $start_date, $end_date);
    }

    /**
     * The query for not having a field value.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $field
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNot($query, $field, $value)
    {
        return $query->where($field, '!=', $value);
    }

    /**
     * Get the last item from resource data according to asc order.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function last()
    {
        return self::latest('id')->first();
    }
}
