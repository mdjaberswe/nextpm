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
use App\Models\Traits\CalendarTrait;
use App\Models\Traits\ActivityTrait;
use App\Models\Traits\PosionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Issue extends BaseModel
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
    protected $table = 'issues';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'issue_owner', 'name', 'description', 'severity', 'reproducible',
        'access', 'issue_status_id', 'issue_type_id', 'start_date', 'due_date',
        'linked_type', 'linked_id', 'release_milestone_id', 'affected_milestone_id', 'position',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'title', 'item', 'start', 'end', 'color', 'modal_size', 'related_id', 'related_type',
        'auth_can_view', 'auth_can_edit', 'auth_can_change_owner', 'auth_can_delete', 'show_route',
        'base_url', 'position_url', 'overdue_days', 'milestone_val', 'affected_milestone_val', 'owner_id',
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
        'severity'              => 'helper:display_field',
        'issue_status_id'       => 'database:issue_status|id|name',
        'issue_type_id'         => 'database:issue_type|id|name',
        'issue_owner'           => 'database:staff|id|name',
        'release_milestone_id'  => 'database:milestone|id|name',
        'affected_milestone_id' => 'database:milestone|id|name',
        'access'                => 'helper:readable_access',
        'reproducible'          => 'helper:snake_to_ucwords',
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
     * Valid severity static list array.
     *
     * @var array
     */
    protected static $severity_list = ['blocker', 'critical', 'major', 'minor', 'trivial'];

    /**
     * Valid reproducible static list array.
     *
     * @var array
     */
    protected static $reproducible_list = ['always', 'sometimes', 'rarely', 'only_once', 'unable'];

    /**
     * Fields list array where the index is field's name and corresponding value as field's display name.
     *
     * @var array
     */
    protected static $fieldlist = [
        'access'                => 'Access',
        'description'           => 'Description',
        'due_date'              => 'Due Date',
        'name'                  => 'Issue Name',
        'issue_owner'           => 'Issue Owner',
        'issue_status_id'       => 'Issue Status',
        'issue_type_id'         => 'Issue Type',
        'linked_type'           => 'Related To',
        'linked_id'             => 'Related Name',
        'start_date'            => 'Start Date',
        'release_milestone_id'  => 'Release Milestone',
        'affected_milestone_id' => 'Affected Milestone',
        'reproducible'          => 'Reproducible',
        'severity'              => 'Severity',
    ];

    /**
     * Fields name array that can be mass updatable.
     *
     * @var array
     */
    protected static $mass_fieldlist = [
        'access', 'description', 'due_date', 'name', 'issue_owner',
        'issue_status_id', 'issue_type_id', 'linked_type', 'start_date', 'reproducible', 'severity',
    ];

    /**
     * Fields name array that uses to filter data.
     *
     * @var array
     */
    protected static $filter_fieldlist = [
        'access', 'description', 'due_date', 'name', 'issue_owner',
        'issue_status_id', 'issue_type_id', 'linked_type', 'start_date', 'reproducible', 'severity',
    ];

    /**
     * Issue form validation.
     *
     * @param array                  $data
     * @param \App\Models\Issue|null $issue
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data, $issue = null)
    {
        // The due date can not be earlier than the start date.
        $required   = ! is_null($issue) ? 'required' : '';
        $after_date = not_null_empty($data['start_date'])
                      ? 'after:' . date('Y-m-d', strtotime($data['start_date'] . ' -1 day')) : '';

        $rules = [
            'name'            => 'required|max:200',
            'start_date'      => $required . '|date',
            'due_date'        => $required . '|date|' . $after_date,
            'issue_type_id'   => 'exists:issue_types,id,deleted_at,NULL',
            'issue_status_id' => 'required|exists:issue_status,id,deleted_at,NULL',
            'issue_owner'     => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'severity'        => 'in:' . implode(',', self::$severity_list),
            'reproducible'    => 'in:' . implode(',', self::$reproducible_list),
            'related_type'    => 'in:' . implode(',', self::$related_types),
            'access'          => 'required|in:private,public,public_rwd',
            'description'     => 'max:65535',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('related_type', $data) && ! empty($data['related_type'])) {
            $related_ids = implode(',', morph_to_model($data['related_type'])::getAuthPermittedIds('issue'));
            $related_ids = is_null($issue) ? $related_ids : $related_ids . ',' . $issue->linked_id;
            $rules['related_id'] = "required|exists:{$data['related_type']}s,id,deleted_at,NULL|in:{$related_ids}";

            if ($data['related_type'] == 'project') {
                $valid_milestone_rule = "exists:milestones,id,project_id,{$data['related_id']},deleted_at,NULL";
                $rules['release_milestone_id'] = $rules['affected_milestone_id'] = $valid_milestone_rule;
            }
        }

        return validator($data, $rules);
    }

    /**
     * Issue single field update validation.
     *
     * @param array                  $data
     * @param \App\Models\Issue|null $issue
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function singleValidate($data, $issue = null)
    {
        $message    = [];
        $after_date = '';
        $project_id = null;

        // The due date can not be earlier than the start date.
        if (array_key_exists('due_date', $data)
            && ! empty($data['due_date'])
            && ! is_null($issue)
            && not_null_empty($issue->start_date)
        ) {
            $after_date = 'after:' . date('Y-m-d', strtotime($issue->start_date . ' -1 day'));
        }

        $rules = [
            'name'            => 'sometimes|required|max:200',
            'issue_owner'     => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'      => 'sometimes|required|date',
            'due_date'        => 'sometimes|required|date|' . $after_date,
            'linked_type'     => 'in:' . implode(',', self::$related_types),
            'severity'        => 'in:' . implode(',', self::$severity_list),
            'reproducible'    => 'in:' . implode(',', self::$reproducible_list),
            'access'          => 'sometimes|required|in:private,public,public_rwd',
            'issue_status_id' => 'sometimes|required|exists:issue_status,id,deleted_at,NULL',
            'issue_type_id'   => 'exists:issue_types,id,deleted_at,NULL',
            'description'     => 'max:65535',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('linked_type', $data) && not_null_empty($data['linked_type'])) {
            $related_ids        = implode(',', morph_to_model($data['linked_type'])::getAuthPermittedIds('issue'));
            $related_rule       = ! is_null($issue) ? 'in:' . $related_ids . ',' . $issue->linked_id : '';
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL|{$related_rule}";
            $project_id         = $data['linked_type'] == 'project' ? $data['linked_id'] : $project_id;
        }

        if (not_null_empty($issue) && $issue->linked_type == 'project') {
            $rules['release_milestone_id']  = "exists:milestones,id,project_id,{$issue->linked_id},deleted_at,NULL";
            $rules['affected_milestone_id'] = "exists:milestones,id,project_id,{$issue->linked_id},deleted_at,NULL";
        } elseif (not_null_empty($issue) && $issue->linked_type != 'project') {
            $rules['release_milestone_id']         = 'size:0';
            $rules['affected_milestone_id']        = 'size:0';
            $message['release_milestone_id.size']  = 'The selected release milestone is invalid';
            $message['affected_milestone_id.size'] = 'The selected affected milestone is invalid';
        } elseif (not_null_empty($project_id)) {
            $rules['release_milestone_id']  = "exists:milestones,id,project_id,{$project_id},deleted_at,NULL";
            $rules['affected_milestone_id'] = "exists:milestones,id,project_id,{$project_id},deleted_at,NULL";
        }

        // The auth user can only change the owner if the auth user has "Change Owner" permission of the specified issue.
        if (array_key_exists('change_owner', $data)
            && $data['change_owner']
            && isset($issue)
            && ! $issue->auth_can_change_owner
        ) {
            $data['issue_owner'] = 0;
            $message['issue_owner.exists'] = 'You don\'t have permission to change owner';
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
            'related'         => 'required|in:' . implode(',', self::massfieldlist()),
            'name'            => 'required_if:related,name|max:200',
            'start_date'      => 'required_if:related,start_date|date',
            'due_date'        => 'required_if:related,due_date|date',
            'access'          => 'required_if:related,access|in:private,public,public_rwd',
            'issue_status_id' => 'required_if:related,issue_status_id|exists:issue_status,id,deleted_at,NULL',
            'issue_owner'     => 'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'issue_type_id'   => 'exists:issue_types,id,deleted_at,NULL',
            'linked_type'     => 'in:' . implode(',', self::$related_types),
            'severity'        => 'in:' . implode(',', self::$severity_list),
            'reproducible'    => 'in:' . implode(',', self::$reproducible_list),
            'description'     => 'max:65535',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
            $related_ids = implode(',', morph_to_model($data['linked_type'])::getAuthPermittedIds('issue'));
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL|in:{$related_ids}";
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
                'name'      => 'severity',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:' . implode(',', self::$severity_list),
            ],
            [
                'name'      => 'reproducible',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:' . implode(',', self::$reproducible_list),
            ],
            [
                'name'      => 'issue_owner',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:0,' . implode(',', $owners),
            ],
            [
                'name'      => 'issue_status_id',
                'type'      => 'dropdown',
                'condition' => 'required|exists:issue_status,id,deleted_at,NULL',
            ],
            [
                'name'      => 'issue_type_id',
                'type'      => 'dropdown',
                'condition' => 'required|exists:issue_types,id,deleted_at,NULL',
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
            $rules['linked_id'] = 'required|exists:' . $data['linked_type'] . 's,id,deleted_at,NULL';
        }

        return validator($data, $rules);
    }

    /**
     * Valid severity list.
     *
     * @return array
     */
    public static function severitylist()
    {
        return self::$severity_list;
    }

    /**
     * Get a dropdown list of severity.
     *
     * @return array
     */
    public static function getSeverityDropdownList()
    {
        return array_map_with_keys(self::severitylist(), 'ucfirst');
    }

    /**
     * Issue valid reproducible list.
     *
     * @return array
     */
    public static function reproduciblelist()
    {
        return self::$reproducible_list;
    }

    /**
     * Get a dropdown list of reproducible.
     *
     * @return array
     */
    public static function getReproducibleDropdownList()
    {
        return array_map_with_keys(self::reproduciblelist(), 'snake_to_ucwords');
    }

    /**
     * Get resource data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        return [
            'thead'        => ['issue', 'due date', ['status', 'data_class' => 'sync-val'], 'severity', 'related to', 'owner'],
            'checkbox'     => self::allowMassAction(),
            'action'       => self::allowAction(),
            'json_columns' => \DataTable::jsonColumn([
                'checkbox', 'name', 'due_date', 'status', 'severity', 'related_to', 'issue_owner', 'action',
            ], self::hideColumns()),
        ];
    }

    /**
     * Get resource table data.
     *
     * @param \App\Models\Issue        $issues
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($issues, $request)
    {
        return \DataTable::of($issues)->addColumn('checkbox', function ($issue) {
            return $issue->checkbox_html;
        })->editColumn('name', function ($issue) {
            return $issue->name_html;
        })->editColumn('due_date', function ($issue) {
            return $issue->due_date_html;
        })->addColumn('status', function ($issue) {
            return $issue->activity_status_html;
        })->editColumn('severity', function ($issue) {
            return $issue->plain_severity;
        })->addColumn('related_to', function ($issue) {
            return non_property_checker($issue->linked, 'name_link_icon');
        })->editColumn('issue_owner', function ($issue) {
            return $issue->owner_html;
        })->addColumn('action', function ($issue) {
            return $issue->getActionHtml('Issue', 'admin.issue.destroy', null, [
                'edit'   => $issue->auth_can_edit,
                'delete' => $issue->auth_can_delete,
            ]);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->globalSearch($request, [
                    'name', 'due_date_html', 'status_name', 'severity', 'related_name', 'owner_name',
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
        $columns = ['name', 'due_date', 'status', 'severity', 'related_to', 'issue_owner', 'action'];

        return [
            'columns'      => $columns,
            'json_columns' => \DataTable::jsonColumn($columns),
            'thead'        => ['ISSUE', 'DUE DATE', 'STATUS', 'SEVERITY', 'RELATED TO', 'OWNER'],
            'checkbox'     => false,
            'filter_input' => [
                'status'   => [
                    'type'      => 'dropdown',
                    'no_search' => true,
                    'options'   => ['-1' => 'All Issues', '1' => 'Open Issues', '0' => 'Closed Issues'],
                ],
            ],
        ];
    }

    /**
     * Get resource tab table data.
     *
     * @param \App\Models\Issue        $issues
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTabTableData($issues, $request)
    {
        return \DataTable::of($issues)->editColumn('name', function ($issue) {
            return $issue->name_html;
        })->editColumn('due_date', function ($issue) {
            return $issue->due_date_html;
        })->addColumn('status', function ($issue) {
            return $issue->activity_status_html;
        })->editColumn('severity', function ($issue) {
            return $issue->plain_severity;
        })->addColumn('related_to', function ($issue) {
            return non_property_checker($issue->linked, 'name_link_icon');
        })->editColumn('issue_owner', function ($issue) {
            return $issue->owner_html;
        })->addColumn('action', function ($issue) {
            return $issue->getActionHtml('Issue', 'admin.issue.destroy', null, [
                'edit'   => $issue->auth_can_edit,
                'delete' => $issue->auth_can_delete,
            ], true);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                $status = true;

                if ($request->has('search') && $request->search['value'] != '') {
                    $status = $row->globalSearch($request, [
                        'name', 'due_date_html', 'status_name', 'severity', 'owner_name',
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
     * Get release milestone id.
     *
     * @return int
     */
    public function getMilestoneValAttribute()
    {
        return $this->release_milestone_id;
    }

    /**
     * Get affected milestone id.
     *
     * @return int
     */
    public function getAffectedMilestoneValAttribute()
    {
        return $this->affected_milestone_id;
    }

    /**
     * Get Gantt label.
     *
     * @return null
     */
    public function getGanttLabelAttribute()
    {
        return null;
    }

    /**
     * Get completion percentage 0|100 according to open|closed status.
     *
     * @return int
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->closed_status == true) {
            return 100;
        }

        return 0;
    }

    /**
     * Get display plain severity.
     *
     * @return string
     */
    public function getPlainSeverityAttribute()
    {
        if (not_null_empty($this->severity)) {
            return ucfirst($this->severity);
        }

        return null;
    }

    /**
     * Get display reproducible type.
     *
     * @return string
     */
    public function getReproducibleDisplayAttribute()
    {
        if (not_null_empty($this->reproducible)) {
            return snake_to_ucwords($this->reproducible, true);
        }

        return null;
    }

    /**
     * Get color according to severity.
     *
     * @return string
     */
    public function getColorByImportanceAttribute()
    {
        $default = 'rgba(170, 200, 245, 1)';

        if (is_null($this->severity)) {
            return $default;
        }

        switch ($this->severity) {
            case 'trivial':
                return 'rgba(50, 175, 175, 1)';
            case 'minor':
                return 'rgba(65, 155, 115, 1)';
            case 'major':
                return 'rgba(115, 155, 200, 1)';
            case 'critical':
                return 'rgba(255, 135, 30, 0.8)';
            case 'blocker':
                return 'rgba(255, 65, 55, 0.8)';
            default:
                return $default;
        }
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
        return $this->belongsTo(Staff::class, 'issue_owner')->withTrashed();
    }

    /**
     * An inverse one-to-many relationship with IssueStatus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(IssueStatus::class, 'issue_status_id');
    }

    /**
     * An inverse one-to-many relationship with IssueType.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(IssueType::class, 'issue_type_id');
    }

    /**
     * An inverse one-to-many relationship with Milestone.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function releasemilestone()
    {
        return $this->belongsTo(Milestone::class, 'release_milestone_id');
    }

    /**
     * An inverse one-to-many relationship with Milestone.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affectedmilestone()
    {
        return $this->belongsTo(Milestone::class, 'affected_milestone_id');
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
