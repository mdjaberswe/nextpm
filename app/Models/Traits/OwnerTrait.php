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

namespace App\Models\Traits;

trait OwnerTrait
{
    /**
     * Get the auth user permission status to view the specified resource.
     *
     * @return bool
     */
    public function getAuthCanViewAttribute()
    {
        return $this->authCan('view');
    }

    /**
     * Get the auth user permission status to edit the specified resource.
     *
     * @return bool
     */
    public function getAuthCanEditAttribute()
    {
        return $this->authCan('edit');
    }

    /**
     * Get the auth user permission status to delete the specified resource.
     *
     * @return bool
     */
    public function getAuthCanDeleteAttribute()
    {
        return $this->authCan('delete');
    }

    /**
     * Get the auth user permission status about to change owner the specified resource.
     *
     * @return bool
     */
    public function getAuthCanChangeOwnerAttribute()
    {
        if ($this->authCan('edit') && permit('change_owner.' . $this->identifier)) {
            return true;
        }

        return false;
    }

    /**
     * If the user is admin or created|owner of this specified resource.
     *
     * @param \App\Model\Staff $staff
     *
     * @return bool
     */
    public function isElite($staff)
    {
        return ($staff->admin || $staff->id == $this->created_by || $staff->id == $this->owner_id);
    }

    /**
     * If the auth user is admin or created|owner of this specified resource.
     *
     * @return bool
     */
    public function isAuthElite()
    {
        return (auth_staff()->admin || $this->auth_is_owner || $this->auth_is_creator);
    }

