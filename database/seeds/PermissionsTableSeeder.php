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
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::truncate();

        $permissions = [
            'module.dashboard'        => ['Dashboard Module', 'open', 'general', 'basic'],
            'module.project'          => ['Project Module', 'open', 'general', 'basic'],
            'module.task'             => ['Task Module', 'open', 'general', 'basic'],
            'module.issue'            => ['Issue Module', 'open', 'general', 'basic'],
            'module.milestone'        => ['Milestone Module', 'open', 'general', 'basic'],
            'module.event'            => ['Event Module', 'open', 'general', 'basic'],
            'module.note'             => ['Note', 'open', 'general', 'basic'],
            'module.attachment'       => ['Attachment', 'open', 'general', 'basic'],

            // Dashboard Permissions
            'dashboard.view'          => ['View Dashboard', 'open', 'general', 'basic'],

            // Project Permissions
            'project.view'            => ['View Project List', 'open', 'general', 'basic'],
            'project.create'          => ['Create New Project', 'open', 'general', 'basic'],
            'project.edit'            => ['Edit Project', 'open', 'general', 'basic'],
            'project.delete'          => ['Delete Project', 'open', 'general', 'basic'],

            // Task Permissions
            'task.view'               => ['View Task List', 'open', 'general', 'basic'],
            'task.create'             => ['Create New Task', 'open', 'general', 'basic'],
            'task.edit'               => ['Edit Task', 'open', 'general', 'basic'],
            'task.delete'             => ['Delete Task', 'open', 'general', 'basic'],

            // Issue Permissions
            'issue.view'              => ['View Issue List', 'open', 'general', 'basic'],
            'issue.create'            => ['Create New Issue', 'open', 'general', 'basic'],
            'issue.edit'              => ['Edit Issue', 'open', 'general', 'basic'],
            'issue.delete'            => ['Delete Issue', 'open', 'general', 'basic'],

            // Milestone Permissions
            'milestone.view'          => ['View Milestone List', 'open', 'general', 'basic'],
            'milestone.create'        => ['Create New Milestone', 'open', 'general', 'basic'],
            'milestone.edit'          => ['Edit Milestone', 'open', 'general', 'basic'],
            'milestone.delete'        => ['Delete Milestone', 'open', 'general', 'basic'],

            // Event Permissions
            'event.view'              => ['View Event List', 'open', 'general', 'basic'],
            'event.create'            => ['Create New Event', 'open', 'general', 'basic'],
            'event.edit'              => ['Edit Event', 'open', 'general', 'basic'],
            'event.delete'            => ['Delete Event', 'open', 'general', 'basic'],

            // Note Permissions
            'note.view'               => ['View Note', 'open', 'general', 'basic'],
            'note.create'             => ['Create New Note', 'open', 'general', 'basic'],
            'note.edit'               => ['Edit Note', 'open', 'general', 'basic'],
            'note.delete'             => ['Delete Note', 'open', 'general', 'basic'],

            // Attachment Permissions
            'attachment.view'         => ['View File', 'open', 'general', 'basic'],
            'attachment.create'       => ['Create New File', 'open', 'general', 'basic'],
            'attachment.delete'       => ['Delete File', 'open', 'general', 'basic'],

            // Tool Modules
            'module.mass_update'      => ['Mass update tool module', 'open', 'general', 'tool'],
            'module.mass_delete'      => ['Mass delete tool module', 'open', 'general', 'tool'],
            'module.change_owner'     => ['Change owner tool module', 'open', 'general', 'tool'],

            // Mass Update Permissions
            'mass_update.project'     => ['Mass update projects', 'open', 'general', 'tool'],
            'mass_update.task'        => ['Mass update tasks', 'open', 'general', 'tool'],
            'mass_update.issue'       => ['Mass update issues', 'open', 'general', 'tool'],
            'mass_update.event'       => ['Mass update events', 'open', 'general', 'tool'],

            // Mass Delete Permissions
            'mass_delete.project'     => ['Mass delete projects', 'open', 'general', 'tool'],
            'mass_delete.task'        => ['Mass delete tasks', 'open', 'general', 'tool'],
            'mass_delete.issue'       => ['Mass delete issues', 'open', 'general', 'tool'],
            'mass_delete.event'       => ['Mass delete events', 'open', 'general', 'tool'],
            'mass_delete.user'        => ['Mass delete users', 'open', 'general', 'tool'],
            'mass_delete.role'        => ['Mass delete roles', 'open', 'general', 'tool'],

            // Change Owner Permissions
            'change_owner.project'    => ['Change project owner', 'open', 'general', 'tool'],
            'change_owner.task'       => ['Change task owner', 'open', 'general', 'tool'],
            'change_owner.issue'      => ['Change issue owner', 'open', 'general', 'tool'],
            'change_owner.milestone'  => ['Change milestone owner', 'open', 'general', 'tool'],
            'change_owner.event'      => ['Change event owner', 'open', 'general', 'tool'],

            // Import Export Modules
            'module.import'           => ['Import module', 'open', 'general', 'import_export'],
            'module.export'           => ['Export module', 'open', 'general', 'import_export'],

            // Import Permissions
            'import.project'          => ['Import projects', 'open', 'general', 'import_export'],
            'import.task'             => ['Import tasks', 'open', 'general', 'import_export'],
            'import.issue'            => ['Import issues', 'open', 'general', 'import_export'],
            'import.event'            => ['Import events', 'open', 'general', 'import_export'],

            // Export Permissions
            'export.project'          => ['Export projects', 'open', 'general', 'import_export'],
            'export.task'             => ['Export tasks', 'open', 'general', 'import_export'],
            'export.issue'            => ['Export issues', 'open', 'general', 'import_export'],
            'export.event'            => ['Export events', 'open', 'general', 'import_export'],

            // Admin Level Modules
            'module.settings'         => ['Settings Module', 'open', 'general', 'admin_level'],
            'module.custom_dropdowns' => ['Dropdown Module', 'open', 'general', 'admin_level'],
            'module.user'             => ['User Module', 'open', 'general', 'admin_level'],
            'module.role'             => ['Role Module', 'open', 'general', 'admin_level'],

            // Settings Sub-Modules
            'settings.general'        => ['General Setting', 'open', 'general', 'admin_level'],
            'settings.email'          => ['Email Setting', 'open', 'general', 'admin_level'],

            // Custom Dropdowns Sub-Modules
            'custom_dropdowns.project_status.view'   => ['View Project Status List', 'open', 'general', 'admin_level'],
            'custom_dropdowns.project_status.create' => ['Create New Project Status', 'open', 'general', 'admin_level'],
            'custom_dropdowns.project_status.edit'   => ['Edit Project Status', 'open', 'general', 'admin_level'],
            'custom_dropdowns.project_status.delete' => ['Delete Project Status', 'open', 'general', 'admin_level'],

            'custom_dropdowns.task_status.view'      => ['View Task Status List', 'open', 'general', 'admin_level'],
            'custom_dropdowns.task_status.create'    => ['Create New Task Status', 'open', 'general', 'admin_level'],
            'custom_dropdowns.task_status.edit'      => ['Edit Task Status', 'open', 'general', 'admin_level'],
            'custom_dropdowns.task_status.delete'    => ['Delete Task Status', 'open', 'general', 'admin_level'],

            'custom_dropdowns.issue_status.view'     => ['View Issue Status List', 'open', 'general', 'admin_level'],
            'custom_dropdowns.issue_status.create'   => ['Create New Issue Status', 'open', 'general', 'admin_level'],
            'custom_dropdowns.issue_status.edit'     => ['Edit Issue Status', 'open', 'general', 'admin_level'],
            'custom_dropdowns.issue_status.delete'   => ['Delete Issue Status', 'open', 'general', 'admin_level'],

            'custom_dropdowns.issue_type.view'       => ['View Issue Type List', 'open', 'general', 'admin_level'],
            'custom_dropdowns.issue_type.create'     => ['Create New Issue Type', 'open', 'general', 'admin_level'],
            'custom_dropdowns.issue_type.edit'       => ['Edit Issue Type', 'open', 'general', 'admin_level'],
            'custom_dropdowns.issue_type.delete'     => ['Delete Issue Type', 'open', 'general', 'admin_level'],

            // User Permissions
            'user.view'   => ['View User List', 'open', 'general', 'admin_level'],
            'user.create' => ['Create New User', 'preserve', 'general', 'admin_level'],
            'user.edit'   => ['Edit User except login credentials & role', 'semi_preserve', 'general', 'admin_level'],
            'user.delete' => ['Delete User', 'preserve', 'general', 'admin_level'],

            // Role Permissions
            'role.view'   => ['View Role List', 'open', 'general', 'admin_level'],
            'role.create' => ['Create New Role', 'preserve', 'general', 'admin_level'],
            'role.edit'   => ['Edit Role', 'preserve', 'general', 'admin_level'],
            'role.delete' => ['Delete Role', 'preserve', 'general', 'admin_level'],
        ];

        foreach ($permissions as $permission => $info) {
            if ($last_pos = strrpos($permission, '.')) {
                $display_name = substr($permission, $last_pos + 1);
                $display_name = str_replace('_', ' ', $display_name);
                $display_name = ucfirst($display_name);
            } else {
                $display_name = ucfirst($permission);
            }

            $new_permission               = new Permission;
            $new_permission->name         = $permission;
            $new_permission->display_name = $display_name;
            $new_permission->description  = $info[0];
            $new_permission->type         = $info[1];
            $new_permission->label        = $info[2];
            $new_permission->group        = $info[3];
            $new_permission->save();
        }
    }
}
