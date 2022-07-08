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

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('permission_role')->truncate();

        $roles            = Role::onlyGeneral()->get();
        $permission_count = Permission::onlyGeneral()->count();
        $admin_privileges = Permission::onlyGeneral()->whereGroup('admin_level')->get(['id'])->pluck('id')->toArray();

        foreach ($roles as $role) {
            if ($role->label == 'general') {
                switch ($role->name) {
                    case 'administrator':
                        $role->permissions()->attach(range(1, $permission_count));
                        break;
                    case 'standard':
                        $role->permissions()->attach(array_diff(range(1, $permission_count), $admin_privileges));
                        break;
                    default:
                        $role->permissions()->attach(array_diff(range(1, $permission_count), $admin_privileges));
                }
            }
        }
    }
}
