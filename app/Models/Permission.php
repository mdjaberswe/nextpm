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

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * The query for matching a specific module.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModule($query)
    {
        $query->where('name', 'LIKE', '%module%');
    }

    /**
     * The query for not matching a specific module.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonModule($query)
    {
        $query->where('name', 'NOT LIKE', '%module%');
    }

    /**
     * The query for matching a specific identifier.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $identifier
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIdentifier($query, $identifier)
    {
        $query->where('name', 'LIKE', $identifier . '%');
    }

    /**
     * The query only gets general permissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyGeneral($query)
    {
        return $query->whereLabel('general');
    }

    /**
     * The query for not getting preserve permissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotPreserve($query)
    {
        return $query->where('type', '!=', 'preserve');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    /**
     * Get an array of permissions groups with parent modules.
     *
     * @param \App\Models\Role|null $role
     * @param bool                  $preserve
     *
     * @return array
     */
    public static function getPermissionsGroups($role = null, $preserve = true)
    {
        $permissions_groups = [];
        $groups = self::groupBy('group')->onlyGeneral()->pluck('group');

        foreach ($groups as $group) {
            $permissions_groups[$group]['name'] = $group;
            $permissions_groups[$group]['display_name'] = snake_to_ucwords($group);

            if ($preserve == false) {
                $modules = self::whereGroup($group)
                               ->module()
                               ->onlyGeneral()
                               ->select(['id', 'name', 'display_name'])
                               ->orderBy('id')
                               ->get();
            } else {
                $modules = self::whereGroup($group)
                               ->module()
                               ->onlyGeneral()
                               ->notPreserve()
                               ->select(['id', 'name', 'display_name'])
                               ->orderBy('id')
                               ->get();
            }

            $all_checked = false;
            $module_permissions  = self::modulesPermissionsMap($modules, $role, $preserve);
            $has_all_permissions = array_unique($module_permissions['has_permission']);

            if (count($has_all_permissions) == 1 && end($has_all_permissions) == true) {
                $all_checked = true;
            }

            $permissions_groups[$group]['modules']            = $modules;
            $permissions_groups[$group]['module_permissions'] = $module_permissions;
            $permissions_groups[$group]['all_checked']        = $all_checked;
        }

        return $permissions_groups;
    }

    /**
     * Get modules permissions organized array.
     *
     * @param array                 $modules
     * @param \App\Models\Role|null $role
     * @param bool                  $preserve
     *
     * @return array
     */
    public static function modulesPermissionsMap($modules, $role = null, $preserve = true)
    {
        $modules_permissions_map = [];

        foreach ($modules as $module) {
            $pos = strpos($module->name, '.');
            $identifier = substr($module->name, $pos + 1);

            if ($preserve == false) {
                $permissions = self::identifier($identifier)
                                   ->nonModule()
                                   ->onlyGeneral()
                                   ->select('id', 'name', 'display_name', 'type')
                                   ->orderBy('id')
                                   ->get();
            } else {
                $permissions = self::identifier($identifier)
                                   ->nonModule()
                                   ->onlyGeneral()
                                   ->notPreserve()
                                   ->select('id', 'name', 'display_name', 'type')
                                   ->orderBy('id')
                                   ->get();
            }

            $modules_permissions_map[$module->name] = [];
            $modules_permissions_map['has_permission'][$module->name] = self::hasPermission($module->id, $role);

            foreach ($permissions as $permission) {
                $permission_array = explode('.', $permission->name);
                $map_key = $permission_array[1];

                $modules_permissions_map[$module->name][$map_key][] = [
                    'id'             => $permission->id,
                    'name'           => $permission->name,
                    'display_name'   => $permission->display_name,
                    'type'           => $permission->type,
                    'has_permission' => self::hasPermission($permission->id, $role),
                ];
            }
        }

        return $modules_permissions_map;
    }

    /**
     * Get the role has the permission status.
     *
     * @param int                   $permission_id
     * @param \App\Models\Role|null $role
     *
     * @return bool
     */
    public static function hasPermission($permission_id, $role = null)
    {
        $outcome = false;

        if ($role != null) {
            $has_permission = $role->permissions()->wherePermission_id($permission_id)->count();

            if ($has_permission > 0) {
                $outcome = true;
            }
        }

        return $outcome;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A many-to-many relationship with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
