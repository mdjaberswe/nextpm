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

namespace App\Http\Composers;

use Dropdown;
use DataTable;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminViewComposer
{
    /**
     * Common compose view for all views.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $class                      = get_layout_status();
        $unread_notifications_count = auth_staff()->unread_notifications_count;
        $take                       = $unread_notifications_count > 15 ? $unread_notifications_count : 15;
        $notifications              = auth_staff()->notifications->take($take);
        $unread_messages_count      = auth_staff()->unread_messages_count;
        $take_messages              = $unread_messages_count > 15 ? $unread_messages_count : 15;
        $chat_messages              = auth_staff()->getChatRoomsAttribute($take_messages);

        $view->with(compact(
            'class',
            'chat_messages',
            'notifications',
            'unread_messages_count',
            'unread_notifications_count'
        ));
    }

    /**
     * Compose view for dashboard filter form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function dashboardFilter(View $view)
    {
        $data['timeperiod_list']   = Dropdown::getTimePeriodList();
        $data['admin_users_list']  = ['0' => 'Me'] + Dropdown::getAdminUsersList();
        $data['auto_refresh_list'] = [
            '' => '-None-',
            5  => 'Update every 5 min',
            15 => 'Update every 15 min',
            30 => 'Update every 30 min',
            60 => 'Update every 1 hour',
        ];

        $view->with(compact('data'));
    }

    /**
     * Compose view for notification filter form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function notificationFilter(View $view)
    {
        $data['timeperiod_list']      = Dropdown::getTimePeriodList();
        $data['admin_users_list']     = Dropdown::getAdminUsersList([], [auth_staff()->id]);
        $related_to_list['project']   = ['' => '-None-'] + Dropdown::getArrayList('project');
        $related_to_list['task']      = ['' => '-None-'] + Dropdown::getArrayList('task');
        $related_to_list['milestone'] = ['' => '-None-'] + Dropdown::getArrayList('milestone');
        $related_to_list['issue']     = ['' => '-None-'] + Dropdown::getArrayList('issue');
        $related_to_list['event']     = ['' => '-None-'] + Dropdown::getArrayList('event');
        $related_type_list            = [
            ''          => '-None-',
            'project'   => 'Project',
            'task'      => 'Task',
            'milestone' => 'Milestone',
            'issue'     => 'Issue',
            'event'     => 'Event',
        ];

        $view->with(compact('data', 'related_type_list', 'related_to_list'));
    }

    /**
     * Compose view for announcement form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function announcementForm(View $view)
    {
        $data['admin_users_list'] = Dropdown::getAdminUsersList([], [auth_staff()->id]);

        $view->with(compact('data'));
    }

    /**
     * Compose view for user form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function userForm(View $view)
    {
        $roles_list     = Dropdown::getRolesList();
        $receivers_list = Dropdown::getAdminUsersList([], [auth_staff()->id]);

        $view->with(compact('roles_list', 'receivers_list'));
    }

    /**
     * Compose view for user show page information.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function userInformation(View $view)
    {
        $countries_list = ['' => '-None-'] + countries_list();
        $roles_list     = Dropdown::getRolesList();

        $view->with(compact('countries_list', 'roles_list'));
    }

    /**
     * Compose view for modal followers table.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function follower(View $view)
    {
        $follower_table = \App\Models\Follower::getFollowerTableFormat();

        $view->with(compact('follower_table'));
    }

    /**
     * Compose view for access modal form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function accessModal(View $view)
    {
        $admin_users_list = Dropdown::getAdminUsersList();

        $view->with(compact('admin_users_list'));
    }

    /**
     * Compose view for the common tab.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function tab(View $view)
    {
        $at_who_data      = Dropdown::atWhoData();
        $tasks_table      = \App\Models\Task::getTabTableFormat();
        $issues_table     = \App\Models\Issue::getTabTableFormat();
        $files_table      = \App\Models\AttachFile::getTableFormat();
        $events_table     = \App\Models\Event::getTableFormat(false);
        $projects_table   = \App\Models\Project::getTableFormat(false);
        $milestones_table = \App\Models\Milestone::getTabTableFormat();
        $milestones_sequence_table = \App\Models\Milestone::getTabTableFormat(true);

        $view->with(compact(
            'tasks_table',
            'issues_table',
            'projects_table',
            'milestones_table',
            'milestones_sequence_table',
            'events_table',
            'files_table',
            'at_who_data'
        ));
    }

    /**
     * Compose view for project form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function projectForm(View $view)
    {
        $field_list           = ['' => '-Select a field-'] + Dropdown::getMassFieldList('project');
        $admin_users_list     = Dropdown::getAdminUsersList();
        $access_list          = Dropdown::getAccessList();
        $gantt_filter_list    = \App\Models\Project::getGanttFilterList();
        $gantt_default_filter = \App\Models\Project::getGanttFilterParam();
        $status_list          = Dropdown::getArrayList('project_status', 'position');
        $members_json_column  = DataTable::jsonColumn(['name', 'phone', 'email', 'tasks', 'issues', 'action']);
        $members_table        = [
            'json_columns' => $members_json_column,
            'thead'        => ['USER', 'PHONE', 'EMAIL', 'TASKS', 'ISSUES'],
            'checkbox'     => false,
        ];

        $view->with(compact(
            'field_list',
            'status_list',
            'access_list',
            'members_table',
            'admin_users_list',
            'gantt_filter_list',
            'gantt_default_filter'
        ));
    }

    /**
     * Compose view for project member form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function memberForm(View $view)
    {
        $admin_users_list    = Dropdown::getAdminUsersList();
        $project_permissions = \App\Models\Project::getPermissionsList();
        $fixed_modules       = \App\Models\Project::getFixedModulesList();
        $fixed_permissions   = \App\Models\Project::getFixedPermissionsList();

        $view->with(compact('admin_users_list', 'project_permissions', 'fixed_modules', 'fixed_permissions'));
    }

    /**
     * Compose view for project status form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function projectStatusForm(View $view)
    {
        $max_position    = \App\Models\ProjectStatus::max('position');
        $max_position_id = isset($max_position) ? \App\Models\ProjectStatus::wherePosition($max_position)->first()->id : 0;
        $category_list   = ['open' => 'Open', 'closed' => 'Closed'];
        $position_list   = [0 => 'AT TOP'] + Dropdown::getArrayList('project_status', 'position', ['id', 'position_after_name']);

        $view->with(compact('position_list', 'max_position_id', 'category_list'));
    }

    /**
     * Compose view for milestone form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function milestoneForm(View $view)
    {
        $access_list      = Dropdown::getAccessList();
        $admin_users_list = Dropdown::getAdminUsersList();
        $projects_list    = ['' => '-None-'] + Dropdown::getPermittedList('project', 'milestone');

        $view->with(compact('admin_users_list', 'projects_list', 'access_list'));
    }

    /**
     * Compose view for task form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function taskForm(View $view)
    {
        $field_list        = ['' => '-Select a field-'] + Dropdown::getMassFieldList('task');
        $task_owner_list   = Dropdown::getAdminUsersList(['' => '-None-']);
        $admin_users_list  = Dropdown::getAdminUsersList();
        $access_list       = Dropdown::getAccessList();
        $priority_list     = Dropdown::getPriorityList(['' => '-None-']);
        $status_list       = \App\Models\TaskStatus::getOptionsHtml();
        $status_plain_list = Dropdown::getArrayList('task_status', 'position');
        $milestones_list   = ['' => '-None-'] + Dropdown::getArrayList('milestone');
        $related_type_list = ['' => '-None-', 'project' => 'Project'];
        $related_to_list['project'] = ['' => '-None-'] + Dropdown::getPermittedList('project', 'task');

        $view->with(compact(
            'field_list',
            'access_list',
            'status_list',
            'priority_list',
            'task_owner_list',
            'milestones_list',
            'related_to_list',
            'related_type_list',
            'admin_users_list',
            'status_plain_list'
        ));
    }

    /**
     * Compose view for task status form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function taskStatusForm(View $view)
    {
        $max_position    = \App\Models\TaskStatus::max('position');
        $max_position_id = isset($max_position) ? \App\Models\TaskStatus::wherePosition($max_position)->first()->id : 0;
        $category_list   = ['open' => 'Open', 'closed' => 'Closed'];
        $position_list   = [0 => 'AT TOP'] + Dropdown::getArrayList('task_status', 'position', ['id', 'position_after_name']);

        $view->with(compact('position_list', 'max_position_id', 'category_list'));
    }

    /**
     * Compose view for issue status form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function issueStatusForm(View $view)
    {
        $max_position    = \App\Models\IssueStatus::max('position');
        $max_position_id = isset($max_position) ? \App\Models\IssueStatus::wherePosition($max_position)->first()->id : 0;
        $category_list   = ['open' => 'Open', 'closed' => 'Closed'];
        $position_list   = [0 => 'AT TOP'] + Dropdown::getArrayList('issue_status', 'position', ['id', 'position_after_name']);

        $view->with(compact('position_list', 'max_position_id', 'category_list'));
    }

    /**
     * Compose view for issue form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function issueForm(View $view)
    {
        $field_list        = ['' => '-Select a field-'] + Dropdown::getMassFieldList('issue');
        $issue_owner_list  = Dropdown::getAdminUsersList(['' => '-None-']);
        $admin_users_list  = Dropdown::getAdminUsersList();
        $access_list       = Dropdown::getAccessList();
        $status_list       = Dropdown::getArrayList('issue_status', 'position');
        $milestones_list   = ['' => '-None-'] + Dropdown::getArrayList('milestone');
        $severity_list     = \App\Models\Issue::getSeverityDropdownList();
        $reproducible_list = \App\Models\Issue::getReproducibleDropdownList();
        $types_list        = ['' => '-None-'] + Dropdown::getArrayList('issue_type', 'position');
        $related_type_list = ['' => '-None-', 'project' => 'Project'];
        $related_to_list['project'] = ['' => '-None-'] + Dropdown::getPermittedList('project', 'issue');

        $view->with(compact(
            'field_list',
            'types_list',
            'status_list',
            'access_list',
            'severity_list',
            'milestones_list',
            'related_to_list',
            'issue_owner_list',
            'admin_users_list',
            'reproducible_list',
            'related_type_list'
        ));
    }

    /**
     * Compose view for issue type form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function issueTypeForm(View $view)
    {
        $max_position    = \App\Models\IssueType::max('position');
        $max_position_id = isset($max_position) ? \App\Models\IssueType::wherePosition($max_position)->first()->id : 0;
        $position_list   = [0 => 'AT TOP'] + Dropdown::getArrayList('issue_type', 'position', ['id', 'position_after_name']);

        $view->with(compact('position_list', 'max_position_id'));
    }

    /**
     * Compose view for event form.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function eventForm(View $view)
    {
        $field_list        = ['' => '-Select a field-'] + Dropdown::getMassFieldList('event');
        $admin_users_list  = Dropdown::getAdminUsersList();
        $access_list       = Dropdown::getAccessList();
        $priority_list     = Dropdown::getPriorityList(['' => '-None-']);
        $attendees_list    = Dropdown::getArrayList('staff', 'id', ['id_type', 'name']);
        $start_date        = Carbon::now()->setTime(10, 0)->format('Y-m-d h:i A');
        $end_date          = Carbon::now()->setTime(11, 0)->format('Y-m-d h:i A');
        $related_type_list = ['' => '-None-', 'project' => 'Project'];
        $related_to_list['project'] = ['' => '-None-'] + Dropdown::getPermittedList('project', 'event');

        $view->with(compact(
            'field_list',
            'access_list',
            'start_date',
            'end_date',
            'priority_list',
            'attendees_list',
            'admin_users_list',
            'related_to_list',
            'related_type_list'
        ));
    }

    /**
     * Compose view for modal event attendees table.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function eventAttendee(View $view)
    {
        $attendees_table = \App\Models\Event::getAttendeeTableFormat();
        $attendees_list  = Dropdown::getArrayList('staff', 'id', ['id_type', 'name']);

        $view->with(compact('attendees_table', 'attendees_list'));
    }

    /**
     * Compose view for setting general information.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function settingGeneralForm(View $view)
    {
        $time_zones_list = Dropdown::getTimeZonesList(['' => '-None-']);

        $view->with(compact('time_zones_list'));
    }
}
