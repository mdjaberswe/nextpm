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
use App\Models\Issue;
use App\Models\Revision;
use App\Models\FilterView;
use App\Models\IssueStatus;
use App\Models\AllowedStaff;
use App\Jobs\SaveAllowedStaff;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminIssueController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:issue.view', ['only' => ['index', 'issueData', 'show']]);
        $this->middleware('admin:issue.create', ['only' => ['store']]);
        $this->middleware('admin:issue.edit', ['only' => ['edit', 'update', 'bulkUpdate']]);
        $this->middleware('admin:mass_update.issue', ['only' => ['bulkUpdate']]);
        $this->middleware('admin:issue.delete', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('admin:mass_delete.issue', ['only' => ['bulkDestroy']]);
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
            'title'              => 'Issues List',
            'item'               => 'Issue',
            'field'              => 'issues',
            'view'               => 'admin.issue',
            'route'              => 'admin.issue',
            'permission'         => 'issue',
            'bulk'               => 'update',
            'filter'             => true,
            'current_filter'     => FilterView::getCurrentFilter('issue'),
            'breadcrumb'         => FilterView::getBreadcrumb('issue'),
            'export'             => permit('export.issue'),
            'data_default'       => 'issue_owner:' . auth_staff()->id,
        ];

        $table = Issue::getTableFormat();

        return view('admin.issue.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function issueData(Request $request)
    {
        // filter by user view permission and current filter parameter.
        $issues = Issue::getAuthViewData()->filterViewData()->latest('id')->get();

        return Issue::getTableData($issues, $request);
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
        $kanban_count        = [];
        $kanban              = [];
        $parent_kanban_count = null;
        $validation          = Issue::validate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position               = Issue::getTargetPositionVal(-1);
            $issue                  = new Issue;
            $issue->issue_owner     = null_if_empty($request->issue_owner);
            $issue->name            = $request->name;
            $issue->access          = $request->access;
            $issue->issue_status_id = $request->issue_status_id;
            $issue->start_date      = null_if_empty($request->start_date);
            $issue->due_date        = null_if_empty($request->due_date);
            $issue->severity        = null_if_empty($request->severity);
            $issue->issue_type_id   = null_if_empty($request->issue_type_id);
            $issue->description     = null_if_empty($request->description);
            $issue->position        = $position;

            if (not_null_empty($request->related_type)) {
                $issue->linked_id   = $request->related_id;
                $issue->linked_type = $request->related_type;

                if ($request->related_type == 'project') {
                    $issue->release_milestone_id  = null_if_empty($request->release_milestone_id);
                    $issue->affected_milestone_id = null_if_empty($request->affected_milestone_id);
                }
            }

            $issue->secureDateAttributes()->save();

            // After store posted data, real-time changes on Kanban, Calendar, Gantt.
            $kanban_count = Issue::getKanbanStageCount();
            $kanban[$issue->kanban_stage_key][] = $issue->kanban_card_html;

            if (not_null_empty($issue->linked_type)) {
                $module = morph_to_model($issue->linked_type)::find($issue->linked_id);

                if (isset($module)) {
                    $parent_kanban_count[$issue->linked_type] = $module->getActivityKanbanStageCount('issue');
                }
            }

            if (not_null_empty($issue->release_milestone_id)) {
                $milestone = morph_to_model('milestone')::find($issue->release_milestone_id);

                if (isset($milestone)) {
                    $parent_kanban_count['milestone'] = $milestone->getActivityKanbanStageCount('issue');
                }
            }

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'            => true,
                'gantt'             => true,
                'saveId'            => $issue->id,
                'kanban'            => $kanban,
                'kanbanCount'       => $kanban_count,
                'parentKanbanCount' => $parent_kanban_count,
                'renderEvent'       => $issue->update_calendar,
            ]);

            // Save allowed staff with permitted action.
            if ($request->access == 'private') {
                dispatch(new SaveAllowedStaff(
                    $request->staffs,
                    'issue',
                    $issue->id,
                    $request->can_write,
                    $request->can_delete
                ));
            }

            // Notify all users associated with this record.
            if (count($issue->notifees)) {
                Notification::send(
                    get_wherein('user', $issue->notifees, [auth()->user()->id]),
                    new CrudNotification('issue_created', $issue->id)
                );
            }
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Issue        $issue
     * @param string|null              $infotype
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Issue $issue, $infotype = null)
    {
        // If the auth user has permission to view this record then show the page
        // and pass $page variable with title, breadcrumb, tabs information.
        if ($issue->auth_can_view) {
            $page = [
                'title'       => 'Issue: ' . $issue->name,
                'item_title'  => $issue->show_page_breadcrumb,
                'item'        => 'Issue',
                'view'        => 'admin.issue',
                'tabs'        => [
                    'list'    => Issue::informationTypes(),
                    'default' => Issue::defaultInfoType($infotype),
                    'item_id' => $issue->id,
                    'url'     => 'tab/issue',
                ],
            ];

            return view('admin.issue.show', compact('page', 'issue'));
        }

        return redirect()->route('admin.issue.index');
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Issue        $issue
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Issue $issue)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;
            $html   = null;

            // If the specified resource is valid and the auth user has permission to edit.
            if (isset($issue) && isset($request->id) && $issue->id == $request->id && $issue->auth_can_edit) {
                $info = $issue->toArray();
                $info['show']   = [];
                $info['freeze'] = [];

                // If the auth user doesn't have permission to change "owner" then freeze "owner" field.
                if (! $issue->auth_can_change_owner) {
                    $info['freeze'][] = 'issue_owner';
                }

                // If the specified resource is related to a non-permitted module
                // then fix the dropdown in the proper format.
                if (! is_null($info['related_type'])) {
                    $rel_field        = $info['related_type'] . '_id';
                    $info[$rel_field] = $info['related_id'];
                    $info['show'][]   = $rel_field;
                    $permitted_related_ids = morph_to_model($info['related_type'])::getAuthPermittedIds('issue');

                    if (! in_array($info['related_id'], $permitted_related_ids)) {
                        $info['selectlist'][$rel_field] = $issue->fixRelatedDropdown($info['related_type'], [], true);
                    }
                }

                // Modal title link useful for calendar or gantt view.
                $info['modal_title_link'] = [
                    'href'  => route('admin.issue.show', $issue->id),
                    'title' => str_limit($info['name'], 70, '.'),
                ];

                // Modal footer delete button useful for calendar or gantt view.
                if ($issue->auth_can_delete) {
                    $info['modal_footer_delete'] = [
                        'action' => route('admin.issue.destroy', $issue->id),
                        'id'     => $issue->id,
                        'item'   => 'issue',
                    ];
                }

                $info = (object) $info;

                // If the request for render form HTML and It is useful for the common modal.
                if (isset($request->html)) {
                    $html = view('admin.issue.partials.form', ['form' => 'edit'])->render();
                }
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
        }

        return redirect()->route('admin.issue.show', $issue->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Issue        $issue
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Issue $issue)
    {
        $kanban              = [];
        $kanban_count        = [];
        $parent_kanban_count = null;

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($issue) && isset($request->id) && $issue->id == $request->id && $issue->auth_can_edit) {
            $validation = Issue::validate($request->all(), $issue);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if ($issue->auth_can_change_owner) {
                    $issue->issue_owner = null_if_empty($request->issue_owner);
                }

                $old_status = $issue->issue_status_id;
                $new_status = (int) $request->issue_status_id;

                // Update the kanban card position if the status has changed.
                if ($old_status != $new_status) {
                    $position = Issue::getTargetPositionVal(-1);
                    $issue->position = $position;
                }

                $issue->name            = $request->name;
                $issue->access          = $request->access;
                $issue->issue_status_id = $request->issue_status_id;
                $issue->start_date      = null_if_empty($request->start_date);
                $issue->due_date        = null_if_empty($request->due_date);
                $issue->severity        = null_if_empty($request->severity);
                $issue->issue_type_id   = null_if_empty($request->issue_type_id);
                $issue->description     = null_if_empty($request->description);

                if (not_null_empty($request->related_type)) {
                    $issue->linked_id   = $request->related_id;
                    $issue->linked_type = $request->related_type;

                    if ($request->related_type == 'project') {
                        $issue->release_milestone_id  = null_if_empty($request->release_milestone_id);
                        $issue->affected_milestone_id = null_if_empty($request->affected_milestone_id);
                    }
                } else {
                    $issue->linked_id   = null;
                    $issue->linked_type = null;
                }

                $issue->update();

                // Delete all allowed users if request access is not private.
                if ($request->access != 'private') {
                    $issue->allowedstaffs()->forceDelete();
                }

                // Realtime changes on Kanban, Calendar, Gantt after updating data.
                $kanban_count = Issue::getKanbanStageCount();
                $kanban[$issue->kanban_stage_key][$issue->kanban_card_key] = $old_status != $new_status
                                                                             ? $issue->kanban_card_html
                                                                             : $issue->kanban_card;

                if (not_null_empty($issue->linked_type)) {
                    $module = morph_to_model($issue->linked_type)::find($issue->linked_id);

                    if (isset($module)) {
                        $parent_kanban_count[$issue->linked_type] = $module->getActivityKanbanStageCount('issue');
                    }
                }

                if (not_null_empty($issue->release_milestone_id)) {
                    $milestone = morph_to_model('milestone')::find($issue->release_milestone_id);

                    if (isset($milestone)) {
                        $parent_kanban_count['milestone'] = $milestone->getActivityKanbanStageCount('issue');
                    }
                }

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'            => true,
                    'gantt'             => true,
                    'kanban'            => $kanban,
                    'kanbanCount'       => $kanban_count,
                    'parentKanbanCount' => $parent_kanban_count,
                    'updateEvent'       => $issue->update_calendar,
                    'saveId'            => $request->id,
                ]);

                // Notify all users associated with this record.
                if (count($issue->notifees)
                    && count($issue->newUpdatedArray())
                    && $issue->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $issue->notifees, [auth()->user()->id]),
                        new CrudNotification('issue_updated', $issue->id, $issue->newUpdatedArray())
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
     * @param \App\Models\Issue        $issue
     *
     * @return \Illuminate\Http\Response
     */
    public function singleUpdate(Request $request, Issue $issue)
    {
        $html          = null;
        $history       = null;
        $updated_by    = null;
        $last_modified = null;
        $realtime      = [];
        $real_replace  = [];
        $inner_html    = [];
        $edit_false    = [];
        $data          = $request->all();

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($issue) && $issue->auth_can_edit) {
            $data['id'] = $issue->id;
            $data['change_owner'] = (isset($request->issue_owner) && $issue->auth_can_change_owner);

            if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
                $related_field     = $data['linked_type'] . '_id';
                $data['linked_id'] = $data[$related_field];
            }

            $validation = Issue::singleValidate($data, $issue);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                $prev_linked_id = $issue->linked_id;
                $update_data = replace_null_if_empty($request->all());
                $issue->update($update_data);

                // Realtime HTML content changes on the page according to the updated field.
                if (isset($request->access)) {
                    $html = $issue->access_html;

                    if ($request->access != 'private') {
                        $issue->allowedstaffs()->forceDelete();
                    }
                } elseif (isset($request->name)) {
                    $html = $issue->name;
                } elseif (isset($request->start_date)) {
                    $html = not_null_empty($issue->start_date) ? $issue->readableDate('start_date') : '';
                } elseif (isset($request->due_date)) {
                    $html = not_null_empty($issue->due_date) ? $issue->readableDate('due_date') : '';
                } elseif (isset($request->linked_type)) {
                    if (is_null($request->linked_type) || empty($request->linked_type)) {
                        $html = '';
                        $issue->update([
                            'linked_id' => null,
                            'linked_type' => null,
                            'release_milestone_id' => null,
                            'affected_milestone_id' => null,
                        ]);
                    } else {
                        if ($request->linked_type == 'project' && (int) $request->linked_id != $prev_linked_id) {
                            $issue->update(['release_milestone_id' => null, 'affected_milestone_id' => null]);
                        }

                        $issue->update(['linked_id' => $data['linked_id'], 'linked_type' => $data['linked_type']]);
                        $html = $issue->fresh()->linked->name_link_icon;
                    }

                    if (is_null($issue->release_milestone_id)) {
                        $release = "<div class='value' data-value='' data-realtime='release_milestone_id'></div>";
                        $real_replace[] = ["[data-realtime='release_milestone_id']", $release];
                    }

                    if (is_null($issue->affected_milestone_id)) {
                        $affect  = "<div class='value' data-value='' data-realtime='affected_milestone_id'></div>";
                        $real_replace[] = ["[data-realtime='affected_milestone_id']", $affect];
                    }
                } elseif (isset($request->reproducible) && not_null_empty($request->reproducible)) {
                    $html = $issue->reproducible_display;
                }

                $inner_html[]  = ['.follower-container-box', $issue->fresh()->display_followers, false];
                $inner_html[]  = ['.show-misc-actions', $issue->fresh()->show_misc_actions, false];
                $inner_html[]  = ["#days-remaining", $issue->fresh()->days_remaining_html, false];
                $inner_html[]  = ["[data-realtime='duration']", $issue->fresh()->duration_html, false];
                $inner_html[]  = ["[data-realtime='overdue']", $issue->fresh()->overdue_days_html, false];
                $history       = $issue->recent_history_html;
                $updated_by    = "<p class='compact'>" . $issue->updatedByName() . "<br>
                                    <span class='color-shadow sm'>" . $issue->updated_ampm . "</span>
                                  </p>";
                $last_modified = "<p data-toggle='tooltip' data-placement='bottom'
                                     title='" . $issue->readableDateAmPm('modified_at') . "'>" .
                                     time_short_form($issue->modified_at->diffForHumans()) .
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
                if (count($issue->notifees)
                    && count($issue->newUpdatedArray())
                    && $issue->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $issue->notifees, [auth()->user()->id]),
                        new CrudNotification('issue_updated', $issue->id, $issue->newUpdatedArray())
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
     * Update the status field of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Issue        $issue
     *
     * @return \Illuminate\Http\Response
     */
    public function closedOrReopen(Request $request, Issue $issue)
    {
        $status          = false;
        $mark_as_closed  = null;
        $activity_status = null;
        $checkbox        = null;
        $save_id         = null;

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($issue) && $issue->auth_can_edit) {
            $mark_as_closed  = ($issue->status->category == 'open');
            $default_status  = $mark_as_closed ? IssueStatus::getDefaultClosed() : IssueStatus::getDefaultOpen();
            $issue->update(['issue_status_id' => $default_status->id]);
            $status          = true;
            $checkbox        = $issue->fresh()->closed_open_checkbox;
            $activity_status = $issue->fresh()->activity_status_html;
            $save_id         = $issue->id;
        }

        return response()->json([
            'item'           => 'issue',
            'status'         => $status,
            'saveId'         => $save_id,
            'checkbox'       => $checkbox,
            'markAsClosed'   => $mark_as_closed,
            'activityStatus' => $activity_status,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Issue        $issue
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Issue $issue)
    {
        // Valid specified resource and the auth user has to delete permission checker.
        if ($issue->id != $request->id || ! $issue->auth_can_delete) {
            return response()->json(['status' => false]);
        } else {
            $kanban_count        = [];
            $kanban              = [];
            $parent_kanban_count = null;
            $redirect            = null;

            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                $prev = Issue::getAuthViewData()->where('id', '>', $issue->id)->get()->first();
                $next = Issue::getAuthViewData()->where('id', '<', $issue->id)->latest('id')->get()->first();

                if (isset($next)) {
                    $redirect = route('admin.issue.show', $next->id);
                } elseif (isset($prev)) {
                    $redirect = route('admin.issue.show', $prev->id);
                } else {
                    $redirect = route('admin.issue.index');
                }
            }

            // After delete make changes on Kanban, Calendar and notify related users.
            $notifees             = $issue->notifees;
            $module_id            = $issue->linked_id;
            $module_name          = $issue->linked_type;
            $kanban[]             = $issue->kanban_card_key;
            $release_milestone_id = $issue->release_milestone_id;
            $issue->delete();

            $kanban_count = Issue::getKanbanStageCount();

            if (not_null_empty($module_name)) {
                $module = morph_to_model($module_name)::find($module_id);

                if (isset($module)) {
                    $parent_kanban_count[$module_name] = $module->getActivityKanbanStageCount('issue');
                }
            }

            if (not_null_empty($release_milestone_id)) {
                $milestone = morph_to_model('milestone')::find($release_milestone_id);

                if (isset($milestone)) {
                    $parent_kanban_count['milestone'] = $milestone->getActivityKanbanStageCount('issue');
                }
            }

            event(new \App\Events\IssueDeleted([$request->id]));

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'            => true,
                'redirect'          => $redirect,
                'eventId'           => (int) $request->id,
                'parentKanbanCount' => $parent_kanban_count,
                'kanbanCount'       => $kanban_count,
                'kanban'            => $kanban,
            ]);

            // Notify all users associated with this record.
            if (count($notifees)) {
                Notification::send(
                    get_wherein('user', $notifees, [auth()->user()->id]),
                    new CrudNotification('issue_deleted', $request->id)
                );
            }
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
        $issues = $request->issues;

        // Count requested resource data checker and only the auth user permitted data will be deleted.
        if (isset($issues) && count($issues)) {
            $del_ids = Issue::whereIn('id', $issues)->get()->where('auth_can_delete', true)->pluck('id')->toArray();
            Issue::whereIn('id', $del_ids)->delete();
            event(new \App\Events\IssueDeleted($del_ids));
            // Ajax quickly responds and notify all related users.
            flush_response(['status' => true]);
            $notifees = array_flatten(Issue::withTrashed()->whereIn('id', $del_ids)->get()->pluck('notifees'));

            if (count($notifees)) {
                Notification::send(
                    get_wherein('user', $notifees, [auth()->user()->id]),
                    new CrudNotification('issue_mass_removed', 0, ['count' => count($del_ids)])
                );
            }
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
        $issues = $request->issues;

        // Count requested resource data and update related field checker.
        if (isset($issues) && count($issues) && isset($request->related)) {
            $validation = Issue::massValidate($request->all());

            // Update mass data if validation passes.
            if ($validation->passes()) {
                // Update only user permitted data.
                $issue_ids = Issue::whereIn('id', $issues)->get()->where('auth_can_edit', true);

                // If the requested field is "owner" then the auth user needs to have "change owner" permission.
                if ($request->related == 'issue_owner') {
                    $issue_ids = $issue_ids->where('auth_can_change_owner', true);
                }

                $issue_ids = $issue_ids->pluck('id')->toArray();
                $issues    = Issue::whereIn('id', $issue_ids);

                if (\Schema::hasColumn('issues', $request->related)) {
                    $field       = $request->related;
                    $value       = null_if_empty($request->$field);
                    $update_data = [$field => $value];

                    // Check for not inserting problematic data.
                    if ($request->related == 'linked_type') {
                        $linked_field = $request->linked_type . '_id';
                        $update_data['linked_id'] = $request->$linked_field;

                        if ($request->linked_type == 'project') {
                            Issue::whereIn('id', $issue_ids)
                                 ->where('linked_id', '!=', $request->$linked_field)
                                 ->update(['release_milestone_id' => null, 'affected_milestone_id' => null]);
                        }
                    } elseif ($request->related == 'start_date') {
                        $issues = $issues->where('due_date', '>=', $request->start_date)
                                         ->orWhere('due_date', null);
                    } elseif ($request->related == 'due_date') {
                        $issues = $issues->where('start_date', '<=', $request->due_date)
                                         ->orWhere('start_date', null);
                    }

                    // Get notifees, final issue ids and pre updated issues to keep histories
                    $notifees   = array_flatten($issues->get()->pluck('notifees'));
                    $issue_ids  = $issues->pluck('id')->toArray();
                    $old_issues = $issues->get();

                    // Mass update, ajax quick response, mass updated histories, notify related users.
                    Issue::whereIn('id', $issue_ids)->update($update_data);
                    flush_response(['status' => true]);
                    Revision::secureBulkUpdatedHistory('issue', $old_issues, $update_data);
                    Notification::send(
                        get_wherein('user', $notifees, [auth()->user()->id]),
                        new CrudNotification('issue_mass_changed', $issue_ids, [
                            'field'       => display_field($field),
                            'key'         => $field == 'linked_type' ? 'linked_id' : $field,
                            'new_value'   => $field == 'linked_type' ? $update_data['linked_id'] : $value,
                            'old_value'   => null,
                            'count'       => count($issue_ids),
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
     * Display a Kanban View of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function indexKanban(Request $request)
    {
        // Page information like title, user permission, current filter, breadcrumb,
        // and resource kanban data according to start to end order stages.
        $page = [
            'title'             => 'Issues Kanban',
            'item'              => 'Issue',
            'view'              => 'admin.issue',
            'route'             => 'admin.issue',
            'permission'        => 'issue',
            'modal_edit'        => false,
            'modal_bulk_update' => false,
            'modal_bulk_delete' => false,
            'filter'            => true,
            'current_filter'    => FilterView::getCurrentFilter('issue'),
            'item_title'        => FilterView::getBreadcrumb('issue'),
            'import'            => permit('import.issue'),
        ];

        $issues_kanban = Issue::getKanbanData();

        return view('admin.issue.kanban', compact('page', 'issues_kanban'));
    }

    /**
     * Load Kanban items of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\IssueStatus  $issue_status
     * @param string|null              $module_name
     * @param int|null                 $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function kanbanCard(Request $request, IssueStatus $issue_status, $module_name = null, $module_id = null)
    {
        $html          = '';
        $status        = true;
        $load_status   = true;
        $errors        = null;
        $data          = $request->all();
        $take_limit    = not_null_empty($request->takeLimit) ? (int) $request->takeLimit : 10;
        $from_start    = not_null_empty($request->fromStart) && $request->fromStart == true;
        $ids_condition = $from_start ? true : isset($request->ids);

        // If the requested kanban stage is valid and the load condition is true.
        if (isset($issue_status) && $issue_status->id == $request->stageId && $ids_condition) {
            $validation = Issue::kanbanCardValidate($data);

            // Kanban card validation checker.
            if ($validation->passes()) {
                $parent = null;

                if (not_null_empty($module_name) && not_null_empty($module_id)) {
                    $parent = morph_to_model($module_name)::find($module_id);
                }

                // Resource kanban card or parent children kanban card checker.
                $issues = is_null($parent)
                          ? Issue::getAuthViewData()->filterViewData()->filterMask()
                          : $parent->issues()->authViewData()->filterMask();

                // Initial load from start checker.
                if (! $from_start) {
                    $bottom_id    = (int) last($request->ids);
                    $bottom_issue = Issue::find($bottom_id);
                    $issues       = $issues->where('issues.position', '<', $bottom_issue->position);
                }

                $issues = $issues->where('issue_status_id', $issue_status->id)->latest('issues.position')->get();
                $load_status = ($issues->count() > $take_limit);

                foreach ($issues->take($take_limit) as $issue) {
                    $html .= $issue->kanban_card_html;
                }
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        }

        return response()->json([
            'status'     => $status,
            'errors'     => $errors,
            'loadStatus' => $load_status,
            'html'       => $html,
        ]);
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
            'title'               => 'Issues Calendar',
            'item'                => 'Issue',
            'view'                => 'admin.issue',
            'route'               => 'admin.issue',
            'permission'          => 'issue',
            'modal_title_link'    => true,
            'filter'              => true,
            'modal_edit'          => false,
            'modal_bulk_update'   => false,
            'modal_bulk_delete'   => false,
            'current_filter'      => FilterView::getCurrentFilter('issue'),
            'item_title'          => FilterView::getBreadcrumb('issue'),
            'import'              => permit('import.issue'),
            'modal_footer_delete' => permit('issue.delete'),
        ];

        return view('admin.issue.calendar', compact('page'));
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
        $issues = Issue::getAuthViewData()->filterViewData()->get();

        return response()->json($issues);
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
        $status = false;
        $errors = null;
        $issue  = Issue::find($request->id);

        // Check the specified resource and update the start, due date of this resource.
        if (isset($issue)) {
            $status     = true;
            $start_date = str_replace('T', ' ', $request->start);
            $due_date   = str_replace('T', ' ', $request->end);
            $issue->start_date = $start_date;

            if (! is_null($issue->due_date)) {
                $issue->due_date = $due_date;
            }

            $issue->update();
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
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
    public function connectedIssueData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::withTrashed()->find($module_id);

        // If parent module exists then get child resource data filter by user view permission
        if (isset($module)) {
            $issues = $module->issues()->authViewData()->filterMask()->latest('issues.id')->get();

            return Issue::getTabTableData($issues, $request);
        }

        return null;
    }
}
