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
use App\Models\Traits\ModuleTrait;
use App\Models\Traits\CalendarTrait;
use App\Models\Traits\ActivityTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Event extends BaseModel
{
    use SoftDeletes;
    use OwnerTrait;
    use ModuleTrait;
    use CalendarTrait;
    use ActivityTrait;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_owner', 'linked_id', 'linked_type', 'name',
        'start_date', 'end_date', 'location', 'description', 'priority', 'access',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'title', 'item', 'start', 'end', 'color', 'modal_size', 'period', 'show_route',
        'auth_can_view', 'auth_can_edit', 'auth_can_change_owner', 'auth_can_delete',
        'base_url', 'position_url', 'related_id', 'related_type', 'owner_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'start_date', 'end_date'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFields = [
        'priority'    => 'string:<span class=\'capitalize\'>%s</span>',
        'event_owner' => 'database:staff|id|name',
        'start_date'  => 'datetime:Y-m-d g:i a',
        'end_date'    => 'datetime:Y-m-d g:i a',
        'access'      => 'helper:readable_access',
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
        'access'      => 'Access',
        'description' => 'Description',
        'end_date'    => 'End Date',
        'name'        => 'Event Name',
        'event_owner' => 'Event Owner',
        'location'    => 'Location',
        'priority'    => 'Priority',
        'linked_type' => 'Related To',
        'linked_id'   => 'Related Name',
        'start_date'  => 'Start Date',
    ];

    /**
     * Fields name array that can be mass updatable.
     *
     * @var array
     */
    protected static $mass_fieldlist = [
        'access', 'description', 'end_date', 'name', 'event_owner', 'location', 'priority', 'linked_type', 'start_date',
    ];

    /**
     * Fields name array that uses to filter data.
     *
     * @var array
     */
    protected static $filter_fieldlist = [
        'access', 'description', 'end_date', 'name', 'event_owner', 'location', 'priority', 'linked_type', 'start_date',
    ];

    /**
     * Event validation.
     *
     * @param array                  $data
     * @param \App\Models\Event|null $event
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data, $event = null)
    {
        $data['start_date'] = ampm_to_sql_datetime($data['start_date']);
        $data['end_date']   = ampm_to_sql_datetime($data['end_date']);

        $rules = [
            'name'         => 'required|max:200',
            'location'     => 'max:200',
            'event_owner'  => 'required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
            'related_type' => 'in:' . implode(',', self::$related_types),
            'priority'     => 'in:' . implode(',', self::$priority_list),
            'description'  => 'max:65535',
            'access'       => 'required|in:private,public,public_rwd',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('related_type', $data) && ! empty($data['related_type'])) {
            $related_ids = implode(',', morph_to_model($data['related_type'])::getAuthPermittedIds('event'));
            $related_ids = is_null($event) ? $related_ids : $related_ids . ',' . $event->linked_id;
            $rules['related_id'] = "required|exists:{$data['related_type']}s,id,deleted_at,NULL|in:$related_ids";
        }

        return validator($data, $rules);
    }

    /**
     * Event single field update validation.
     *
     * @param array                  $data
     * @param \App\Models\Event|null $event
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function singleValidate($data, $event = null)
    {
        $message    = [];
        $after_date = '';

        if (array_key_exists('end_date', $data)
            && ! empty($data['end_date'])
            && ! is_null($event)
            && not_null_empty($event->start_date)
        ) {
            $after_date = 'after:' . date('Y-m-d', strtotime($event->start_date . ' -1 day'));
        }

        $rules = [
            'name'        => 'sometimes|required|max:200',
            'location'    => 'max:200',
            'event_owner' => 'sometimes|required|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
            'start_date'  => 'sometimes|required|date',
            'end_date'    => 'sometimes|required|date|' . $after_date,
            'linked_type' => 'in:' . implode(',', self::$related_types),
            'priority'    => 'in:' . implode(',', self::$priority_list),
            'description' => 'max:65535',
            'access'      => 'sometimes|required|in:private,public,public_rwd',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
            $related_ids        = implode(',', morph_to_model($data['linked_type'])::getAuthPermittedIds('event'));
            $related_rule       = ! is_null($event) ? 'in:' . $related_ids . ',' . $event->linked_id : '';
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL|{$related_rule}";
        }

        // The auth user can only change ower if the auth user has "change owner" permission of the module.
        if (array_key_exists('change_owner', $data) && $data['change_owner']
            && isset($event)
            && ! $event->auth_can_change_owner
        ) {
            $data['event_owner'] = 0;
            $message['event_owner.exists'] = 'You don\'t have permission to change owner';
        }

        return validator($data, $rules, $message);
    }

    /**
     * Event mass update validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function massValidate($data)
    {
        $rules = [
            'location'    => 'max:200',
            'description' => 'max:65535',
            'priority'    => 'in:' . implode(',', self::$priority_list),
            'linked_type' => 'in:' . implode(',', self::$related_types),
            'related'     => 'required|in:' . implode(',', self::massfieldlist()),
            'name'        => 'required_if:related,name|max:200',
            'start_date'  => 'required_if:related,start_date|date',
            'end_date'    => 'required_if:related,end_date|date',
            'access'      => 'required_if:related,access|in:private,public,public_rwd',
            'event_owner' => 'required_if:related,event_owner|' .
                             'exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL',
        ];

        // If posted data has a related module then validation rules for only user permitted module ids are accepted.
        if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
            $related_ids = implode(',', morph_to_model($data['linked_type'])::getAuthPermittedIds('event'));
            $rules['linked_id'] = "required|exists:{$data['linked_type']}s,id,deleted_at,NULL|in:{$related_ids}";
        }

        return validator($data, $rules);
    }

    /**
     * Event filter data validation.
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
                'name'      => 'location',
                'type'      => 'string',
                'condition' => 'required|array|max:200',
            ],
            [
                'name'      => 'priority',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:' . implode(',', self::$priority_list),
            ],
            [
                'name'      => 'event_owner',
                'type'      => 'dropdown',
                'condition' => 'required|array|in:0,' . implode(',', $owners),
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
     * Get a valid priority list.
     *
     * @return array
     */
    public static function prioritylist()
    {
        return self::$priority_list;
    }

    /**
     * Get user upcoming event list.
     *
     * @param string     $owner_condition
     * @param string     $start
     * @param string     $end
     * @param array|null $owner
     *
     * @return \App\Models\Event
     */
    public static function getAuthUpcomingEventsList($owner_condition, $start, $end, $owner = null)
    {
        $today  = date('Y-m-d');
        $events = self::getAuthViewData()->conditionalFilterQuery('event_owner', $owner_condition, $owner);

        if ($start > $today) {
            $events = $events->withinPeriod($start, $end);
        } else {
            $events = $events->where('start_date', '>', $today);
        }

        return $events->orderBy('start_date')->get();
    }

    /**
     * Get the event attendee show page link.
     *
     * @param \App\Models\EventAttendee $attendee
     * @param string                    $prefix
     *
     * @return string
     */
    public static function getAttendeeHtml($attendee, $prefix = null)
    {
        $route = $attendee->linked_type == 'staff' ? 'admin.user.show' : 'admin.' . $attendee->linked_type . '.show';
        $title = isset($prefix) ? $prefix . $attendee->linked->name : $attendee->linked->name;

        return "<a href='" . route($route, $attendee->linked_id) . "' class='avatar-link' data-toggle='tooltip'
                    data-placement='top' title='" . fill_up_space($title) . "'>" .
                    "<img src='{$attendee->linked->avatar}'>" .
                '</a>';
    }

    /**
     * Get rest attendees HTML.
     *
     * @param int $start_key
     *
     * @return string
     */
    public function getRestAttendees($start_key)
    {
        $html = '';

        foreach ($this->attendees as $key => $attendee) {
            if ($key >= $start_key) {
                $html .= str_replace('"', '', str_replace("'", "", $attendee->linked->name)) . '<br>';
            }

            if ($key == 11) {
                break;
            }
        }

        if ($this->attendees->count() > 12) {
            $html .= '+' . ($this->attendees->count() - 12) . ' attendees ...';
        }

        return $html;
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

        $columns = [
            'name', 'start_date', 'end_date', 'location', 'priority',
            'related_to', 'attendee' , 'event_owner', 'action',
        ];

        $thead = [
            'event name', 'start date', 'end date', 'location', 'priority', 'related to',
            ['attendees', 'style' => 'min-width: 120px'], 'owner',
        ];

        // If mass action is allowed then add mass select checkbox column.
        if ($allow_mass_action) {
            array_unshift($columns, 'checkbox');
        }

        return [
            'thead'        => $thead,
            'checkbox'     => $allow_mass_action,
            'action'       => self::allowAction(),
            'columns'      => $columns,
            'json_columns' => \DataTable::jsonColumn($columns, self::hideColumns()),
        ];
    }

    /**
     * Get resource table data.
     *
     * @param \App\Models\Event        $events
     * @param \Illuminate\Http\Request $request
     * @param bool                     $cmn_action
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($events, $request, $cmn_action = false)
    {
        return \DataTable::of($events)->addColumn('checkbox', function ($event) {
            return $event->checkbox_html;
        })->editColumn('name', function ($event) {
            return $event->name_html;
        })->editColumn('start_date', function ($event) {
            return $event->readableDateHtml('start_date', true);
        })->editColumn('end_date', function ($event) {
            return $event->readableDateHtml('end_date', true);
        })->editColumn('priority', function ($event) {
            return $event->plain_priority;
        })->addColumn('related_to', function ($event) {
            return non_property_checker($event->linked, 'name_link_icon');
        })->addColumn('attendee', function ($event) {
            return $event->attendees_html;
        })->editColumn('event_owner', function ($event) {
            return $event->owner_html;
        })->addColumn('action', function ($event) use ($cmn_action) {
            return $event->getActionHtml('Event', 'admin.event.destroy', null, [
                'edit'   => $event->auth_can_edit,
                'delete' => $event->auth_can_delete,
            ], $cmn_action);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->globalSearch($request, [
                    'name', 'start_date_html', 'end_date_html', 'location', 'priority', 'related_name', 'owner_name',
                ]);
            });
        })->make(true);
    }

    /**
     * Get event attendee data table format.
     *
     * @return array
     */
    public static function getAttendeeTableFormat()
    {
        $table = [
            'json_columns' => \DataTable::jsonColumn(['name', 'phone', 'email', 'type', 'action']),
            'thead'        => ['NAME', 'PHONE', 'EMAIL', 'TYPE'],
            'checkbox'     => false,
            'action'       => true,
        ];

        $table['filter_input']['type'] = [
            'type'      => 'dropdown',
            'no_search' => true,
            'options'   => ['all' => 'All Attendees', 'staff' => 'Users'],
        ];

        return $table;
    }

    /**
     * Get 'event attendees' table JSON column according to the auth user permission.
     *
     * @return string
     */
    public function getAttendeeJsonColumnAttribute()
    {
        $default_hide_columns = $this->auth_can_edit ? [] : ['action'];

        return \DataTable::jsonColumn(['name', 'phone', 'email', 'type', 'action'], [], $default_hide_columns);
    }

    /**
     * Get event attendee table data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendeeData($request)
    {
        return \DataTable::of($this->attendees)->addColumn('name', function ($attendee) {
            return $attendee->linked->profile_html;
        })->addColumn('phone', function ($attendee) {
            return $attendee->linked->phone;
        })->addColumn('email', function ($attendee) {
            return $attendee->linked->email;
        })->addColumn('type', function ($attendee) {
            return $attendee->display_type;
        })->addColumn('action', function ($attendee) {
            return $attendee->getActionHtml('Attendee', 'admin.event.attendee.destroy', null, [
                'edit'   => false,
                'delete' => $this->auth_can_edit,
            ], true);
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                $status = $row->linked->globalSearch($request, ['name', 'email', 'phone']) || $row->globalSearch($request, ['display_type']);

                if ($request->has('type') && $request->type != 'all' && $request->type != $row->linked_type) {
                    $status = false;
                }

                return $status;
            });
        })->make(true);
    }

    /**
     * Get event calendar 'Filter View' list array.
     *
     * @return array
     */
    public static function getCalendarFilterList()
    {
        return [
            'events'         => 'Events',
            'my_events'      => 'My Events',
            'my_acts'        => 'My Activities',
            'my_open_acts'   => 'My Open Activities',
            'my_closed_acts' => 'My Closed Activities',
            'acts'           => 'All Activities',
            'open_acts'      => 'Open Activities',
            'closed_acts'    => 'Closed Activities',
        ];
    }

    /**
     * Get calendar filter parameter value.
     *
     * @return string
     */
    public static function getCalendarFilterParam()
    {
        $default = 'my_events';

        if (session()->has('calendar_filter')
            && array_key_exists(session('calendar_filter'), self::getCalendarFilterList())
        ) {
            return session('calendar_filter');
        }

        return $default;
    }

    /**
     * Get the calendar 'Filter View' HTML dropdown list.
     *
     * @return string
     */
    public static function getCalendarFilterDropdown()
    {
        $filter_list  = self::getCalendarFilterList();
        $fiiter_param = self::getCalendarFilterParam();
        $dropdown     = '';

        foreach ($filter_list as $filter => $display_text) {
            $selected  = ($filter == $fiiter_param) ? 'selected' : '';
            $dropdown .= "<option value='{$filter}' {$selected}>{$display_text}</option>";
        }

        return $dropdown;
    }

    /**
     * Get the auth user calendar data.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAuthCalendarData()
    {
        $tasks  = Task::getAuthViewData();
        $issues = Issue::getAuthViewData();
        $events = self::getAuthViewData();
        $filter = self::getCalendarFilterParam();

        // If "Filter View" is all activities then get all activities without filtering data.
        if (is_null($filter) || $filter == 'acts') {
            return collection_merge([$tasks->get(), $issues->get(), $events->get()]);
        }

        // If the owner is the auth user.
        if (strpos($filter, 'my') !== false) {
            $tasks  = $tasks->where('task_owner', auth_staff()->id);
            $issues = $issues->where('issue_owner', auth_staff()->id);
            $events = $events->where('event_owner', auth_staff()->id);
        }

        switch ($filter) {
            case 'events':
            case 'my_events':
                return $events->get();
            case 'my_acts':
                return collection_merge([$tasks->get(), $issues->get(), $events->get()]);
            case 'open_acts':
            case 'my_open_acts':
                return collection_merge([$tasks->onlyOpen()->get(), $issues->onlyOpen()->get()]);
            case 'closed_acts':
            case 'my_closed_acts':
                return collection_merge([$tasks->onlyClosed()->get(), $issues->onlyClosed()->get()]);
            default:
                return collect();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATOR
    |--------------------------------------------------------------------------
    */
    /**
     * Set the event's location.
     *
     * @param string $value
     *
     * @return string
     */
    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = str_replace(["'", '"'], '', $value);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get event name HTML.
     *
     * @return string
     */
    public function getNameHtmlAttribute()
    {
        $tooltip = strlen($this->name) > 50 ? "data-toggle='tooltip' data-placement='top' title='{$this->name}'" : null;

        return "<a href='{$this->show_route}' {$tooltip}>" . str_limit($this->name, 50) . '</a>';
    }

    /**
     * Get start date readable HTML.
     *
     * @return string
     */
    public function getStartDateHtmlAttribute()
    {
        return $this->readableDateHtml('start_date', true);
    }

    /**
     * Get end date readable HTML.
     *
     * @return string
     */
    public function getEndDateHtmlAttribute()
    {
        return $this->readableDateHtml('end_date', true);
    }

    /**
     * Get a display period.
     *
     * @return string
     */
    public function getPeriodAttribute()
    {
        $period = null;

        if ($this->start_date->toDateString() == $this->end_date->toDateString()) {
            $period = $this->start_date->format('D, M j, Y g:i A') . ' - ' . $this->end_date->format('g:i A');
        } else {
            $period = $this->start_date->toDayDateTimeString() . ' - ' . $this->end_date->toDayDateTimeString();
        }

        return $period;
    }

    /**
     * Get to know the auth user can view the specified resource or not.
     *
     * @return bool
     */
    public function getAuthCanViewAttribute()
    {
        if ($this->authAttendee()->count() && permit('event.view')) {
            return true;
        }

        return $this->authCan('view');
    }

    /**
     * Get the auth user attendee data.
     *
     * @return \App\Models\EventAttendee
     */
    public function authAttendee()
    {
        return $this->attendees()
                    ->where('linked_type', auth()->user()->linked_type)
                    ->where('linked_id', auth()->user()->linked_id)
                    ->get();
    }

    /**
     * Get the event attendees list.
     *
     * @return array
     */
    public function getAttendeesListAttribute()
    {
        return $this->attendees->pluck('id_type')->toArray();
    }

    /**
     * Get event attendees HTML.
     *
     * @param bool   $tooltip
     * @param int    $limit
     * @param string $prefix
     *
     * @return string
     */
    public function getAttendeesHtmlAttribute($tooltip = true, $limit = 2, $prefix = null)
    {
        $attendees_html = null;
        $tooltip        = isset($tooltip) ? $tooltip : true;
        $limit          = isset($limit) ? $limit : 2;

        // By default show 3 attendees and if more than three then show two and a link for show rest attendees.
        foreach ($this->attendees as $key => $attendee) {
            if ($key < $limit) {
                $attendees_html .= self::getAttendeeHtml($attendee, $prefix);

                if ($key == ($limit - 1)) {
                    if ($this->attendees->count() == ($limit + 1)) {
                        $attendees_html .= self::getAttendeeHtml($this->attendees[$limit], $prefix);
                    } elseif ($this->attendees->count() > ($limit + 1)) {
                        $count           = $this->attendees->count() - $limit;
                        $rest_attendees  = $this->getRestAttendees($limit);
                        $tooltip         = $tooltip ? "data-toggle='tooltip' data-placement='top' data-html='true' title='{$rest_attendees}'" : '';
                        $attendees_html .= "<a class='avatar-link further add-multiple' $tooltip
                                               modal-title='Event Attendees' modal-sub-title='{$this->name}'
                                               modal-datatable='true' datatable-url='event-attendee-data/{$this->id}'
                                               datatable-col='{$this->attendee_json_column}' data-action=''
                                               datatable-addurl='event-attendee-store/{$this->id}' save-new='false-all'
                                               data-content='event.partials.modal-event-attendee' cancel-txt='Close'>" .
                                               $count .
                                           "</a>";
                    }

                    break;
                }
            }
        }

        return $attendees_html;
    }

    /**
     * Get attendees has owner status.
     *
     * @return bool
     */
    public function getAttendeesHasOwnerAttribute()
    {
        if ($this->attendees->count()) {
            $attendees_has_owner = $this->attendees
                                        ->where('linked_type', 'staff')
                                        ->where('linked_id', $this->event_owner)
                                        ->count();

            return $attendees_has_owner > 0;
        }

        return false;
    }

    /**
     * Get event owner and attendees HTML.
     *
     * @return string
     */
    public function getOwnerAttendeesHtmlAttribute()
    {
        $attendees_html = "<a href='" . route('admin.user.show', $this->event_owner) . "' class='avatar-link'
                              data-toggle='tooltip' data-placement='top'
                              title='" . fill_up_space('Owner : ' . $this->owner->name) . "'>" .
                              "<img src='{$this->owner->avatar}'>
                          </a>";

        if ($this->attendees->count() == 0) {
            return $attendees_html;
        } elseif (! $this->attendees_has_owner) {
            $attendees_html .= $this->getAttendeesHtmlAttribute(false, 1, 'Attendee : ');

            return $attendees_html;
        }

        $event_owner = $this->event_owner;
        $attendees   = $this->attendees()->where(function ($query) use ($event_owner) {
            $query->where('linked_type', 'staff')->where('linked_id', '!=', $event_owner);
        })->orWhere('linked_type', '!=', 'staff')->get();

        if ($attendees->count()) {
            $count = $attendees->count() - 1;
            $attendees_html .= self::getAttendeeHtml($attendees->first(), 'Attendee : ');

            if ($count > 0) {
                if ($count == 1) {
                    $attendees_html .= self::getAttendeeHtml($attendees->get(1));
                } else {
                    $attendees_html .= "<a class='avatar-link further add-multiple' modal-title='Event Attendees'
                                           modal-sub-title='{$this->name}' data-action='' modal-datatable='true'
                                           datatable-url='event-attendee-data/{$this->id}'
                                           datatable-col='{$this->attendee_json_column}'
                                           datatable-addurl='event-attendee-store/{$this->id}' save-new='false-all'
                                           data-content='event.partials.modal-event-attendee' cancel-txt='Close'>" .
                                           $count .
                                        '</a>';
                }
            }
        }

        return $attendees_html;
    }

    /**
     * Get event calendar item HTML.
     *
     * @return string
     */
    public function getCalendarShellAttribute()
    {
        $at_location = isset($this->location) ? ' @ ' . $this->location : '';
        $day = $this->start_date->format('j');

        if ($this->start_date->format('Y-m') == $this->end_date->format('Y-m')
            && $this->start_date->format('j') != $this->end_date->format('j')
        ) {
            $day = $this->start_date->format('j') . ' - ' . $this->end_date->format('j');
        }

        $shell = "<div class='calendar-shell'>
                    <div class='cal-date'>
                        <h3 class='month'>{$this->start_date->format('M')}</h3>
                        <p>{$day}</p>
                    </div>

                    <div class='cal-info'>
                        <p class='link'>{$this->name_link_icon}</p>
                        <p>
                            <i class='icon mdi mdi-clock-outline'></i> <span class='shadow'>" .
                            $this->start_date->format('g:i A') . $at_location . '</span>
                        </p>
                        <p>' . non_property_checker($this->linked, 'name_link_icon') . '</p>
                    </div>
                </div>';

        return $shell;
    }

    /**
     * Get total attendees of this event.
     *
     * @return int
     */
    public function getTotalAttendeesAttribute()
    {
        return $this->attendees->count();
    }

    /**
     * Get total attendees HTML.
     *
     * @return string
     */
    public function getTotalAttendeesHtmlAttribute()
    {
        return "<a class='link-center-underline add-multiple' modal-datatable='true' modal-title='Event Attendees'
                   modal-sub-title='{$this->name}' data-action='' data-content='event.partials.modal-event-attendee'
                   datatable-url='event-attendee-data/{$this->id}' datatable-col='{$this->attendee_json_column}'
                   datatable-addurl='event-attendee-store/{$this->id}' save-new='false-all' cancel-txt='Close'>" .
                   $this->total_attendees .
               '</a>';
    }

    /**
     * Get classified total attendees CSS class.
     *
     * @return string
     */
    public function getClassifiedTotalAttendeesAttribute()
    {
        if ($this->total_attendees >= 0 && $this->total_attendees <= 10) {
            $css = 'cold';
        } elseif ($this->total_attendees > 10 && $this->total_attendees <= 50) {
            $css = 'warm';
        } elseif ($this->total_attendees > 50) {
            $css = 'hot';
        } else {
            $css = 'cold';
        }

        return "<span class='$css counter' data-value='{$this->total_attendees}'>{$this->total_attendees}</span>";
    }

    /**
     * Get event duration with the time unit.
     *
     * @return array
     */
    public function getDurationAttribute()
    {
        $minute_duration = abs($this->end_date->diffInMinutes($this->carbonDate('start_date'), false));
        $hour_duration   = ($minute_duration / 60);
        $duration        = abs($this->end_date->diffInHours($this->carbonDate('start_date'), false));
        $unit            = str_plural('hr', $duration);

        // Duration show in minutes|hours|days depends on duration length.
        if ($duration == 0) {
            $duration = $minute_duration;
            $unit     = 'min';
        } elseif ($minute_duration > 60 && ! is_int($hour_duration) && $duration < 24) {
            $hour     = floor($hour_duration);
            $minute   = $hour_duration - $hour;
            $minute   = round($minute * 60);
            $duration = [$hour, $minute];
            $unit     = [$unit, 'min'];
        } elseif ($duration > 24 && ! is_array($duration)) {
            $duration = abs($this->end_date->diffInDays($this->carbonDate('start_date'), false)) + 1;
            $unit     = str_plural('day', $duration);
        }

        return ['value' => $duration, 'unit' => $unit];
    }

    /**
     * Get event duration HTML.
     *
     * @return string
     */
    public function getDurationHtmlAttribute()
    {
        if (! is_array($this->duration['value'])) {
            return $this->duration['value'] . ' ' . $this->duration['unit'];
        }

        return $this->duration['value'][0] . ' ' . $this->duration['unit'][0] . ' ' .
               $this->duration['value'][1] . ' ' . $this->duration['unit'][1];
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
        return $this->belongsTo(Staff::class, 'event_owner')->withTrashed();
    }

    /**
     * A one-to-many relationship with EventAttendee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendees()
    {
        return $this->hasMany(EventAttendee::class)->groupBy('linked_type', 'linked_id');
    }

    /**
     * A polymorphic, inverse one-to-many relationship with Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo();
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
