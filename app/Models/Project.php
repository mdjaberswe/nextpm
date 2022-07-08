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

use App\Models\Traits\OwnerTrait;
use App\Models\Traits\ChartTrait;
use App\Models\Traits\KanbanTrait;
use App\Models\Traits\CalendarTrait;
use App\Models\Traits\PosionableTrait;
use App\Models\Traits\ParentModuleTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Project extends BaseModel
{
    use SoftDeletes;
    use OwnerTrait;
    use ChartTrait;
    use KanbanTrait;
    use CalendarTrait;
    use PosionableTrait;
    use ParentModuleTrait;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'project_owner', 'project_status_id', 'start_date', 'end_date', 'access', 'position',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['auth_can_view', 'auth_can_edit', 'auth_can_change_owner', 'auth_can_delete'];

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
    protected $dontKeepRevisionOf = ['position'];

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFields = [
        'project_status_id' => 'database:project_status|id|name',
        'project_owner'     => 'database:staff|id|name',
        'access'            => 'helper:readable_access',
    ];

    /**
     * Fields list array where the index is field's name and corresponding value as field's display name.
     *
     * @var array
     */
    protected static $fieldlist = [
        'access'            => 'Access',
        'description'       => 'Description',
        'end_date'          => 'End Date',
        'name'              => 'Project Name',
        'project_owner'     => 'Project Owner',
        'member'            => 'Project Members',
        'project_status_id' => 'Project Status',
        'start_date'        => 'Start Date',
    ];

    /**
     * Fields name array that can be mass updatable.
     *
     * @var array
     */
    protected static $mass_fieldlist = [
        'access', 'description', 'end_date', 'name', 'project_owner', 'project_status_id', 'start_date',
    ];

    /**
     * Fields name array that uses to filter data.
     *
     * @var array
     */
    protected static $filter_fieldlist = [
        'access', 'description', 'end_date', 'name', 'project_owner', 'member', 'project_status_id', 'start_date',
    ];

    /**
     * Project form validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        $after_date = '';

        // The end date can not be earlier than the start date.
        if (not_null_empty($data['start_date'])) {
            $after_date = 'after:' . date('Y-m-d', strtotime($data['start_date'] . ' -1 day'));
        }

        $rules = [
            'name'              => 'required|max:200',
            'project_owner'     => 'required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'        => 'date',
            'end_date'          => 'date|' . $after_date,
            'description'       => 'max:65535',
            'access'            => 'required|in:private,public,public_rwd',
            'project_status_id' => 'required|exists:project_status,id,deleted_at,NULL',
        ];

        return validator($data, $rules);
    }

    /**
     * Single field update validation.
     *
     * @param array                    $data
     * @param \App\Models\Project|null $project
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function singleValidate($data, $project = null)
    {
        $message    = [];
        $after_date = '';

        // The end date can not be earlier than the start date.
        if (array_key_exists('end_date', $data)
            && ! empty($data['end_date'])
            && ! is_null($project)
            && not_null_empty($project->start_date)
        ) {
            $after_date = 'after:' . date('Y-m-d', strtotime($project->start_date . ' -1 day'));
        }

        $rules = [
            'name'              => 'sometimes|required|max:200',
            'project_owner'     => 'sometimes|required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'        => 'date',
            'end_date'          => 'date|' . $after_date,
            'description'       => 'max:65535',
            'access'            => 'sometimes|required|in:private,public,public_rwd',
            'project_status_id' => 'sometimes|required|exists:project_status,id,deleted_at,NULL',
        ];

        // The auth user can only change the owner if the auth user has "Change Owner" permission of the specified project.
        if (array_key_exists('change_owner', $data) && $data['change_owner']
            && isset($project)
            && ! $project->auth_can_change_owner
        ) {
            $data['project_owner'] = 0;
            $message['project_owner.exists'] = 'You don\'t have permission to change owner';
        }

        return validator($data, $rules, $message);
    }

    /**
     * Mass update validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function massValidate($data)
    {
        return validator($data, [
            'start_date'        => 'date',
            'end_date'          => 'date',
            'description'       => 'max:65535',
            'related'           => 'required|in:' . implode(',', self::massfieldlist()),
            'name'              => 'required_if:related,name|max:200',
            'access'            => 'required_if:related,access|in:private,public,public_rwd',
            'project_status_id' => 'required_if:related,project_status_id|exists:project_status,id,deleted_at,NULL',
            'project_owner'     => 'required_if:related,project_owner|' .
                                   'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
        ]);
    }

    /**
     * Resource data filter params validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function filterValidate($data)
    {
        $users  = User::onlyStaff()->where('status', 1)->pluck('linked_id')->toArray();
        $fields = [
            [
                'name'      => 'name',
                'type'      => 'string',
                'condition' => 'required|array|max:200',
            ],
            [
                'name'      => 'project_owner',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:0,' . implode(',', $users),
            ],
            [
                'name'      => 'member',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:0,' . implode(',', $users),
            ],
            [
                'name'      => 'project_status_id',
                'type'      => 'dropdown',
                'condition' => 'required|exists:project_status,id,deleted_at,NULL',
            ],
            [
                'name'      => 'access',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:private,public,public_rwd',
            ],
            [
                'name'      => 'description',
                'type'      => 'string',
                'condition' => 'required|array|max:65535',
            ],
            [
                'name'      => 'start_date',
                'type'      => 'date',
                'condition' => 'required|in:7,30,90',
            ],
            [
                'name'      => 'end_date',
                'type'      => 'date',
                'condition' => 'required|in:7,30,90',
            ],
        ];

        $rules = FilterView::filterRulesGenerator($data, $fields);

        return validator($data, $rules);
    }

    /**
     * Project member validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function memberValidate($data)
    {
        return validator($data, [
            'members' => 'required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
        ]);
    }

    /**
     * Get project ids array where the auth user has the activity permission.
     *
     * @param string $activity
     * @param string $permission
     *
     * @return array
     */
    public static function getAuthPermittedIds(string $activity, string $permission = 'create')
    {
        if (! permit($activity . '.' . $permission)) {
            return [];
        }

        if (auth_staff()->admin) {
            return self::orderBy('id')->pluck('id')->toArray();
        }

        // Permitted if satisfied one of the following conditions:
        // The auth user is the owner
        // The auth user is the creator
        // The auth user is a member and has this $permission
        $ids = self::where('project_owner', auth_staff()->id)
                   ->leftjoin('revisions', 'projects.id', '=', 'revisions.revisionable_id')
                   ->leftjoin('project_member', 'projects.id', '=', 'project_member.project_id')
                   ->orWhere(function ($query) {
                        $query->whereRevisionable_type('project')
                              ->whereUser_id(auth()->user()->id)
                              ->wherekey('created_at');
                   })->orWhere(function ($query) use ($activity, $permission) {
                        $query->where('project_member.staff_id', auth_staff()->id)
                              ->where('project_member.' . $activity . '_' . $permission, 1);
                   })->select('projects.*')->groupBy('projects.id')->pluck('projects.id')->toArray();

        return $ids;
    }

    /**
     * Get the auth user permitted project data.
     *
     * @param string $activity
     * @param string $permission
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getAuthPermittedData(string $activity, string $permission = 'create')
    {
        return self::whereIn('id', self::getAuthPermittedIds($activity, $permission));
    }

    /**
     * Get to know the auth user can edit the project member.
     *
     * @param \App\Models\Staff $member
     *
     * @return bool
     */
    public function authCanEditMember($member)
    {
        // The auth user can not edit the project member if the member is admin or creator or owner.
        if ($member->admin
            || $member->id == $this->created_by
            || $member->id == $this->project_owner
        ) {
            return false;
        }

        return $this->authCanDo('member_edit', 'local');
    }

    /**
     * Get to know the auth user can delete the project member.
     *
     * @param \App\Models\Staff $member
     *
     * @return bool
     */
    public function authCanDeleteMember($member)
    {
        // Case: If the member is the owner then can be deletable
        // if the auth user has "change owner" permission and
        // the auth user is admin or creator but not the owner.
        if ($member->id == $this->project_owner) {
            if (! permit('change_owner.project')) {
                return false;
            }

            if ((auth_staff()->admin || $this->auth_is_creator) && ! $this->auth_is_owner) {
                return true;
            } else {
                return false;
            }
        }

        return $this->authCanDo('member_delete', 'local');
    }

    /**
     * Get to know the auth user can view the specified project.
     *
     * @return bool
     */
    public function getAuthCanViewAttribute()
    {
        if ($this->authCanDo('project.view')) {
            return true;
        }

        return $this->authCan('view');
    }

    /**
     * Get to know the auth user can edit the specified project.
     *
     * @return bool
     */
    public function getAuthCanEditAttribute()
    {
        if ($this->authCanDo('project.edit')) {
            return true;
        }

        return $this->authCan('edit');
    }

    /**
     * Get to know the auth user can delete the specified project.
     *
     * @return bool
     */
    public function getAuthCanDeleteAttribute()
    {
        if ($this->authCanDo('project.delete')) {
            return true;
        }

        return $this->authCan('delete');
    }

    /**
     * The auth user activity permission for the project.
     *
     * @param string $can_permission
     * @param string $permission_level
     *
     * @return boolean
     */
    public function authCanDo($can_permission, $permission_level = 'global')
    {
        $global_permission = str_replace('_', '.', $can_permission);
        $local_permission  = str_replace('.', '_', $can_permission);

        if ($permission_level == 'global' && ! permit($global_permission)) {
            return false;
        }

        // Permitted if the auth user is admin or owner or creator.
        if (auth_staff()->admin || $this->auth_is_owner || $this->auth_is_creator) {
            return true;
        }

        // Permitted if the auth member has this $can_permission.
        if ($this->authMember()->count()) {
            return $this->authMember()->first()->pivot->$local_permission;
        }

        return false;
    }

    /**
     * Get user has at least one action permission status.
     * Enable action column for all projects to ensure to show every single project has a different permissions setup.
     *
     * @return bool
     */
    public static function allowAction()
    {
        return true;
    }

    /**
     * Get the auth user as the project member.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authMember()
    {
        return $this->members()->where('staff_id', auth_staff()->id)->get();
    }

    /**
     * Get project permissions array list.
     *
     * @return array
     */
    public static function getPermissionsList()
    {
        $permissions = [
            'project' => [
                'project_view'   => 'View',
                'project_edit'   => 'Edit',
                'project_delete' => 'Delete',
            ],
            'member' => [
                'member_view'   => 'View',
                'member_create' => 'Create',
                'member_edit'   => 'Edit',
                'member_delete' => 'Delete',
            ],
            'milestone' => [
                'milestone_view'   => 'View',
                'milestone_create' => 'Create',
                'milestone_edit'   => 'Edit',
                'milestone_delete' => 'Delete',
            ],
            'task' => [
                'task_view'   => 'View',
                'task_create' => 'Create',
                'task_edit'   => 'Edit',
                'task_delete' => 'Delete',
            ],
            'issue' => [
                'issue_view'   => 'View',
                'issue_create' => 'Create',
                'issue_edit'   => 'Edit',
                'issue_delete' => 'Delete',
            ],
            'event' => [
                'event_view'   => 'View',
                'event_create' => 'Create',
                'event_edit'   => 'Edit',
                'event_delete' => 'Delete',
            ],
            'note' => [
                'note_view'   => 'View',
                'note_create' => 'Create',
                'note_edit'   => 'Edit',
                'note_delete' => 'Delete',
            ],
            'attachment' => [
                'attachment_view'   => 'View',
                'attachment_create' => 'Create',
                'attachment_delete' => 'Delete',
            ],
            'gantt'   => 'Gantt',
            'report'  => 'Report',
            'history' => 'History',
        ];

        return $permissions;
    }

    /**
     * Get project minimal permitted modules.
     *
     * @return array
     */
    public static function getFixedModulesList()
    {
        return ['project', 'milestone', 'task', 'issue', 'event'];
    }

    /**
     * Get project minimal permitted permissions.
     *
     * @return array
     */
    public static function getFixedPermissionsList()
    {
        return ['project_view', 'milestone_view', 'task_view', 'issue_view', 'event_view'];
    }

    /**
     * Get project permissions array.
     *
     * @return array
     */
    public static function getPermissionArray()
    {
        return array_collapse(self::getPermissionsList()) + [
            'gantt'   => 'Gantt',
            'report'  => 'Report',
            'history' => 'History',
        ];
    }

    /**
     * Get project permission keys.
     *
     * @return array
     */
    public static function getPermissionKeys()
    {
        return array_keys(self::getPermissionArray());
    }

    /**
     * Get project permissions that are all granted.
     *
     * @return array
     */
    public static function getAllPermissions()
    {
        return array_fill_keys(self::getPermissionKeys(), 1);
    }

    /**
     * Get minimal permissions for a project.
     *
     * @return array
     */
    public static function getMinimalPermissions()
    {
        // Minimal permissions of a project member.
        $minimal_permissions = array_filter(self::getPermissionKeys(), function ($permission) {
            return strpos($permission, 'view') !== false || in_array($permission, ['gantt', 'report', 'history']);
        });

        return array_fill_keys($minimal_permissions, 1);
    }

    /**
     * Get Gantt filter names array.
     *
     * @return array
     */
    public static function getGanttFilterList()
    {
        return [
            'acts'         => 'All Activities',
            'open_act'     => 'Open Activities',
            'closed_act'   => 'Closed Activities',
            'tasks'        => 'All Tasks',
            'open_task'    => 'Open Tasks',
            'closed_task'  => 'Closed Tasks',
            'issues'       => 'All Issues',
            'open_issue'   => 'Open Issues',
            'closed_issue' => 'Closed Issues',
        ];
    }

    /**
     * Get Gantt filter param name.
     *
     * @return string
     */
    public static function getGanttFilterParam()
    {
        $default = 'acts';

        if (session()->has('gantt_filter')
            && array_key_exists(session('gantt_filter'), self::getGanttFilterList())
        ) {
            return session('gantt_filter');
        }

        return $default;
    }

    /**
     * Get show page tab array list.
     *
     * @param \App\Models\Project|null $project
     *
     * @return array
     */
    public static function informationTypes($project = null)
    {
        $information_types = [
            'overview'     => 'Overview',
            'tasks'        => 'Tasks',
            'taskskanban'  => ['display' => 'Tasks Kanban', 'nav' => 0, 'parent' => 'tasks'],
            'issues'       => 'Issues',
            'issueskanban' => ['display' => 'Issues Kanban', 'nav' => 0, 'parent' => 'issues'],
            'milestones'   => 'Milestones',
            'calendar'     => 'Calendar',
            'events'       => ['display' => 'Events', 'nav' => 0, 'parent' => 'calendar'],
            'notes'        => 'Notes',
            'files'        => 'Files',
            'gantt'        => 'Gantt',
            'reports'      => 'Reports',
            'history'      => 'History',
        ];

        // If the auth user doesn't have permission to view "Task" then remove the task tab.
        // By default project member has task view permission.
        if (! permit('task.view')) {
            array_forget($information_types, 'tasks');
            array_forget($information_types, 'taskskanban');
        }

        // If the auth user doesn't have permission to view "Issue" then remove the issue tab.
        // By default project member has issue view permission.
        if (! permit('issue.view')) {
            array_forget($information_types, 'issues');
            array_forget($information_types, 'issueskanban');
        }

        // If the auth user doesn't have permission to view "Milestone" then remove the milestone tab.
        // By default project member has milestone view permission.
        if (! permit('milestone.view')) {
            array_forget($information_types, 'milestones');
        }

        // If the auth user doesn't have permission to view "Event" then remove the event tab.
        // By default project member has event view permission.
        if (! permit('event.view')) {
            array_forget($information_types, 'events');
        }

        // If the auth user doesn't have permission to view Milestone|Task|Issue|Event then remove the calendar tab.
        // By default project member has calendar view permission.
        if (! (permit('milestone.view') || permit('task.view') || permit('issue.view') || permit('event.view'))) {
            array_forget($information_types, 'calendar');
        }

        // If the auth user doesn't have permission to view "Note" then remove the note tab.
        // By default project member has note view permission.
        if (! permit('note.view')) {
            array_forget($information_types, 'notes');
        }

        // if user don't have permission to view "Files" then remove the file tab.
        // By default project member has file view permission.
        if (! permit('attachment.view')) {
            array_forget($information_types, 'files');
        }

        // If project access is private so that only member is allowed not public|external users.
        if (! is_null($project) && $project->access == 'private') {
            // If the auth member doesn't have note view permission then remove the note tab.
            if (! $project->authCanDo('note.view')) {
                array_forget($information_types, 'notes');
            }

            // If the auth member doesn't have file view permission then remove the file tab.
            if (! $project->authCanDo('attachment.view')) {
                array_forget($information_types, 'files');
            }

            // If the auth member doesn't have gantt view permission then remove the gantt tab.
            if (! $project->authCanDo('gantt', 'local')) {
                array_forget($information_types, 'gantt');
            }

            // If the auth member doesn't have report view permission then remove the report tab.
            if (! $project->authCanDo('report', 'local')) {
                array_forget($information_types, 'reports');
            }

            // If the auth member doesn't have history view permission then remove the history tab.
            if (! $project->authCanDo('history', 'local')) {
                array_forget($information_types, 'history');
            }
        }

        return $information_types;
    }

    /**
     * Get resource data table format.
     *
     * @param bool $allow_mass_action
     *
     * @return array
     */
    public static function getTableFormat($allow_mass_action = true)
    {
        $allow_mass_action = $allow_mass_action ? self::allowMassAction() : $allow_mass_action;
        $json_columns = [
            'name', 'completion_percentage', 'tasks', 'issues', 'milestones',
            'start_date', 'end_date', 'member', 'action',
        ];

        $thead = [
            'project name', 'progress', 'tasks', 'issues', 'milestones',
            'start date', 'end date', ['members', 'style' => 'min-width: 120px'],
        ];

        if ($allow_mass_action) {
            array_unshift($json_columns, 'checkbox');
        }

        return [
            'thead'        => $thead,
            'json_columns' => \DataTable::jsonColumn($json_columns, self::hideColumns()),
            'checkbox'     => $allow_mass_action,
            'action'       => self::allowAction(),
        ];
    }

    /**
     * Get resource table data.
     *
     * @param \App\Models\Project      $projects
     * @param \Illuminate\Http\Request $request
     * @param bool                     $common_action
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($projects, $request, $common_action = false)
    {
        return \DataTable::of($projects)->addColumn('checkbox', function ($project) {
            return $project->checkbox_html;
        })->editColumn('name', function ($project) {
            return $project->name_html;
        })->editColumn('completion_percentage', function ($project) {
            return $project->completion_percentage_html;
        })->addColumn('member', function ($project) {
            return $project->members_html;
        })->addColumn('tasks', function ($project) {
            return $project->task_stat_html;
        })->addColumn('issues', function ($project) {
            return $project->issue_stat_html;
        })->addColumn('milestones', function ($project) {
            return $project->milestone_stat_html;
        })->editColumn('start_date', function ($project) {
            return $project->start_date_html;
        })->editColumn('end_date', function ($project) {
            return $project->end_date_html;
        })->addColumn('action', function ($project) use ($common_action) {
            return $project->getActionHtml('Project', 'admin.project.destroy', null, [
                'edit'   => $project->auth_can_edit,
                'delete' => $project->auth_can_delete,
            ], $common_action);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->globalSearch($request, [
                    'name', 'completion_percentage_html', 'task_stat_html', 'milestone_stat_html',
                    'issue_stat_html', 'start_date_html', 'end_date_html', 'owner_name',
                ]);
            });
        })->make(true);
    }

    /**
     * Get member table data.
     *
     * @param \Illuminate\Http\Request $request
     * @param bool|null                $view_only
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberData($request, $view_only = null)
    {
        $members = $this->members()
                        ->orderBy('pivot_created_at')
                        ->groupBy('pivot_staff_id')
                        ->get()
                        ->sortByDesc(function ($member, $key) {
                            if ($member->project_admin) {
                                return 3;
                            } elseif ($member->auth_member) {
                                return 1;
                            } else {
                                return 0;
                            }
                        });

        $data = \DataTable::of($members)->addColumn('name', function ($member) {
            return $member->project_member_html;
        })->addColumn('email', function ($member) {
            return $member->email;
        })->editColumn('phone', function ($member) {
            return $member->phone;
        })->addColumn('tasks', function ($member) {
            return $member->project_tasks_bar;
        })->addColumn('issues', function ($member) {
            return $member->project_issues_bar;
        });

        // If not for only view data but can do some actions then add actions column.
        if (! isset($view_only)) {
            $project = $this;
            $data = $data->addColumn('action', function ($member) use ($project) {
                $permissions = [
                    'edit'   => $project->authCanEditMember($member),
                    'delete' => $project->authCanDeleteMember($member),
                ];

                return $member->getMemberActionHtml('Member', 'admin.member.destroy', null, $permissions, true);
            });
        }

        return $data->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->globalSearch($request, [
                    'name', 'email', 'phone', 'project_tasks_bar', 'project_issues_bar',
                ]);
            });
        })->make(true);
    }

    /**
     * Get the project milestone table format.
     *
     * @return array
     */
    public function getMilestoneTabTableFormat()
    {
        // The auth user is allowed to have an action column if has any one of those following permissions
        // milestone edit or milestone delete or task create or issue create.
        $allow_action = $this->authCanDo('milestone_edit')
                        || $this->authCanDo('milestone_delete')
                        || $this->authCanDo('task_create')
                        || $this->authCanDo('issue_create');

        // Don't need to do mass action that's why remove the mass checkbox select column.
        $hide_columns = ['checkbox'];

        // If the auth user does not have any action permission, then remove the action column.
        if (! $allow_action) {
            array_push($hide_columns, 'action');
        }

        // If the auth user does not have milestone edit permission,
        // then remove the drag-drop reorder position sequence column.
        if (! $this->authCanDo('milestone_edit')) {
            array_push($hide_columns, 'sequence');
        }

        $thead   = ['name', 'progress', 'tasks', 'issues', 'start date', 'end date', 'owner'];
        $columns = [
            'sequence' => ['className' => 'reorder'], 'name', 'completion_percentage', 'tasks', 'issues',
            'start_date', 'end_date', 'milestone_owner', 'action',
        ];

        return [
            'thead'        => $thead,
            'columns'      => $columns,
            'json_columns' => \DataTable::jsonColumn($columns, $hide_columns),
            'action'       => $allow_action,
            'checkbox'     => false,
        ];
    }

    /**
     * Get the project activities gantt data.
     *
     * @return array
     */
    public function getGanttData()
    {
        $data       = [];
        $filter     = self::getGanttFilterParam();
        $milestones = $this->milestones()->authViewData()->filterMask()->orderBy('milestones.position')->get();

        if ($milestones->count()) {
            $m = 0;
            $non_milestone_activities = $this->getActivitiesAttribute($filter, true)
                                             ->sortBy('due_date')
                                             ->sortBy('start_date');

            // If non-milestone activities are count then group all non-milestone activities.
            if ($non_milestone_activities->count()) {
                $data[$m]['name'] = "<span class='icon fa fa-random'></span> Non-Milestone";

                foreach ($non_milestone_activities as $activity) {
                    $data[$m]['id']       = $activity->id;
                    $data[$m]['desc']     = $activity->icon_html . $activity->name;
                    $data[$m]['cssClass'] = $activity->identifier . ' done-' . $activity->completion_percentage;
                    $data[$m]['values'][] = [
                        'from'        => moment_timestamp($activity->start_date),
                        'to'          => moment_timestamp($activity->due_date),
                        'label'       => $activity->gantt_label,
                        'desc'        => $activity->gantt_info,
                        'dataObj'     => [
                            'id'         => $activity->id,
                            'type'       => $activity->identifier,
                            'can_edit'   => $activity->auth_can_edit,
                            'can_delete' => $activity->auth_can_delete,
                            'show_url'   => $activity->show_route,
                            'edit_url'   => route('admin.' . $activity->identifier . '.edit', $activity->id),
                            'update_url' => route('admin.' . $activity->identifier . '.update', $activity->id),
                            'modal_size' => 'large',
                        ],
                        'customClass' => "{$activity->identifier} progress progress-{$activity->completion_percentage}",
                    ];

                    $m++;
                }
            }

            // Group all milestones with own activities in the gantt chart.
            foreach ($milestones as $milestone) {
                $data[$m]['id']       = $milestone->id;
                $data[$m]['name']     = $milestone->icon_html . $milestone->name;
                $data[$m]['desc']     = $milestone->gantt_description;
                $data[$m]['cssClass'] = $milestone->identifier . ' done-' . $milestone->gantt_completion_percentage;
                $data[$m]['values'][] = [
                    'from'        => moment_timestamp($milestone->activity_start_date),
                    'to'          => moment_timestamp($milestone->activity_end_date),
                    'label'       => $milestone->gantt_label,
                    'desc'        => $milestone->gantt_info,
                    'dataObj'     => [
                        'id'         => $milestone->id,
                        'type'       => 'milestone',
                        'can_edit'   => $milestone->auth_can_edit,
                        'can_delete' => $milestone->auth_can_delete,
                        'show_url'   => route('admin.milestone.show', $milestone->id),
                        'edit_url'   => route('admin.milestone.edit', $milestone->id),
                        'update_url' => route('admin.milestone.update', $milestone->id),
                        'modal_size' => 'medium',
                    ],
                    'customClass' => $milestone->identifier . ' progress progress-' .
                                     $milestone->gantt_completion_percentage,
                ];

                $activities = $milestone->getActivitiesAttribute($filter)->sortBy('due_date')->sortBy('start_date');

                if ($activities->count()) {
                    $t = $m + 1;

                    foreach ($activities as $activity) {
                        $data[$t]['id']       = $activity->id;
                        $data[$t]['desc']     = $activity->icon_html . $activity->name;
                        $data[$t]['cssClass'] = $activity->identifier . ' done-' . $activity->completion_percentage;
                        $data[$t]['values'][] = [
                            'from'        => moment_timestamp($activity->start_date),
                            'to'          => moment_timestamp($activity->due_date),
                            'label'       => $activity->gantt_label,
                            'desc'        => $activity->gantt_info,
                            'dataObj'     => [
                                'id'         => $activity->id,
                                'type'       => $activity->identifier,
                                'can_edit'   => $activity->auth_can_edit,
                                'can_delete' => $activity->auth_can_delete,
                                'show_url'   => $activity->show_route,
                                'edit_url'   => route('admin.' . $activity->identifier . '.edit', $activity->id),
                                'update_url' => route('admin.' . $activity->identifier . '.update', $activity->id),
                                'modal_size' => 'large',
                            ],
                            'customClass' => $activity->identifier . ' progress progress-' .
                                             $activity->completion_percentage,
                        ];

                        $t++;
                    }

                    $m = ($t - 1);
                }

                $m++;
            }
        } else {
            // Show all milestone free activities in gantt chart.
            $activities = $this->getActivitiesAttribute($filter)->sortBy('due_date')->sortBy('start_date');

            if ($activities->count()) {
                $t = 0;

                foreach ($activities as $activity) {
                    $data[$t]['id']       = $activity->id;
                    $data[$t]['name']     = $activity->icon_html . $activity->name;
                    $data[$t]['desc']     = $activity->description;
                    $data[$t]['cssClass'] = "plain {$activity->identifier} done-{$activity->completion_percentage}";
                    $data[$t]['values'][] = [
                        'from'        => moment_timestamp($activity->start_date),
                        'to'          => moment_timestamp($activity->due_date),
                        'label'       => $activity->gantt_label,
                        'desc'        => $activity->gantt_info,
                        'dataObj'     => [
                            'id'         => $activity->id,
                            'type'       => $activity->identifier,
                            'can_edit'   => $activity->auth_can_edit,
                            'can_delete' => $activity->auth_can_delete,
                            'show_url'   => $activity->show_route,
                            'edit_url'   => route('admin.' . $activity->identifier . '.edit', $activity->id),
                            'update_url' => route('admin.' . $activity->identifier . '.update', $activity->id),
                            'modal_size' => 'large',
                        ],
                        'customClass' => $activity->identifier . ' progress progress-' .
                                         $activity->completion_percentage,
                    ];

                    $t++;
                }
            }
        }

        return $data;
    }

    /**
     * Get the project top issues fixer list.
     *
     * @param int $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getIssueFixerList($limit = 5)
    {
        if ($this->members()->count()) {
            return $this->members()->get()->sortByDesc('closed_project_issues_count')->take($limit);
        }

        return [];
    }

    /**
     * Get to know the project has at least one issue fixer.
     *
     * @return bool
     */
    public function getHasIssueFixerAttribute()
    {
        if (count($this->getIssueFixerList())) {
            return $this->getIssueFixerList()->first()->closed_project_issues_count > 0;
        }

        return false;
    }

    /**
     * Get the project top task finisher list.
     *
     * @param int $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTaskFinisherList($limit = 5)
    {
        if ($this->members()->count()) {
            return $this->members()->get()->sortByDesc('closed_project_tasks_count')->take($limit);
        }

        return [];
    }

    /**
     * Get to know the project has at least one finisher.
     *
     * @return bool
     */
    public function getHasFinisherAttribute()
    {
        if (count($this->getTaskFinisherList())) {
            return $this->getTaskFinisherList()->first()->closed_project_tasks_count > 0;
        }

        return false;
    }

    /**
     * Get the project overdue tasks.
     *
     * @return array
     */
    public function getOverdueTasksList()
    {
        if ($this->tasks()->count()) {
            return $this->tasks()->onlyOpen()->get()->filter(function ($task) {
                return $task->overdue_days > 0;
            })->sortByDesc('overdue_days')->flatten()->all();
        }

        return [];
    }

    /**
     * Get the project overdue issues.
     *
     * @return array
     */
    public function getOverdueIssuesList()
    {
        if ($this->issues()->count()) {
            return $this->issues()->onlyOpen()->get()->filter(function ($issue) {
                return $issue->overdue_days > 0;
            })->sortByDesc('overdue_days')->flatten()->all();
        }

        return [];
    }

    /**
     * Get the project overdue milestones.
     *
     * @return array
     */
    public function getOverdueMilestonesList()
    {
        if ($this->milestones()->count()) {
            return $this->milestones()->get()->filter(function ($milestone) {
                return (($milestone->completion_percentage < 100) && ($milestone->overdue_days > 0));
            })->sortByDesc('overdue_days')->flatten()->all();
        }

        return [];
    }

    /**
     * Get the project overdue activities.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOverdueActs()
    {
        $activities = array_merge(
            $this->getOverdueTasksList(),
            $this->getOverdueIssuesList(),
            $this->getOverdueMilestonesList()
        );

        if (count($activities)) {
            return collect($activities)->sortBy('created_at')->sortByDesc('overdue_days');
        }

        return collect();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * Query project by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReadableIdentifier($query, $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Query project by field, condition, and value.
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
            $query = $query->join('project_member', 'project_member.project_id', '=', 'projects.id');
        }

        // Query filter for project member attributes.
        if ($related_attribute == 'member') {
            if (is_array($conditional_value) && in_array('0', $conditional_value)) {
                $param_key                     = array_search('0', $conditional_value);
                $conditional_value[$param_key] = auth_staff()->id;
            }

            if ($condition == 'not_equal') {
                $not_in_ids = \DB::table('project_member')
                                 ->whereIn('staff_id', $conditional_value)
                                 ->pluck('project_id');

                return $query->whereNotIn('projects.id', $not_in_ids);
            }

            $attribute = 'project_member.staff_id';
        }

        return $query->conditionalFilterQuery($attribute, $condition, $conditional_value);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the project total no of milestones.
     *
     * @return int
     */
    public function getMilestoneCountAttribute()
    {
        return $this->milestones()->count();
    }

    /**
     * Get the project total no of completed milestones.
     *
     * @return int
     */
    public function getCompletedMilestoneCountAttribute()
    {
        if ($this->milestone_count) {
            $milestone_ids = $this->milestones()->pluck('id');

            return Milestone::whereIn('id', $milestone_ids)
                            ->get()
                            ->where('completion_percentage', 100)
                            ->count();
        }

        return 0;
    }

    /**
     * Get the project total no of open milestones.
     *
     * @return int
     */
    public function getOpenMilestoneCountAttribute()
    {
        if ($this->milestone_count > 0) {
            return min_zero($this->milestone_count - $this->completed_milestone_count);
        }

        return 0;
    }

    /**
     * Get the project milestones completion percentage.
     *
     * @return int
     */
    public function getMilestoneCompletionPercentageAttribute()
    {
        $percentage = -1;

        if ($this->milestone_count > 0) {
            $percentage = floor($this->completed_milestone_count / $this->milestone_count * 100);
        }

        return $percentage;
    }

    /**
     * Get the project milestone progress bar HTML.
     *
     * @return string
     */
    public function getMilestoneStatHtmlAttribute()
    {
        return \HtmlElement::renderProgressBar(
            $this->milestone_completion_percentage,
            'Milestone',
            $this->milestone_count,
            $this->completed_milestone_count,
            $this->open_milestone_count
        );
    }

    /**
     * Get project sorted members according to admin, member.
     *
     * @return array
     */
    public function getSortedMembersAttribute()
    {
        $members = $this->members()
                        ->orderBy('pivot_created_at')
                        ->groupBy('pivot_staff_id')
                        ->get()
                        ->sortByDesc(function ($member, $key) {
                            if ($member->project_admin) {
                                return 3;
                            } elseif ($member->auth_member) {
                                return 1;
                            } else {
                                return 0;
                            }
                        })->flatten()->all();

        return $members;
    }

    /**
     * Get project members HTML.
     *
     * @return string
     */
    public function getMembersHtmlAttribute()
    {
        $members_html = '';

        if ($this->members->count()) {
            foreach ($this->sorted_members as $key => $member) {
                if ($key < 2) {
                    $members_html .= $member->project_avatar_html;

                    if ($key == 1) {
                        if ($this->members->count() == 3) {
                            $members_html .= $this->sorted_members[2]->project_avatar_html;
                        } elseif ($this->members->count() > 3) {
                            $count         = $this->members->count() - 2;
                            $rest_members  = $this->getRestMembers(2);
                            $members_html .= "<a class='avatar-link further add-multiple' data-toggle='tooltip'
                                                 data-placement='top' data-html='true' title='{$rest_members}'
                                                 modal-title='Project Members' modal-sub-title='{$this->name}'
                                                 modal-datatable='true' save-new='false-all' cancel-txt='Close'
                                                 datatable-url='project-member-data/{$this->id}/read'
                                                 data-action='' data-content='project.partials.modal-project-member'>"
                                                 . $count .
                                             '</a>';
                        }

                        break;
                    }
                }
            }
        }

        return $members_html;
    }

    /**
     * Get rest hidden members HTML.
     *
     * @param int $start_key
     *
     * @return string
     */
    public function getRestMembers($start_key)
    {
        $html = '';

        foreach ($this->sorted_members as $key => $member) {
            if ($key >= $start_key) {
                $html .= str_replace('"', '', str_replace("'", "", $member->name)) . '<br>';
            }

            if ($key == 11) {
                break;
            }
        }

        if (count($this->sorted_members) > 12) {
            $html .= '+' . (count($this->sorted_members) - 12) . ' members ...';
        }

        return $html;
    }

    /**
     * Get the project extended actions HTML.
     *
     * @param bool $edit_permission
     *
     * @return string
     */
    public function extendActionHtml($edit_permission = true)
    {
        $extend_action = '';

        if ($this->authCanDo('task.create')) {
            $extend_action .= "<li>
                                <a class='add-multiple' data-item='task' data-content='task.partials.form'
                                   data-action='" . route('admin.task.store') . "' save-new='false'
                                   data-default='related_type:project|related_id:{$this->id}'>
                                   <i class='fa fa-check-square'></i> Add Task
                                </a>
                               </li>";
        }

        if ($this->authCanDo('issue.create')) {
            $extend_action .= "<li>
                                <a class='add-multiple' data-item='issue' data-content='issue.partials.form'
                                   data-action='" . route('admin.issue.store') . "' save-new='false'
                                   data-default='related_type:project|related_id:{$this->id}'>
                                   <i class='fa fa-bug'></i> Add Issue
                                </a>
                               </li>";
        }

        if ($this->authCanDo('event.create')) {
            $extend_action .= "<li>
                                <a class='add-multiple' data-item='event'  data-content='event.partials.form'
                                   data-action='" . route('admin.event.store') . "' save-new='false'
                                   data-default='related_type:project|related_id:{$this->id}'>
                                   <i class='fa fa-calendar'></i> Add Event
                                </a>
                               </li>";
        }

        return $extend_action;
    }

    /**
     * Get the initial date HTML of the project.
     *
     * @return string
     */
    public function getInitDateHtmlAttribute()
    {
        $date  = $this->readableDate('created_at');
        $title = 'Created Date';

        if (! is_null($this->start_date)) {
            $date  = $this->readableDate('start_date');
            $title = 'Start Date';
        }

        return "<span class='capitalize' data-toggle='tooltip' data-placement='bottom' title='{$title}'>{$date}</span>";
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(Staff::class, 'project_owner')->withTrashed();
    }

    /**
     * An inverse one-to-many relationship with ProjectStatus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    /**
     * A many-to-many relationship with Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(Staff::class, 'project_member')
                    ->withPivot(self::getPermissionKeys())
                    ->withPivot('id')
                    ->withTimestamps()
                    ->groupBy('staffs.id');
    }

    /**
     * A one-to-many relationship with Milestone.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Polymorphic one-to-many relationship with Task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tasks()
    {
        return $this->morphMany(Task::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with Issue.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function issues()
    {
        return $this->morphMany(Issue::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with AllowedStaff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function allowedstaffs()
    {
        return $this->morphMany(AllowedStaff::class, 'linked');
    }

    /**
     * Polymorphic one-to-many relationship with Follower.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function followers()
    {
        return $this->morphMany(Follower::class, 'linked');
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
