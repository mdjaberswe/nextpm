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
use App\Models\Traits\OwnerTrait;
use App\Models\Traits\ChartTrait;
use App\Models\Traits\CalendarTrait;
use App\Models\Traits\PosionableTrait;
use App\Models\Traits\ParentModuleTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Milestone extends BaseModel
{
    use SoftDeletes;
    use OwnerTrait;
    use ChartTrait;
    use CalendarTrait;
    use PosionableTrait;
    use ParentModuleTrait;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'milestones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'start_date', 'end_date', 'access', 'position', 'project_id', 'milestone_owner',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'title', 'item', 'start', 'end', 'color', 'modal_size', 'show_route',
        'auth_can_view', 'auth_can_edit', 'auth_can_change_owner', 'auth_can_delete',
        'base_url', 'position_url', 'completion_percentage', 'status', 'overdue_days', 'owner_id',
    ];

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
        'project_id'      => 'database:project|id|name',
        'milestone_owner' => 'database:staff|id|name',
        'access'          => 'helper:readable_access',
    ];

    /**
     * Milestone form validation.
     *
     * @param array                      $data
     * @param \App\Models\Milestone|null $milestone
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data, $milestone = null)
    {
        $project_ids   = implode(',', Project::getAuthPermittedIds('milestone'));
        $project_ids   = is_null($milestone) ? $project_ids : $project_ids . ',' . $milestone->project_id;
        $end_date_rule = '';

        // The end date can not be earlier than the start date.
        if (not_null_empty($data['start_date'])) {
            $end_date_rule = 'after:' . date('Y-m-d', strtotime($data['start_date'] . ' -1 day'));
        }

        $rules = [
            'name'            => 'required|max:200',
            'milestone_owner' => 'required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|' . $end_date_rule,
            'access'          => 'required|in:private,public,public_rwd',
            'project_id'      => 'required|exists:projects,id,deleted_at,NULL|in:' . $project_ids,
            'description'     => 'max:65535',
        ];

        return validator($data, $rules);
    }

    /**
     * Milestone single field update validation.
     *
     * @param array                      $data
     * @param \App\Models\Milestone|null $milestone
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function singleValidate($data, $milestone = null)
    {
        $message      = [];
        $after_date   = '';
        $project_ids  = implode(',', Project::getAuthPermittedIds('milestone'));
        $project_rule = ! is_null($milestone) ? 'in:' . $project_ids . ',' . $milestone->project_id : '';

        // The end date can not be earlier than the start date.
        if (array_key_exists('end_date', $data)
            && ! empty($data['end_date'])
            && ! is_null($milestone)
            && not_null_empty($milestone->start_date)
        ) {
            $after_date = 'after:' . date('Y-m-d', strtotime($milestone->start_date . ' -1 day'));
        }

        $rules = [
            'name'            => 'sometimes|required|max:200',
            'milestone_owner' => 'sometimes|required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'project_id'      => 'sometimes|required|exists:projects,id,deleted_at,NULL|' . $project_rule,
            'start_date'      => 'sometimes|required|date',
            'end_date'        => 'sometimes|required|date|' . $after_date,
            'description'     => 'max:65535',
            'access'          => 'sometimes|required|in:private,public,public_rwd',
        ];

        // The auth user can only change the owner if the auth user has "Change Owner" permission of the specified milestone.
        if (array_key_exists('change_owner', $data)
            && $data['change_owner']
            && isset($milestone)
            && ! $milestone->auth_can_change_owner
        ) {
            $data['milestone_owner'] = 0;
            $message['milestone_owner.exists'] = 'You don\'t have permission to change owner';
        }

        return validator($data, $rules, $message);
    }

    /**
     * Get show page tab array list.
     *
     * @param \App\Models\Milestone|null $milestone
     *
     * @return array
     */
    public static function informationTypes($milestone = null)
    {
        $information_types = [
            'overview'     => 'Overview',
            'tasks'        => 'Tasks',
            'taskskanban'  => ['display' => 'Tasks Kanban', 'nav' => 0, 'parent' => 'tasks'],
            'issues'       => 'Issues',
            'issueskanban' => ['display' => 'Issues Kanban', 'nav' => 0, 'parent' => 'issues'],
            'calendar'     => 'Calendar',
            'notes'        => 'Notes',
            'files'        => 'Files',
            'history'      => 'History',
        ];

        // If the auth user doesn't have permission to view "Task" then remove the task tab.
        if (! permit('task.view')) {
            array_forget($information_types, 'tasks');
            array_forget($information_types, 'taskskanban');
        }

        // If the auth user doesn't have permission to view "Issue" then remove the issue tab.
        if (! permit('issue.view')) {
            array_forget($information_types, 'issues');
            array_forget($information_types, 'issueskanban');
        }

        // If the auth user doesn't have permission to view "Task" or "Issue" then remove the calendar tab.
        if (! (permit('task.view') || permit('issue.view'))) {
            array_forget($information_types, 'calendar');
        }

        // If the auth user doesn't have permission to view "Note" then remove the note tab.
        if (! permit('note.view')) {
            array_forget($information_types, 'notes');
        }

        // If the auth user doesn't have permission to view "Files" then remove the file tab.
        if (! permit('attachment.view')) {
            array_forget($information_types, 'files');
        }

        return $information_types;
    }

    /**
     * Get resource tab table format.
     *
     * @param bool $sequence
     *
     * @return array
     */
    public static function getTabTableFormat($sequence = false)
    {
        // If $sequence is true then add drag-drop column to reorder milestones position.
        if ($sequence == true) {
            $thead   = ['name', 'progress', 'tasks', 'issues', 'start date', 'end date', 'owner'];
            $columns = [
                'sequence' => ['className' => 'reorder'], 'name', 'completion_percentage',
                'tasks', 'issues', 'start_date', 'end_date', 'milestone_owner', 'action',
            ];
        } else {
            $thead   = ['name', 'progress', 'tasks', 'issues', 'start date', 'end date', 'project', 'owner'];
            $columns = [
                'name', 'completion_percentage', 'tasks', 'issues',
                'start_date', 'end_date', 'project_id', 'milestone_owner', 'action',
            ];
        }

        return [
            'thead'        => $thead,
            'columns'      => $columns,
            'json_columns' => \DataTable::jsonColumn($columns, self::hideColumns()),
            'action'       => self::allowAction(),
            'checkbox'     => false,
        ];
    }

    /**
     * Get resource tab table data.
     *
     * @param \App\Models\Milestone    $milestones
     * @param \Illuminate\Http\Request $request
     * @param bool                     $sequence
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTabTableData($milestones, $request, $sequence = false)
    {
        $data = \DataTable::of($milestones)->editColumn('name', function ($milestone) {
            return $milestone->name_html;
        })->addColumn('completion_percentage', function ($milestone) {
            return $milestone->completion_percentage_html;
        })->editColumn('milestone_owner', function ($milestone) {
            return $milestone->owner_html;
        })->addColumn('tasks', function ($milestone) {
            return $milestone->task_stat_html;
        })->addColumn('issues', function ($milestone) {
            return $milestone->issue_stat_html;
        })->editColumn('start_date', function ($milestone) {
            return $milestone->start_date_html;
        })->editColumn('end_date', function ($milestone) {
            return $milestone->end_date_html;
        })->addColumn('action', function ($milestone) {
            return $milestone->getActionHtml('Milestone', 'admin.milestone.destroy', null, [
                'edit'   => $milestone->auth_can_edit,
                'delete' => $milestone->auth_can_delete,
            ], ['modal-small' => 'medium']);
        });

        // If $sequence is true then add drag-drop column to reorder milestones position.
        if ($sequence == true) {
            $data = $data->addColumn('sequence', function ($milestone) {
                return $milestone->drag_and_drop;
            });
        } else {
            $data = $data->editColumn('project_id', function ($milestone) {
                return $milestone->project->name_link_icon;
            });
        }

        return $data->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get gantt bar completion percentage.
     *
     * @return int
     */
    public function getGanttCompletionPercentageAttribute()
    {
        $approx_completion = ceil($this->completion_percentage / 10);

        if ($approx_completion == 10 && $this->completion_percentage < 100) {
            return 90;
        }

        return ($approx_completion * 10);
    }

    /**
     * Get gantt description of the specified resource.
     *
     * @return string
     */
    public function getGanttDescriptionAttribute()
    {
        $icon = $this->completion_percentage == 100 ? 'fa fa-calendar-check-o' : 'fa fa-calendar-o';

        return "<span class='icon icon-sm icon-milestone-period fa $icon'></span>" .
                $this->readableDate('activity_start_date', 'M j') . ' to ' .
                $this->readableDate('activity_end_date', 'M j');
    }

    /**
     * Get gantt display label.
     *
     * @return string
     */
    public function getGanttLabelAttribute()
    {
        return $this->gantt_completion_percentage . '%';
    }

    /**
     * Get breadcrumb HTML.
     *
     * @return string
     */
    public function getBreadcrumbTitleAttribute()
    {
        return "<ol class='breadcrumb'>
                    <li>" . link_to_route('admin.project.index', 'Projects') . "</li>
                    <li>
                        <a href='" . route('admin.project.show', $this->project_id) . "' data-realtime='project_id'>
                            {$this->project->name}
                        </a>
                    </li>
                    <li class='active'><span data-realtime='name'>{$this->name}</span></li>
                </ol>";
    }

    /**
     * Get the prev record of the specified resource.
     *
     * @return \App\Models\Milestone
     */
    public function getPrevRecordAttribute()
    {
        return self::where('project_id', $this->project_id)
                   ->where('position', '<', $this->position)
                   ->latest('position')
                   ->first();
    }

    /**
     * Get the next record of the specified resource.
     *
     * @return \App\Models\Milestone
     */
    public function getNextRecordAttribute()
    {
        return self::where('project_id', $this->project_id)
                   ->where('position', '>', $this->position)
                   ->first();
    }

    /**
     * Get default affected milestone id.
     *
     * @return int
     */
    public function getDefaultAffectedIdAttribute()
    {
        if (is_null($this->next_record)) {
            return $this->id;
        }

        return $this->next_record->id;
    }

    /**
     * Get the specified resource color.
     *
     * @return string
     */
    public function getColorByImportanceAttribute()
    {
        return \ChartData::rgbaColor((int) $this->position);
    }

    /**
     * Get extend actions HTML.
     *
     * @param bool $edit_permission
     *
     * @return string
     */
    public function extendActionHtml($edit_permission = true)
    {
        $extend_action = '';

        // If the auth user can create 'task' of the specified milestone related to the parent project.
        if ($this->project->authCanDo('task_create')) {
            $extend_action .= "<li>
                                <a class='add-multiple' data-item='task' data-content='task.partials.form'
                                   data-action='" . route('admin.task.store') . "' save-new='false'
                                   data-default='related_type:project|related_id:{$this->project_id}|milestone_id:{$this->id}|milestone_val:{$this->id}'>
                                   <i class='fa fa-check-square'></i> Add Task
                                </a>
                               </li>";
        }

        // If the auth user can create 'issue' of the specified milestone related to the parent project.
        if ($this->project->authCanDo('issue_create')) {
            $extend_action .= "<li>
                                <a class='add-multiple' data-item='issue' data-content='issue.partials.form'
                                   data-action='" . route('admin.issue.store') . "' save-new='false'
                                   data-default='related_type:project|related_id:{$this->project_id}|release_milestone_id:{$this->id}|affected_milestone_id:{$this->default_affected_id}|milestone_val:{$this->id}|affected_milestone_val:{$this->default_affected_id}'>
                                   <i class='fa fa-bug'></i> Add Issue
                                </a>
                               </li>";
        }

        return $extend_action;
    }

    /**
     * Get the milestone status.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->completion_percentage == 100 ? 'Closed' : 'Open';
    }

    /**
     * Get the milestone closed status.
     *
     * @return bool
     */
    public function getClosedStatusAttribute()
    {
        return $this->completion_percentage == 100;
    }

    /**
     * Get the linked project.
     *
     * @return \App\Models\Project
     */
    public function getLinkedAttribute()
    {
        return $this->project;
    }

    /**
     * Get the parent module type.
     *
     * @return string
     */
    public function getLinkedTypeAttribute()
    {
        return 'project';
    }

    /**
     * Get the parent module id.
     *
     * @return int
     */
    public function getLinkedIdAttribute()
    {
        return $this->project_id;
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
        return $this->belongsTo(Staff::class, 'milestone_owner')->withTrashed();
    }

    /**
     * An inverse one-to-many relationship with Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * A one-to-many relationship with Task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * A one-to-many relationship with Issue.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany(Issue::class, 'release_milestone_id');
    }

    /**
     * A one-to-many relationship with Issue.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function affectedIssues()
    {
        return $this->hasMany(Issue::class, 'affected_milestone_id');
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
