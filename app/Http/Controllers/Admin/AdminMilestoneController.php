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
use App\Models\Staff;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\FilterView;
use App\Models\AllowedStaff;
use App\Jobs\SaveAllowedStaff;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminMilestoneController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
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
        $validation = Milestone::validate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position                   = Milestone::getTargetPositionVal(-1);
            $milestone                  = new Milestone;
            $milestone->position        = $position;
            $milestone->name            = $request->name;
            $milestone->access          = $request->access;
            $milestone->milestone_owner = $request->milestone_owner;
            $milestone->project_id      = $request->project_id;
            $milestone->start_date      = $request->start_date;
            $milestone->end_date        = $request->end_date;
            $milestone->description     = null_if_empty($request->description);
            $milestone->save();

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'      => true,
                'gantt'       => true,
                'saveId'      => $milestone->id,
                'renderEvent' => $milestone->update_calendar,
            ]);

            // Save allowed staff with permitted action.
            if ($request->access == 'private') {
                dispatch(new SaveAllowedStaff(
                    $request->staffs,
                    'milestone',
                    $milestone->id,
                    $request->can_write,
                    $request->can_delete
                ));
            }

            // Notify all users associated with this record.
            Notification::send(
                get_wherein('user', $milestone->notifees, [auth()->user()->id]),
                new CrudNotification('milestone_created', $milestone->id)
            );
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Milestone    $milestone
     * @param string|null              $infotype
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Milestone $milestone, $infotype = null)
    {
        // If the auth user has permission to view this record then show the page
        // and pass $page variable with title, breadcrumb, tabs information.
        if ($milestone->auth_can_view) {
            $page = [
                'title'       => 'Milestone: ' . $milestone->name,
                'item_title'  => $milestone->breadcrumb_title,
                'item'        => 'Milestone',
                'view'        => 'admin.milestone',
                'tabs'        => [
                    'list'    => Milestone::informationTypes(),
                    'default' => Milestone::defaultInfoType($infotype),
                    'item_id' => $milestone->id,
                    'url'     => 'tab/milestone',
                ],
            ];

            return view('admin.milestone.show', compact('page', 'milestone'));
        }

        return redirect()->route('admin.project.show', [$milestone->project_id, 'milestones']);
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Milestone    $milestone
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Milestone $milestone)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;
            $html   = null;

            // If the specified resource is valid then follow the next execution.
            if (isset($milestone) && isset($request->id) && $milestone->id == $request->id) {
                $info = $milestone->toArray();
                $info['freeze'] = [];

                // If the auth user doesn't have permission to change "owner" then freeze "owner" field.
                if (! $milestone->auth_can_change_owner) {
                    $info['freeze'][] = 'milestone_owner';
                }

                // Modal title link useful for calendar or gantt view.
                $info['modal_title_link'] = [
                    'href'  => route('admin.milestone.show', $milestone->id),
                    'title' => str_limit($info['name'], 70, '.'),
                ];

                // Modal footer delete button useful for calendar or gantt view.
                if ($milestone->auth_can_delete) {
                    $info['modal_footer_delete'] = [
                        'action' => route('admin.milestone.destroy', $milestone->id),
                        'id'     => $milestone->id,
                    ];
                }

                // If the specified resource is related to a non-permitted project
                // then fix the dropdown in the proper format.
                $permitted_project_ids = Project::getAuthPermittedIds('milestone');

                if (! in_array($info['project_id'], $permitted_project_ids)) {
                    $info['selectlist']['project_id'] = $milestone->fixRelatedDropdown('project', [], true);
                }

                $info = (object) $info;

                // If the request for render form HTML and It is useful for the common modal.
                if (isset($request->html)) {
                    $html = view('admin.milestone.partials.form', ['form' => 'edit'])->render();
                }
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
        }

        return redirect()->route('admin.milestone.show', $milestone->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Milestone    $milestone
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Milestone $milestone)
    {
        // If the specified resource is valid then follow the next execution.
        if (isset($milestone) && isset($request->id) && $milestone->id == $request->id) {
            $validation = Milestone::validate($request->all(), $milestone);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if ($milestone->auth_can_change_owner) {
                    $milestone->milestone_owner = $request->milestone_owner;
                }

                $milestone->name        = $request->name;
                $milestone->access      = $request->access;
                $milestone->project_id  = $request->project_id;
                $milestone->start_date  = $request->start_date;
                $milestone->end_date    = $request->end_date;
                $milestone->description = null_if_empty($request->description);
                $milestone->update();

                // Delete all allowed users if request access is not private.
                if ($request->access != 'private') {
                    $milestone->allowedstaffs()->forceDelete();
                }

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'      => true,
                    'gantt'       => true,
                    'updateEvent' => $milestone->update_calendar,
                    'saveId'      => $request->id,
                ]);

                // Notify all users associated with this record.
                if (count($milestone->notifees)
                    && count($milestone->newUpdatedArray())
                    && $milestone->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $milestone->notifees, [auth()->user()->id]),
                        new CrudNotification('milestone_updated', $milestone->id, $milestone->newUpdatedArray())
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
     * @param \App\Models\Milestone    $milestone
     *
     * @return \Illuminate\Http\Response
     */
    public function singleUpdate(Request $request, Milestone $milestone)
    {
        $realtime      = [];
        $real_replace  = [];
        $inner_html    = [];
        $edit_false    = [];
        $html          = null;
        $history       = null;
        $updated_by    = null;
        $last_modified = null;
        $data          = $request->all();

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($milestone) && $milestone->auth_can_edit) {
            $data['id'] = $milestone->id;
            $data['change_owner'] = (isset($request->milestone_owner) && $milestone->auth_can_change_owner);
            $validation = Milestone::singleValidate($data, $milestone);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                $update_data = replace_null_if_empty($request->all());
                $milestone->update($update_data);

                // Realtime HTML content changes on the page according to the updated field.
                if (isset($request->access)) {
                    $html = $milestone->access_html;

                    if ($request->access != 'private') {
                        $milestone->allowedstaffs()->forceDelete();
                    }
                } elseif (isset($request->name)) {
                    $html = $milestone->name;
                } elseif (isset($request->start_date)) {
                    $html = $milestone->readableDate('start_date');
                } elseif (isset($request->end_date)) {
                    $html = $milestone->readableDate('end_date');
                } elseif (isset($request->project_id)) {
                    $project_html   = "<a href='" . route('admin.project.show', $milestone->project_id) . "'
                                          data-realtime='project_id'>" . $milestone->fresh()->project->name .
                                      "</a>";
                    $real_replace[] = ["[data-realtime='project_id']", $project_html];
                }

                $inner_html[]  = ['.follower-container-box', $milestone->fresh()->display_followers, false];
                $inner_html[]  = ['.show-misc-actions', $milestone->fresh()->show_misc_actions, false];
                $inner_html[]  = ["[data-realtime='duration']", $milestone->fresh()->duration_html, false];
                $inner_html[]  = ["[data-realtime='age']", $milestone->fresh()->age_html, false];
                $history       = $milestone->recent_history_html;
                $updated_by    = "<p class='compact'>" . $milestone->updatedByName() . "<br>
                                     <span class='color-shadow sm'>" . $milestone->updated_ampm . "</span>
                                  </p>";
                $last_modified = "<p data-toggle='tooltip' data-placement='bottom'
                                     title='" . $milestone->readableDateAmPm('modified_at') . "'>" .
                                     time_short_form($milestone->modified_at->diffForHumans()) .
                                 "</p>";

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'       => true,
                    'updatedBy'    => $updated_by,
                    'innerHtml'    => $inner_html,
                    'editFalse'    => $edit_false,
                    'lastModified' => $last_modified,
                    'realReplace'  => $real_replace,
                    'realtime'     => $realtime,
                    'history'      => $history,
                    'html'         => $html,
                ]);

                // Notify all users associated with this record.
                if (count($milestone->notifees)
                    && count($milestone->newUpdatedArray())
                    && $milestone->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $milestone->notifees, [auth()->user()->id]),
                        new CrudNotification('milestone_updated', $milestone->id, $milestone->newUpdatedArray())
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
     * @param \App\Models\Milestone    $milestone
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Milestone $milestone)
    {
        // Valid specified resource checker.
        if ($milestone->id != $request->id) {
            return response()->json(['status' => false]);
        } else {
            $redirect = null;

            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                $prev = Milestone::getAuthViewData()
                                 ->where('project_id', $milestone->project_id)
                                 ->where('id', '>', $request->id)
                                 ->get()
                                 ->first();

                $next = Milestone::getAuthViewData()
                                 ->where('project_id', $milestone->project_id)
                                 ->where('id', '<', $request->id)
                                 ->latest('id')
                                 ->get()
                                 ->first();

                if (isset($next)) {
                    $redirect = route('admin.milestone.show', $next->id);
                } elseif (isset($prev)) {
                    $redirect = route('admin.milestone.show', $prev->id);
                } else {
                    $redirect = $milestone->project->show_route . '/milestones';
                }
            }

            // Update milestone related tasks and issues "milestone field" value with null.
            $notifees    = $milestone->notifees;
            $calendar_id = $milestone->id;
            $milestone->tasks()->update(['milestone_id' => null]);
            $milestone->issues()->update(['release_milestone_id' => null, 'affected_milestone_id' => null]);
            $milestone->delete();

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'redirect' => $redirect, 'eventId' => (int) $request->id]);

            // Notify all users associated with this record.
            Notification::send(
                get_wherein('user', $notifees, [auth()->user()->id]),
                new CrudNotification('milestone_deleted', $request->id)
            );
        }
    }

    /**
     * JSON format sequencial listing data according to the related parent module of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $module_name
     * @param int                      $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function sequenceMilestoneData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::withTrashed()->find($module_id);

        // If the parent module exists then get the child resource data filter by the auth user view permission.
        if (isset($module)) {
            $data = $module->milestones()->authViewData()->filterMask()->orderBy('milestones.position')->get();

            return Milestone::getTabTableData($data, $request, true);
        }

        return null;
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
    public function connectedMilestoneData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::withTrashed()->find($module_id);

        // If the parent module exists then get the child resource data filter by the auth user view permission.
        if (isset($module)) {
            $milestones = $module->milestones()->authViewData()->filterMask()->latest('milestones.id')->get();

            return Milestone::getTabTableData($milestones, $request);
        }

        return null;
    }

    /**
     * Update the calendar position of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCalendarPosition(Request $request)
    {
        $status    = false;
        $errors    = null;
        $milestone = Milestone::find($request->id);

        // Check the specified resource and update the start, end date of this resource.
        if (isset($milestone)) {
            $status     = true;
            $start_date = str_replace('T', ' ', $request->start);
            $end_date   = str_replace('T', ' ', $request->end);
            $milestone->start_date = $start_date;
            $milestone->end_date   = $end_date;
            $milestone->save();
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
    }
}
