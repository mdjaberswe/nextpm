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
use App\Models\Traits\KanbanTrait;
use App\Models\Traits\ModuleTrait;
use App\Models\Traits\ActivityTrait;
use App\Models\Traits\CalendarTrait;
use App\Models\Traits\PosionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Task extends BaseModel
{
    use SoftDeletes;
    use OwnerTrait;
    use KanbanTrait;
    use ModuleTrait;
    use ActivityTrait;
    use CalendarTrait;
    use PosionableTrait;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_owner', 'name', 'description', 'priority', 'access', 'task_status_id', 'completion_percentage',
        'start_date', 'due_date', 'linked_type', 'linked_id', 'milestone_id', 'position',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'title', 'item', 'start', 'end', 'color', 'modal_size', 'related_id', 'related_type', 'auth_can_view',
        'auth_can_edit', 'auth_can_change_owner', 'auth_can_delete', 'base_url', 'position_url',
        'show_route', 'overdue_days', 'milestone_val', 'owner_id',
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
        'completion_percentage' => 'string:<span class=\'percent\'>%u</span>',
        'priority'              => 'helper:display_field',
        'milestone_id'          => 'database:milestone|id|name',
        'task_status_id'        => 'database:task_status|id|name',
        'task_owner'            => 'database:staff|id|name',
        'access'                => 'helper:readable_access',
    ];

    /**
     * Field custom display name.
     *
     * @var array
     */
    protected $revisionFormattedFieldNames = ['linked_id' => 'Related'];

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected static $related_types = ['project'];

    /**
     * Valid priority static list array.
     *
     * @var array
     */
    protected static $priority_list = ['high', 'highest', 'low', 'lowest', 'normal'];

    /**
     * Fields list array where the index is field's name and corresponding value as field's display name.
     *
     * @var array
     */
    protected static $fieldlist = [
        'access'                => 'Access',
        'completion_percentage' => 'Completion Percentage',
        'description'           => 'Description',
        'due_date'              => 'Due Date',
        'milestone_id'          => 'Milestone',
        'priority'              => 'Priority',
        'linked_type'           => 'Related To',
        'linked_id'             => 'Related Name',
        'start_date'            => 'Start Date',
        'name'                  => 'Task Name',
        'task_owner'            => 'Task Owner',
        'task_status_id'        => 'Task Status',
    ];

    /**
     * Fields name array that can be mass updatable.
     *
     * @var array
     */
    protected static $mass_fieldlist = [
        'access', 'completion_percentage', 'description', 'due_date', 'priority',
        'linked_type', 'start_date', 'name', 'task_owner', 'task_status_id',
    ];

    /**
     * Fields name array that uses to filter data.
     *
     * @var array
     */
    protected static $filter_fieldlist = [
        'access', 'completion_percentage', 'description', 'due_date', 'priority',
        'linked_type', 'start_date', 'name', 'task_owner', 'task_status_id',
    ];

    /**
     * Task form validation.
     *
     * @param array                 $data
     * @param \App\Models\Task|null $task
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data, $task = null)
    {
        $required   = ! is_null($task) ? 'required' : '';
        // The due date can not be earlier than the start date.
        $after_date = not_null_empty($data['start_date'])
                      ? 'after:' . date('Y-m-d', strtotime($data['start_date'] . ' -1 day')) : '';
        $rules      = [
            'name'                  => 'required|max:200',
            'start_date'            => $required . '|date',
            'due_date'              => $required . '|date|' . $after_date,
            'access'                => 'required|in:private,public,public_rwd',
            'task_status_id'        => 'required|exists:task_status,id,deleted_at,NULL',
            'completion_percentage' => 'numeric|min:0|max:100|in:0,10,20,30,40,50,60,70,80,90,100',
            'task_owner'            => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'related_type'          => 'in:' . implode(',', self::$related_types),
            'priority'              => 'in:' . implode(',', self::$priority_list),
            'description'           => 'max:65535',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('related_type', $data) && ! empty($data['related_type'])) {
            $related_ids = implode(',', morph_to_model($data['related_type'])::getAuthPermittedIds('task'));
            $related_ids = is_null($task) ? $related_ids : $related_ids . ',' . $task->linked_id;
            $rules['related_id'] = "required|exists:{$data['related_type']}s,id,deleted_at,NULL|in:{$related_ids}";

            if ($data['related_type'] == 'project') {
                $rules['milestone_id'] = "exists:milestones,id,project_id,{$data['related_id']},deleted_at,NULL";
            }
        }

        return validator($data, $rules);
    }

    /**
     * Task single field update validation.
     *
     * @param array                 $data
     * @param \App\Models\Task|null $task
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function singleValidate($data, $task = null)
    {
        $message    = [];
        $after_date = '';
        $project_id = null;

        // The due date can not be earlier than the start date.
        if (array_key_exists('due_date', $data)
            && ! empty($data['due_date'])
            && ! is_null($task)
            && not_null_empty($task->start_date)
        ) {
            $after_date = 'after:' . date('Y-m-d', strtotime($task->start_date . ' -1 day'));
        }

        $rules = [
            'name'                  => 'sometimes|required|max:200',
            'task_owner'            => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'            => 'sometimes|required|date',
            'due_date'              => 'sometimes|required|date|' . $after_date,
            'priority'              => 'in:' . implode(',', self::$priority_list),
            'linked_type'           => 'in:' . implode(',', self::$related_types),
            'access'                => 'sometimes|required|in:private,public,public_rwd',
            'task_status_id'        => 'sometimes|required|exists:task_status,id,deleted_at,NULL',
            'completion_percentage' => 'numeric|min:0|max:100|in:0,10,20,30,40,50,60,70,80,90,100',
            'description'           => 'max:65535',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
            $related_ids        = implode(',', morph_to_model($data['linked_type'])::getAuthPermittedIds('task'));
            $related_rule       = ! is_null($task) ? "in:{$related_ids},{$task->linked_id}" : '';
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL|$related_rule";
            $project_id         = $data['linked_type'] == 'project' ? $data['linked_id'] : $project_id;
        }

        if (not_null_empty($task) && $task->linked_type == 'project') {
            $rules['milestone_id'] = "exists:milestones,id,project_id,{$task->linked_id},deleted_at,NULL";
        } elseif (not_null_empty($task) && $task->linked_type != 'project') {
            $rules['milestone_id']        = 'size:0';
            $message['milestone_id.size'] = 'The selected milestone is invalid';
        } elseif (not_null_empty($project_id)) {
            $rules['milestone_id'] = "exists:milestones,id,project_id,{$project_id},deleted_at,NULL";
        }

        // The auth user can only change the owner if the auth user has "Change Owner" permission of the specified task.
        if (array_key_exists('change_owner', $data)
            && $data['change_owner']
            && isset($task)
            && ! $task->auth_can_change_owner
        ) {
            $data['task_owner'] = 0;
            $message['task_owner.exists'] = 'You don\'t have permission to change owner';
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
        $rules = [
            'related'               => 'required|in:' . implode(',', self::massfieldlist()),
            'name'                  => 'required_if:related,name|max:200',
            'start_date'            => 'required_if:related,start_date|date',
            'due_date'              => 'required_if:related,due_date|date',
            'access'                => 'required_if:related,access|in:private,public,public_rwd',
            'task_status_id'        => 'required_if:related,task_status_id|exists:task_status,id,deleted_at,NULL',
            'task_owner'            => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'completion_percentage' => 'numeric|min:0|max:100|in:0,10,20,30,40,50,60,70,80,90,100',
            'priority'              => 'in:' . implode(',', self::$priority_list),
            'linked_type'           => 'in:' . implode(',', self::$related_types),
            'description'           => 'max:65535',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
            $related_ids = implode(',', morph_to_model($data['linked_type'])::getAuthPermittedIds('task'));
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL|in:$related_ids";
        }

        return validator($data, $rules);
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
        $owners = User::onlyStaff()->where('status', 1)->pluck('linked_id')->toArray();
        $fields = [
            [
                'name'      => 'name',
                'type'      => 'string',
                'condition' => 'required|array|max:200',
            ],
            [
                'name'      => 'priority',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:' . implode(',', self::$priority_list),
            ],
            [
                'name'      => 'task_owner',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:0,' . implode(',', $owners),
            ],
            [
                'name'      => 'task_status_id',
                'type'      => 'dropdown',
                'condition' => 'required|exists:task_status,id,deleted_at,NULL',
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
                'name'      => 'due_date',
                'type'      => 'date',
                'condition' => 'required|in:7,30,90',
            ],
            [
                'name'      => 'completion_percentage',
                'type'      => 'numeric',
                'condition' => 'required|numeric|min:0|max:100|in:0,10,20,30,40,50,60,70,80,90,100',
            ],
            [
                'name'      => 'linked_type',
                'type'      => 'dropdown',
                'condition' => 'required|in:' . implode(',', self::$related_types),
            ],
        ];

        $rules = FilterView::filterRulesGenerator($data, $fields);

        if (array_key_exists('linked_type_condition', $data)
            && $data['linked_type_condition'] != 'empty'
            && $data['linked_type_condition'] != 'not_empty'
        ) {
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL";
        }

        return validator($data, $rules);
    }

    /**
     * Valid priority list.
     *
     * @return array
     */
    public static function prioritylist()
    {
        return self::$priority_list;
    }

    /**
     * Get resource data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        return [
            'thead'        => ['task name', 'due date', ['status', 'data_class' => 'sync-val'], ['progress', 'data_class' => 'sync-val'], 'priority', 'related to', 'owner'],
            'checkbox'     => self::allowMassAction(),
            'action'       => self::allowAction(),
            'json_columns' => \DataTable::jsonColumn([
                'checkbox', 'name', 'due_date', 'status', 'completion_percentage',
                'priority', 'related_to', 'task_owner', 'action',
            ], self::hideColumns()),
        ];
    }

    /**
     * Get resource table data.
     *
     * @param \App\Models\Task         $tasks
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($tasks, $request)
    {
        return \DataTable::of($tasks)->addColumn('checkbox', function ($task) {
            return $task->checkbox_html;
        })->editColumn('name', function ($task) {
            return $task->name_html;
        })->editColumn('due_date', function ($task) {
            return $task->due_date_html;
        })->addColumn('status', function ($task) {
            return $task->activity_status_html;
        })->editColumn('completion_percentage', function ($task) {
            return $task->completion_html;
        })->editColumn('priority', function ($task) {
            return $task->plain_priority;
        })->addColumn('related_to', function ($task) {
            return non_property_checker($task->linked, 'name_link_icon');
        })->editColumn('task_owner', function ($task) {
            return $task->owner_html;
        })->addColumn('action', function ($task) {
            return $task->getActionHtml('Task', 'admin.task.destroy', null, [
                'edit'   => $task->auth_can_edit,
                'delete' => $task->auth_can_delete,
            ]);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->globalSearch($request, [
                    'name', 'due_date_html', 'status_name', 'completion_percentage',
                    'priority', 'related_name', 'owner_name',
                ]);
            });
        })->make(true);
    }

    /**
     * Get resource tab table format.
     *
     * @return array
     */
    public static function getTabTableFormat()
    {
        $columns = [
            'name', 'due_date', 'status', 'completion_percentage', 'priority', 'related_to', 'task_owner', 'action',
        ];

        return [
            'columns'      => $columns,
            'json_columns' => \DataTable::jsonColumn($columns),
            'thead'        => ['NAME', 'DUE DATE', 'STATUS', 'PROGRESS', 'PRIORITY', 'RELATED TO', 'OWNER'],
            'checkbox'     => false,
            'filter_input' => [
                'status'   => [
                    'type'      => 'dropdown',
                    'no_search' => true,
                    'options'   => ['-1' => 'All Tasks', '1' => 'Open Tasks', '0' => 'Closed Tasks']
                ],
            ],
        ];
    }

    /**
     * Get resource tab table data.
     *
     * @param \App\Models\Task         $tasks
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTabTableData($tasks, $request)
    {
        return \DataTable::of($tasks)->editColumn('name', function ($task) {
            return $task->name_html;
        })->editColumn('due_date', function ($task) {
            return $task->due_date_html;
        })->addColumn('status', function ($task) {
            return $task->activity_status_html;
        })->editColumn('completion_percentage', function ($task) {
            return $task->completion_html;
        })->editColumn('priority', function ($task) {
            return $task->plain_priority;
        })->addColumn('related_to', function ($task) {
            return non_property_checker($task->linked, 'name_link_icon');
        })->editColumn('task_owner', function ($task) {
            return $task->owner_html;
        })->addColumn('action', function ($task) {
            return $task->getActionHtml('Task', 'admin.task.destroy', null, [
                'edit'   => $task->auth_can_edit,
                'delete' => $task->auth_can_delete,
            ], true);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                $status = true;

                if ($request->has('search') && $request->search['value'] != '') {
                    $status = $row->globalSearch($request, [
                        'name', 'due_date_html', 'status_name', 'completion_percentage', 'priority', 'owner_name',
                    ]);
                }

                if ($request->has('status') && $request->status == 0 && $row->status->category == 'open') {
                    $status = false;
                }

                if ($request->has('status') && $request->status == 1 && $row->status->category == 'closed') {
                    $status = false;
                }

                return $status;
            });
        })->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get Gantt label.
     *
     * @return null
     */
    public function getGanttLabelAttribute()
    {
        return $this->completion_percentage . '%';
    }

    /**
     * Get completion percentage progress bar HTML.
     *
     * @return string
     */
    public function getCompletionHtmlAttribute()
    {
        return "<a class='completion-bar'>
                    <div class='progress'>
                        <div class='progress-bar color-success' role='progressbar'
                            aria-valuenow='{$this->completion_percentage}' aria-valuemin='0' aria-valuemax='100'
                            style='width: {$this->completion_percentage}%'>
                            <span class='sr-only'>{$this->completion_percentage}% Complete</span>
                        </div>
                        <span class='shadow'>{$this->completion_percentage}%</span>
                    </div>
                </a>";
    }

    /**
     * Get associated milestone id.
     *
     * @return int
     */
    public function getMilestoneValAttribute()
    {
        return $this->milestone_id;
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
        return $this->belongsTo(Staff::class, 'task_owner')->withTrashed();
    }

    /**
     * An inverse one-to-many relationship with TaskStatus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    /**
     * An inverse one-to-many relationship with Milestone.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    /**
     * A polymorphic, inverse one-to-many relationship with Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo()->withTrashed();
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