    /**
     * Get all permissions status about what the auth user can do.
     *
     * @param string $action
     *
     * @return bool
     */
    public function authCan($action)
    {
        $is_auth_allowed = false;
        $can_permission  = 'can_' . $action;
        $permission      = $this->permission . '.' . $action;
        $owner           = $this->identifier . '_owner';
        $is_auth_permit  = permit($permission);
        $public_access   = ($action == 'view') ? ['public', 'public_rwd'] : ['public_rwd'];

        // If the auth user does not have the global permission.
        if (! $is_auth_permit) {
            return false;
        }

        // If the auth user is admin.
        if (auth_staff()->admin) {
            return true;
        }

        // If Access is equal to Public read-only Or Public read/write/delete, but not Private.
        // Case: Viewable if Access is equal to Public read-only Or Public read/write/delete.
        // Case: Editable iff Access is equal to Public read/write/delete.
        // Case: Deletable iff Access is equal to Public read/write/delete.
        // And the auth user has global permission for every case.
        if (in_array($this->access, $public_access) && $is_auth_permit) {
            return true;
        }

        // Permitted if the owner is equal to the auth user and the auth user has the global permission.
        if (($this->$owner == auth_staff()->id) && $is_auth_permit) {
            return true;
        }

        // Permitted if the parent owner is equal to the auth user and the auth user has the global permission.
        if (! is_null($this->parent_owner_id) && ($this->parent_owner_id == auth_staff()->id) && $is_auth_permit) {
            return true;
        }

        // Permitted if the creator is equal to the auth user and the auth user has the global permission.
        if ((non_property_checker($this->createdBy(), 'linked_id') == auth_staff()->id) && $is_auth_permit) {
            return true;
        }

        // Get the auth user is allowed or not to perform this action.
        if (in_array(auth_staff()->id, $this->allowedstaffs->pluck('staff_id')->toArray())) {
            $is_auth_allowed = $this->allowedstaffs()->whereStaff_id(auth_staff()->id)->first()->$can_permission;
        }

        // Permitted if the auth user is allowed and has the global permission.
        if ($is_auth_allowed && $is_auth_permit) {
            return true;
        }

        // The specified resource is related to Project and the auth user is a member of the project and has permission.
        if ($this->linked_type == 'project'
            && $this->linked->authMember()->count()
            && $this->linked->authCanDo($permission)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Filter by the auth user viewable data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthViewData($query)
    {
        if (! permit($this->permission . '.' . 'view')) {
            return $query->whereNull('id');
        }

        if (auth_staff()->admin) {
            return $query;
        }

        $table         = $this->table;
        $identifier    = $this->identifier;
        $joined_tables = collect($query->getQuery()->joins)->pluck('table')->toArray();

        // Viewable Query:
        // Access is equal to Public read-only OR
        // Access is equal to Public read/write/delete OR
        // Owner is equal to Auth User OR
        // Creator is equal to Auth User OR
        // Auth User is allowed to view.
        $query = $query->orWhere($this->table . '.access', 'public')
                        ->orWhere($this->table . '.access', 'public_rwd')
                        ->orWhere($this->identifier . '_owner', auth_staff()->id)
                        ->leftjoin('revisions', $this->table . '.id', '=', 'revisions.revisionable_id')
                        ->leftjoin('allowed_staffs', $this->table . '.id', '=', 'allowed_staffs.linked_id')
                        ->orWhere(function ($query) use ($identifier) {
                            $query->where('revisionable_type', $identifier)
                                  ->where('user_id', auth()->user()->id)
                                  ->where('key', 'created_at');
                        })->orWhere(function ($query) use ($identifier) {
                            $query->where('allowed_staffs.linked_type', $identifier)
                                  ->where('allowed_staffs.staff_id', auth_staff()->id)
                                  ->where('allowed_staffs.can_view', 1);
                        });

        // Viewable Query:
        // The identifier is equal to Project and the auth user is project member OR
        // the parent owner is equal to the auth user.
        if ($this->identifier == 'project') {
            if (! in_array('project_member', $joined_tables)) {
                $query = $query->leftJoin('project_member', 'project_member.project_id', '=', 'projects.id');
            }

            $query = $query->orWhere('project_member.staff_id', auth_staff()->id);
        } elseif ($this->identifier == 'milestone') {
            $query = $query->leftJoin('projects', 'milestones.project_id', '=', 'projects.id')
                           ->leftJoin('project_member', 'milestones.project_id', '=', 'project_member.project_id')
                           ->orWhere('projects.project_owner', auth_staff()->id)
                           ->orWhere('project_member.staff_id', auth_staff()->id);
        } elseif (in_array($this->identifier, ['task', 'issue', 'event'])) {
            // If the auth user is "Parent Project" owner or member.
            $query = $query->leftJoin('projects', $table . '.linked_id', '=', 'projects.id')
                           ->leftJoin('project_member', $table . '.linked_id', '=', 'project_member.project_id')
                           ->orWhere(function ($query) use ($table) {
                                $query->where($table . '.linked_type', 'project')
                                      ->where('projects.project_owner', auth_staff()->id)
                                      ->orWhere('project_member.staff_id', auth_staff()->id);
                           });

            if ($identifier == 'event') {
                // If the auth user is a participant in the event.
                $query = $query->leftJoin('event_attendees', 'event_attendees.event_id', '=', 'events.id')
                               ->orWhere(function ($query) {
                                    $query->where('event_attendees.linked_type', 'staff')
                                          ->where('event_attendees.linked_id', auth_staff()->id);
                               });
            }
        }

        return $query;
    }

    /**
     * Authenticate user viewable data ids.
     *
     * @return array
     */
    public static function getAuthViewIds()
    {
        $model          = with(new static);
        $table          = $model->table;
        $identifier     = $model->identifier;
        $permission     = $model->permission . '.' . 'view';
        $join_id        =  $table . '.id';
        $owner          = $identifier . '_owner';
        $is_auth_permit = permit($permission);

        if (! $is_auth_permit) {
            return [];
        }

        if (auth_staff()->admin) {
            return self::orderBy('id')->pluck('id');
        }

        // Viewable Query:
        // Access is equal to Public read-only OR
        // Access is equal to Public read/write/delete OR
        // Owner is equal to Auth User OR
        // Creator is equal to Auth User OR
        // Auth User is allowed to view.
        $query = self::where($table . '.access', 'public')
                    ->orWhere($table . '.access', 'public_rwd')
                    ->orWhere($owner, auth_staff()->id)
                    ->leftjoin('revisions', $join_id, '=', 'revisions.revisionable_id')
                    ->leftjoin('allowed_staffs', $join_id, '=', 'allowed_staffs.linked_id')
                    ->orWhere(function ($query) use ($identifier) {
                        $query->where('revisionable_type', $identifier)
                              ->where('user_id', auth()->user()->id)
                              ->where('key', 'created_at');
                    })->orWhere(function ($query) use ($identifier) {
                        $query->where('allowed_staffs.linked_type', $identifier)
                              ->where('allowed_staffs.staff_id', auth_staff()->id)
                              ->where('allowed_staffs.can_view', 1);
                    });

        // Viewable Query:
        // The identifier is equal to Project and the auth user is project member OR
        // the parent owner is equal to the auth user.
        if ($identifier == 'project') {
            $query = $query->leftJoin('project_member', 'project_member.project_id', '=', 'projects.id')
                           ->orWhere('project_member.staff_id', auth_staff()->id);
        } elseif ($identifier == 'milestone') {
            $query = $query->leftJoin('projects', 'milestones.project_id', '=', 'projects.id')
                           ->leftJoin('project_member', 'milestones.project_id', '=', 'project_member.project_id')
                           ->orWhere('projects.project_owner', auth_staff()->id)
                           ->orWhere('project_member.staff_id', auth_staff()->id);
        } elseif (in_array($identifier, ['task', 'issue', 'event'])) {
            // If the auth user is "Parent Project" owner or member.
            $query = $query->leftJoin('projects', $table . '.linked_id', '=', 'projects.id')
                           ->leftJoin('project_member', $table . '.linked_id', '=', 'project_member.project_id')
                           ->orWhere(function ($query) use ($table) {
                                $query->where($table . '.linked_type', 'project')
                                      ->where('projects.project_owner', auth_staff()->id)
                                      ->orWhere('project_member.staff_id', auth_staff()->id);
                           });

            if ($identifier == 'event') {
                // If the auth user is a participant in the event.
                $query = $query->leftJoin('event_attendees', 'event_attendees.event_id', '=', 'events.id')
                               ->orWhere(function ($query) {
                                    $query->where('event_attendees.linked_type', 'staff')
                                          ->where('event_attendees.linked_id', auth_staff()->id);
                               });
            }
        }

        return $query->select($table . '.*')->groupBy($join_id)->pluck($table . '.id');
    }

    /**
     * Get the auth user view data.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getAuthViewData()
    {
        return self::whereIn(with(new static)->table . '.id', self::getAuthViewIds());
    }

    /**
     * Get owners list.
     *
     * @param array $default_list
     * @param array $select_item
     *
     * @return array
     */
    public function getOwnerList($default_list = [], $select_item = ['' => '-None-'])
    {
        // If it is related to "Project" then member and external users categories dropdown list.
        if ($this->linked_type == 'project') {
            return $select_item + \App\Models\Staff::getAppendDropdownList(
                $this->linked_type,
                $this->linked_id,
                $this->linked
            );
        }

        return $default_list;
    }

    /**
     * Get the auth user-owned data only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $auth_owner
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyAuthOwner($query, $auth_owner)
    {
        if ($auth_owner == true) {
            return $query->where($this->owner_field, auth_staff()->id);
        }

        return $query;
    }

    /**
     * Get data by listed owner condition.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null                            $owner
     * @param bool                                  $truly_null
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereInOwner($query, $owner = null, $truly_null = false)
    {
        if ($truly_null == false) {
            $whereClause = ! is_null($owner) ? 'whereIn' : 'whereNot';
            $whereField = ! is_null($owner) ? $this->owner_field : 'id';

            return $query->$whereClause($whereField, $owner);
        }

        return $query->whereNull($this->owner_field);
    }

    /**
     * Get the previous record of the specified resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getPrevRecordAttribute()
    {
        if ($this->order_type == 'desc') {
            return self::getAuthViewData()->where('id', '>', $this->id)->first();
        }

        return self::getAuthViewData()->where('id', '<', $this->id)->latest('id')->first();
    }

    /**
     * Get the next record of the specified resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getNextRecordAttribute()
    {
        if ($this->order_type == 'desc') {
            return self::getAuthViewData()->where('id', '<', $this->id)->latest('id')->first();
        }

        return self::getAuthViewData()->where('id', '>', $this->id)->first();
    }

    /**
     * Get the owner field name.
     *
     * @return string
     */
    public function getOwnerFieldAttribute()
    {
        return $this->identifier . '_owner';
    }

    /**
     * Get the owner field id.
     *
     * @return int
     */
    public function getOwnerIdAttribute()
    {
        $owner_field = $this->owner_field;

        return $this->$owner_field;
    }

    /**
     * Get parent owner id.
     *
     * @return int
     */
    public function getParentOwnerIdAttribute()
    {
        if (not_null_empty($this->linked_type)) {
            return $this->linked->owner_id;
        }

        return null;
    }

    /**
     * Get to know the auth user is the owner of the specified resource or not.
     *
     * @return bool
     */
    public function getAuthIsOwnerAttribute()
    {
        return $this->owner_id == auth_staff()->id;
    }

    /**
     * Get the owner's name.
     *
     * @return string
     */
    public function getOwnerNameAttribute()
    {
        return non_property_checker($this->owner, 'name');
    }

    /**
     * Get the owner show page HTML link.
     *
     * @param string    $status
     * @param bool|null $only_avatar
     *
     * @return string
     */
    public function getOwnerHtmlAttribute($status = null, $only_avatar = null)
    {
        $owner_field = $this->owner_field;

        if (is_null($this->$owner_field)) {
            return null;
        }

        return $this->owner->getProfileHtmlAttribute($status, $only_avatar);
    }

    /**
     * Get mass action checkbox HTML.
     *
     * @param string|null $css
     * @param bool|null   $disabled
     *
     * @return string
     */
    public function getCheckboxHtmlAttribute($css = null, $disabled = null)
    {
        $disabled = null;

        // If the auth user can't edit or delete the specified resource then disable the checkbox.
        if (! $this->auth_can_edit && ! $this->auth_can_delete) {
            $disabled = true;
        }

        return parent::getCheckboxHtmlAttribute($css, $disabled);
    }
}
