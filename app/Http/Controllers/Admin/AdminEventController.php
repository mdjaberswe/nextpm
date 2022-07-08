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

namespace App\Http\Controllers\Admin;

use Notification;
use App\Models\Event;
use App\Models\Staff;
use App\Models\Revision;
use App\Models\FilterView;
use App\Models\EventAttendee;
use App\Jobs\SaveAllowedStaff;
use App\Jobs\SaveEventAttendee;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminEventController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:event.view', ['only' => ['index']]);
        $this->middleware('admin:event.create', ['only' => ['store']]);
        $this->middleware('admin:event.edit', ['only' => ['edit', 'update', 'bulkUpdate', 'eventAttendeeStore', 'eventAttendeeDestroy']]);
        $this->middleware('admin:mass_update.event', ['only' => ['bulkUpdate']]);
        $this->middleware('admin:event.delete', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('admin:mass_delete.event', ['only' => ['bulkDestroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Page information like title, user permission, current filter, breadcrumb,
        // and resource table format(heading, columns).
        $page = [
            'title'          => 'Events List',
            'item'           => 'Event',
            'field'          => 'events',
            'view'           => 'admin.event',
            'route'          => 'admin.event',
            'permission'     => 'event',
            'bulk'           => 'update',
            'script'         => true,
            'filter'         => true,
            'current_filter' => FilterView::getCurrentFilter('event'),
            'breadcrumb'     => FilterView::getBreadcrumb('event'),
            'export'         => permit('export.event'),
            'data_default'   => 'event_owner:' . auth_staff()->id,
        ];

        $table = Event::getTableFormat();

        return view('admin.event.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventData(Request $request)
    {
        // Response resource data in JSON format filter by the auth user view permission and current filter parameter.
        $events = Event::getAuthViewData()->filterViewData()->latest('id')->get();

        return Event::getTableData($events, $request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Event::validate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $event              = new Event;
            $event->name        = $request->name;
            $event->access      = $request->access;
            $event->event_owner = $request->event_owner;
            $event->start_date  = ampm_to_sql_datetime($request->start_date);
            $event->end_date    = ampm_to_sql_datetime($request->end_date);
            $event->description = null_if_empty($request->description);
            $event->location    = null_if_empty($request->location);
            $event->priority    = null_if_empty($request->priority);

            if (! empty($request->related_type)) {
                $event->linked_id   = $request->related_id;
                $event->linked_type = $request->related_type;
            }

            $event->save();

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'renderEvent' => $event->update_calendar, 'saveId' => $event->id]);
            dispatch(new SaveEventAttendee($event, $request->attendees));

            // Save allowed staff with permitted action.
            if ($request->access == 'private') {
                dispatch(new SaveAllowedStaff(
                    $request->staffs,
                    'event',
                    $event->id,
                    $request->can_write,
                    $request->can_delete
                ));
            }

            // Notify all users associated with this record.
            Notification::send(
                get_wherein('user', $event->notifees, [auth()->user()->id]),
                new CrudNotification('event_created', $event->id)
            );
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     * @param string|null              $infotype
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Event $event, $infotype = null)
    {
        // If the auth user has permission to view this record then show the page
        // and pass $page variable with title, breadcrumb, tabs information.
        if ($event->auth_can_view) {
            $page = [
                'title'       => 'Event: ' . $event->name,
                'item_title'  => $event->show_page_breadcrumb,
                'item'        => 'Event',
                'view'        => 'admin.event',
                'tabs'        => [
                    'list'    => Event::informationTypes(),
                    'default' => Event::defaultInfoType($infotype),
                    'item_id' => $event->id,
                    'url'     => 'tab/event',
                ],
            ];

            return view('admin.event.show', compact('page', 'event'));
        }

        return redirect()->route('admin.event.index');
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Event $event)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;
            $html   = null;

            // If the specified resource is valid
            // and the auth user has permission to edit then follow the next execution.
            if (isset($event) && isset($request->id) && $event->id == $request->id && $event->auth_can_edit) {
                $info                = $event->toArray();
                $info['attendees[]'] = $event->attendees_list;
                $info['start_date']  = $event->start_date->format('Y-m-d h:i A');
                $info['end_date']    = $event->end_date->format('Y-m-d h:i A');
                $info['show']        = [];
                $info['hide']        = [];
                $info['freeze']      = [];

                // If the auth user doesn't have permission to change "owner" then freeze "owner" field.
                if (! $event->auth_can_change_owner) {
                    $info['freeze'][] = 'event_owner';
                }

                // If the specified resource is related to a non-permitted module
                // then fix the dropdown in the proper format.
                if (! is_null($info['related_type'])) {
                    $rel_field        = $info['related_type'] . '_id';
                    $info[$rel_field] = $info['related_id'];
                    $info['show'][]   = $rel_field;
                    $permitted_related_ids = morph_to_model($info['related_type'])::getAuthPermittedIds('event');

                    if (! in_array($info['related_id'], $permitted_related_ids)) {
                        $info['selectlist'][$rel_field] = $event->fixRelatedDropdown($info['related_type'], [], true);
                    }
                }

                // Modal title link useful for calendar view.
                $info['modal_title_link'] = [
                    'href'  => route('admin.event.show', $event->id),
                    'title' => str_limit($info['name'], 70, '.'),
                ];

                // Modal footer delete button useful for calendar view.
                if ($event->auth_can_delete) {
                    $info['modal_footer_delete'] = [
                        'action' => route('admin.event.destroy', $event->id),
                        'id'     => $event->id,
                        'item'   => 'event',
                    ];
                }

                $info = (object) $info;

                // If the request for render form HTML and It is useful for the common modal.
                if (isset($request->html)) {
                    $html = view('admin.event.partials.form', ['form' => 'edit'])->render();
                }
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
        }

        return redirect()->route('admin.event.show', $event->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($event) && isset($request->id) && $event->id == $request->id && $event->auth_can_edit) {
            $validation = Event::validate($request->all(), $event);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if ($event->auth_can_change_owner) {
                    $event->event_owner = null_if_empty($request->event_owner);
                }

                $event->name        = $request->name;
                $event->access      = $request->access;
                $event->start_date  = ampm_to_sql_datetime($request->start_date);
                $event->end_date    = ampm_to_sql_datetime($request->end_date);
                $event->description = null_if_empty($request->description);
                $event->location    = null_if_empty($request->location);
                $event->priority    = null_if_empty($request->priority);

                if (not_null_empty($request->related_type)) {
                    $event->linked_id   = $request->related_id;
                    $event->linked_type = $request->related_type;
                } else {
                    $event->linked_id   = null;
                    $event->linked_type = null;
                }

                $event->update();

                // Delete all allowed users if request access is not private.
                if ($request->access != 'private') {
                    $event->allowedstaffs()->forceDelete();
                }

                // Ajax quick response for not delaying execution.
                flush_response(['status' => true, 'updateEvent' => $event->update_calendar, 'saveId' => $request->id]);

                // Save event attendees job.
                dispatch(new SaveEventAttendee($event, $request->attendees));

                // Notify all users associated with this record.
                if (count($event->notifees)
                    && count($event->newUpdatedArray())
                    && $event->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $event->notifees, [auth()->user()->id]),
                        new CrudNotification('event_updated', $event->id, $event->newUpdatedArray())
                    );
                }
            } else {
                return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
            }
        } else {
            return response()->json(['status' => false, 'errors' => null]);
        }
    }

    /**
     * Update a single field of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     *
     * @return \Illuminate\Http\Response
     */
    public function singleUpdate(Request $request, Event $event)
    {
        $html          = null;
        $history       = null;
        $updated_by    = null;
        $last_modified = null;
        $realtime      = [];
        $real_replace  = [];
        $inner_html    = [];
        $data          = $request->all();

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($event) && $event->auth_can_edit) {
            $data['id'] = $event->id;
            $data['change_owner'] = $event->auth_can_change_owner;

            if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
                $related_field     = $data['linked_type'] . '_id';
                $data['linked_id'] = $data[$related_field];
            }

            $validation = Event::singleValidate($data, $event);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                $update_data = $request->all();

                if (isset($request->start_date)) {
                    $update_data['start_date'] = ampm_to_sql_datetime($request->start_date);
                }

                if (isset($request->end_date)) {
                    $update_data['end_date'] = ampm_to_sql_datetime($request->end_date);
                }

                $update_data = replace_null_if_empty($update_data);
                $event->update($update_data);

                // Realtime HTML content changes on the page according to the updated field.
                if (isset($request->access)) {
                    $html = $event->access_html;

                    if ($request->access != 'private') {
                        $event->allowedstaffs()->forceDelete();
                    }
                } elseif (isset($request->name)) {
                    $html = $event->name;
                } elseif (isset($request->location)) {
                    $html = $event->location;
                } elseif (isset($request->linked_type)) {
                    if (not_null_empty($request->linked_type)) {
                        $event->update(['linked_id' => $data['linked_id'], 'linked_type' => $data['linked_type']]);
                        $html = $event->fresh()->linked->name_link_icon;
                    } else {
                        $event->update(['linked_id' => null, 'linked_type' => null]);
                        $html = '';
                    }
                } elseif (isset($request->start_date)) {
                    $html = $event->readableDateAmPm('start_date');
                } elseif (isset($request->end_date)) {
                    $html = $event->readableDateAmPm('end_date');
                }

                if (isset($request->start_date) || isset($request->end_date)) {
                    $realtime[] = ['duration', $event->duration_html];
                }

                $inner_html[]  = ['.follower-container-box', $event->fresh()->display_followers, false];
                $inner_html[]  = ['.show-misc-actions', $event->fresh()->show_misc_actions, false];
                $history       = $event->recent_history_html;
                $updated_by    = "<p class='compact'>" . $event->updatedByName() . "<br>
                                    <span class='color-shadow sm'>" . $event->updated_ampm . "</span>
                                  </p>";
                $last_modified = "<p data-toggle='tooltip' data-placement='bottom'
                                     title='" . $event->readableDateAmPm('modified_at') . "'>" .
                                     time_short_form($event->modified_at->diffForHumans()) .
                                 "</p>";

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'       => true,
                    'updatedBy'    => $updated_by,
                    'innerHtml'    => $inner_html,
                    'lastModified' => $last_modified,
                    'realReplace'  => $real_replace,
                    'realtime'     => $realtime,
                    'history'      => $history,
                    'html'         => $html,
                ]);

                // Notify all users associated with this record.
                if (count($event->notifees)
                    && count($event->newUpdatedArray())
                    && $event->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $event->notifees, [auth()->user()->id]),
                        new CrudNotification('event_updated', $event->id, $event->newUpdatedArray())
                    );
                }
            } else {
                return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
            }
        } else {
            return response()->json(['status' => false, 'errors' => null]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Event $event)
    {
        // Valid specified resource and the auth user has to delete permission checker.
        if ($event->id != $request->id || ! $event->auth_can_delete) {
            return response()->json(['status' => false]);
        } else {
            $redirect = null;

            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                $prev = Event::getAuthViewData()->where('id', '>', $event->id)->get()->first();
                $next = Event::getAuthViewData()->where('id', '<', $event->id)->latest('id')->get()->first();

                if (isset($next)) {
                    $redirect = route('admin.event.show', $next->id);
                } elseif (isset($prev)) {
                    $redirect = route('admin.event.show', $prev->id);
                } else {
                    $redirect = route('admin.event.index');
                }
            }

            $notifees = $event->notifees;
            $event->delete();

            event(new \App\Events\EventDeleted([$request->id]));

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'eventId' => (int) $request->id, 'redirect' => $redirect]);

            // Notify all users associated with this record.
            Notification::send(
                get_wherein('user', $notifees, [auth()->user()->id]),
                new CrudNotification('event_deleted', $request->id)
            );
        }
    }

    /**
     * Remove mass resources from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(Request $request)
    {
        // Count requested resource data checker and only the auth user permitted data will be deleted.
        if (isset($request->events) && count($request->events)) {
            $del_ids = Event::whereIn('id', $request->events)->get()->where('auth_can_delete', true)->pluck('id')->toArray();
            Event::whereIn('id', $del_ids)->delete();
            event(new \App\Events\EventDeleted($del_ids));
            // Ajax quickly responds and notify all related users.
            flush_response(['status' => true]);
            $notifees = array_flatten(Event::withTrashed()->whereIn('id', $del_ids)->get()->pluck('notifees'));
            Notification::send(
                get_wherein('user', $notifees, [auth()->user()->id]),
                new CrudNotification('event_mass_removed', 0, ['count' => count($del_ids)])
            );
        } else {
            return response()->json(['status' => false]);
        }
    }

    /**
     * Update mass resources in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        // Count requested resource data and update related field checker.
        if (isset($request->events) && count($request->events) && isset($request->related)) {
            $validation = Event::massValidate($request->all());

            // Update mass data if validation passes.
            if ($validation->passes()) {
                // Update only user permitted data.
                $event_ids = Event::whereIn('id', $request->events)->get()->where('auth_can_edit', true);

                // If the requested field is "owner" then the auth user needs to have "change owner" permission.
                if ($request->related == 'event_owner') {
                    $event_ids = $event_ids->where('auth_can_change_owner', true);
                }

                $event_ids = $event_ids->pluck('id')->toArray();
                $events    = Event::whereIn('id', $event_ids);

                if (\Schema::hasColumn('events', $request->related)) {
                    $field       = $request->related;
                    $value       = null_if_empty($request->$field);
                    $update_data = [$field => $value];

                    // Check for not inserting problematic data.
                    if ($request->related == 'linked_type') {
                        $linked_field             = $request->linked_type . '_id';
                        $update_data['linked_id'] = $request->$linked_field;
                    } elseif ($request->related == 'start_date') {
                        $update_data['start_date'] = ampm_to_sql_datetime($request->start_date);
                        $events = $events->where('end_date', '>=', $update_data['start_date'])
                                         ->orWhere('end_date', null);
                    } elseif ($request->related == 'end_date') {
                        $update_data['end_date'] = ampm_to_sql_datetime($request->end_date);
                        $events = $events->where('start_date', '<=', $update_data['end_date'])
                                         ->orWhere('start_date', null);
                    }

                    // Get notifees, final event ids, and pre updated events to keep histories.
                    $notifees   = array_flatten($events->get()->pluck('notifees'));
                    $event_ids  = $events->pluck('id')->toArray();
                    $old_events = $events->get();

                    // Mass update, ajax quick response, mass updated histories, notify related users.
                    Event::whereIn('id', $event_ids)->update($update_data);
                    flush_response(['status' => true]);
                    Revision::secureBulkUpdatedHistory('event', $old_events, $update_data);
                    Notification::send(
                        get_wherein('user', $notifees, [auth()->user()->id]),
                        new CrudNotification('event_mass_changed', $event_ids, [
                            'field'       => display_field($field),
                            'key'         => $field == 'linked_type' ? 'linked_id' : $field,
                            'new_value'   => $field == 'linked_type' ? $update_data['linked_id'] : $value,
                            'old_value'   => null,
                            'count'       => count($event_ids),
                            'linked_type' => $request->linked_type,
                        ])
                    );
                }
            } else {
                return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
            }
        } else {
            return response()->json(['status' => false, 'errors' => null]);
        }
    }

    /**
     * Display a Calendar View of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function indexCalendar(Request $request)
    {
        // Page information like title, user permission, current filter, breadcrumb, etc.
        $page = [
            'title'               => 'Calendar',
            'item'                => 'Event',
            'modal_title_link'    => true,
            'modal_edit'          => false,
            'modal_bulk_update'   => false,
            'modal_bulk_delete'   => false,
            'filter_param'        => Event::getCalendarFilterParam(),
            'filter_dropdown'     => Event::getCalendarFilterDropdown(),
            'modal_footer_delete' => permit('event.delete'),
        ];

        return view('admin.event.calendar', compact('page'));
    }

    /**
     * JSON format resource data for Calendar View of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function calendarData(Request $request)
    {
        // Get calendar data filter by user view permission and current filter parameter.
        $calender_data = Event::getAuthCalendarData();

        return response()->json($calender_data);
    }

    /**
     * Update the calendar position of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePosition(Request $request)
    {
        $status = false;
        $errors = null;
        $event  = Event::find($request->id);

        // Check the specified resource and update the start, end date of this resource.
        if (isset($event)) {
            $status     = true;
            $start_date = substr($request->start, 0, 10);
            $end_date   = substr($request->end, 0, 10);

            if ($start_date != $end_date) {
                $end_date = get_date_from(1, $end_date);
            }

            $event->start_date = $start_date . substr($event->start, 10);
            $event->end_date = $end_date . substr($event->end, 10);
            $event->save();
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
    }

    /**
     * Set the calendar filter parameter.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function setCalendarFilter(Request $request)
    {
        $status = false;
        $filter = $request->calendar_filter;

        // If a valid calendar "view" then update the current calendar filter view.
        if (not_null_empty($filter) && array_key_exists($filter, Event::getCalendarFilterList())) {
            session(['calendar_filter' => $filter]);
            $status = true;
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Get calendar data by related parent module.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     * @param string                   $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function relatedCalendarData(Request $request, $module_name, $module_id)
    {
        $calendar_data = [];
        $module = morph_to_model($module_name)::find($module_id);

        if (isset($module)) {
            $calendar_data = $module->getCalendarData();
        }

        return response()->json($calendar_data);
    }

    /**
     * JSON format listing data according to the related parent module of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     * @param int                      $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function connectedEventData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::find($module_id);

        // If the parent module exists then get the child resource data filter by the auth user view permission.
        if (isset($module)) {
            $events = $module->events()->authViewData()->filterMask()->latest('events.id')->get();

            return Event::getTableData($events, $request, true);
        }

        return null;
    }

    /**
     * Get specified resource attendees data for a modal data table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     *
     * @return \Illuminate\Http\Response
     */
    public function eventAttendeeData(Request $request, Event $event)
    {
        return $event->getAttendeeData($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event        $event
     *
     * @return \Illuminate\Http\Response
     */
    public function eventAttendeeStore(Request $request, Event $event)
    {
        $status     = true;
        $errors     = null;
        $inner_html = [];
        $rules      = ['attendees' => 'required|array'];
        $validation = validator($request->all(), $rules);

        // Add attendees to the "Event attendees" table if validation passes.
        if ($validation->passes()) {
            dispatch(new SaveEventAttendee($event, $request->attendees, true));

            $attendees_html = $event->fresh()->attendees_html
                              ? $event->fresh()->attendees_html
                              : "<span class='color-shadow l-space1'>--</span>";
            $inner_html[] = ["[data-realtime='attendees']", $attendees_html, false];
            $inner_html[] = ["[data-realtime='total_attendees']", $event->fresh()->total_attendees_html, false];
            $inner_html[] = ["[data-realtime='no_of_attendees']", $event->fresh()->classified_total_attendees, false];
            $inner_html[] = ['.follower-container-box', $event->fresh()->display_followers];
            $inner_html[] = ['.show-misc-actions', $event->fresh()->show_misc_actions];
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json([
            'status'    => $status,
            'errors'    => $errors,
            'innerHtml' => $inner_html
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\EventAttendee $event_attendee
     *
     * @return \Illuminate\Http\Response
     */
    public function eventAttendeeDestroy(Request $request, EventAttendee $event_attendee)
    {
        // Valid specified resource and the auth user has to delete permission checker.
        if ($event_attendee->id != $request->id || ! $event_attendee->event->auth_can_edit) {
            return response()->json(['status' => false]);
        } else {
            $event = $event_attendee->event;
            $event_attendee->delete();

            $attendees_html = $event->fresh()->attendees_html
                              ? $event->fresh()->attendees_html
                              : "<span class='color-shadow l-space1'>--</span>";
            $inner_html[] = ["[data-realtime='attendees']", $attendees_html, false];
            $inner_html[] = ["[data-realtime='total_attendees']", $event->fresh()->total_attendees_html, false];
            $inner_html[] = ["[data-realtime='no_of_attendees']", $event->fresh()->classified_total_attendees, false];
            $inner_html[] = ['.follower-container-box', $event->fresh()->display_followers];
            $inner_html[] = ['.show-misc-actions', $event->fresh()->show_misc_actions];

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'modalDatatable' => true, 'innerHtml' => $inner_html]);
        }
    }
}
