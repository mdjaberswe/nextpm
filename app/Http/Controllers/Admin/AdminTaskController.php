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
use App\Models\Task;
use App\Models\Staff;
use App\Models\Revision;
use App\Models\TaskStatus;
use App\Models\FilterView;
use App\Models\AllowedStaff;
use App\Jobs\SaveAllowedStaff;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminTaskController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:task.view', ['only' => ['index', 'taskData', 'show']]);
        $this->middleware('admin:task.create', ['only' => ['store']]);
        $this->middleware('admin:task.edit', ['only' => ['edit', 'update', 'bulkUpdate']]);
        $this->middleware('admin:task.delete', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('admin:mass_update.task', ['only' => ['bulkUpdate']]);
        $this->middleware('admin:mass_delete.task', ['only' => ['bulkDestroy']]);
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
            'title'          => 'Tasks List',
            'item'           => 'Task',
            'field'          => 'tasks',
            'view'           => 'admin.task',
            'route'          => 'admin.task',
            'permission'     => 'task',
            'bulk'           => 'update',
            'filter'         => true,
            'current_filter' => FilterView::getCurrentFilter('task'),
            'breadcrumb'     => FilterView::getBreadcrumb('task'),
            'export'         => permit('export.task'),
            'data_default'   => 'task_owner:' . auth_staff()->id,
        ];

        $table = Task::getTableFormat();

        return view('admin.task.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function taskData(Request $request)
    {
        // Filter by user view permission and current filter parameter.
        $tasks = Task::getAuthViewData()->filterViewData()->latest('id')->get();

        return Task::getTableData($tasks, $request);
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
        $inner_html          = [];
        $kanban              = [];
        $kanban_count        = [];
        $parent_kanban_count = null;
        $validation          = Task::validate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $task_status = TaskStatus::find($request->task_status_id);
            $position    = Task::getTargetPositionVal(-1);

            // Create new task with lastest kanban card position.
            $task                        = new Task;
            $task->position              = $position;
            $task->name                  = $request->name;
            $task->access                = $request->access;
            $task->task_owner            = null_if_empty($request->task_owner);
            $task->description           = null_if_empty($request->description);
            $task->milestone_id          = null_if_empty($request->milestone_id);
            $task->start_date            = null_if_empty($request->start_date);
            $task->due_date              = null_if_empty($request->due_date);
            $task->priority              = null_if_empty($request->priority);
            $task->task_status_id        = $request->task_status_id;
            $task->completion_percentage = $task_status->category == 'open' ? $request->completion_percentage : 100;

            if (! empty($request->related_type)) {
                $task->linked_id   = $request->related_id;
                $task->linked_type = $request->related_type;
            }

            $task->secureDateAttributes()->save();

            // After store posted data, real-time changes on Kanban, Calendar, Gantt.
            $inner_html[] = ['#user-next-action-' . $task->task_owner, non_property_checker($task->owner, 'next_task_html')];
            $kanban_count = Task::getKanbanStageCount();
            $kanban[$task->kanban_stage_key][] = $task->kanban_card_html;

            if (not_null_empty($task->linked_type)) {
                $module = morph_to_model($task->linked_type)::find($task->linked_id);

                if (isset($module)) {
                    $parent_kanban_count[$task->linked_type] = $module->getActivityKanbanStageCount('task');
                }
            }

            if (not_null_empty($task->milestone_id)) {
                $milestone = morph_to_model('milestone')::find($task->milestone_id);

                if (isset($milestone)) {
                    $parent_kanban_count['milestone'] = $milestone->getActivityKanbanStageCount('task');
                }
            }

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'            => true,
                'gantt'             => true,
                'saveId'            => $task->id,
                'kanban'            => $kanban,
                'kanbanCount'       => $kanban_count,
                'parentKanbanCount' => $parent_kanban_count,
                'renderEvent'       => $task->update_calendar,
                'innerHtml'         => $inner_html,
            ]);

            // Save allowed staff with permitted action.
            if ($request->access == 'private') {
                dispatch(new SaveAllowedStaff(
                    $request->staffs,
                    'task',
                    $task->id,
                    $request->can_write,
                    $request->can_delete
                ));
            }

            // Notify all users associated with this record.
            if (count($task->notifees)) {
                Notification::send(
                    get_wherein('user', $task->notifees, [auth()->user()->id]),
                    new CrudNotification('task_created', $task->id)
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
     * @param \App\Models\Task         $task
     * @param string|null              $infotype
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Task $task, $infotype = null)
    {
        // If the auth user has permission to view this record then show the page
        // and pass $page variable with title, breadcrumb, tabs information.
        if ($task->auth_can_view) {
            $page = [
                'title'       => 'Task: ' . $task->name,
                'item_title'  => $task->show_page_breadcrumb,
                'item'        => 'Task',
                'view'        => 'admin.task',
                'tabs'        => [
                    'list'    => Task::informationTypes(),
                    'default' => Task::defaultInfoType($infotype),
                    'item_id' => $task->id,
                    'url'     => 'tab/task',
                ],
            ];

            return view('admin.task.show', compact('page', 'task'));
        }

        return redirect()->route('admin.task.index');
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Task         $task
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Task $task)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;
            $html   = null;

            // If the specified resource is valid and the auth user has permission to edit.
            if (isset($task) && isset($request->id) && $task->id == $request->id && $task->auth_can_edit) {
                $info = $task->toArray();
                $info['show']   = [];
                $info['freeze'] = [];

                // If the auth user doesn't have permission to change "owner" then freeze "owner" field.
                if (! $task->auth_can_change_owner) {
                    $info['freeze'][] = 'task_owner';
                }

                // If the specified resource is related to a non-permitted module
                // then fix the dropdown in the proper format.
                if (! is_null($info['related_type'])) {
                    $rel_field        = $info['related_type'] . '_id';
                    $info[$rel_field] = $info['related_id'];
                    $info['show'][]   = $rel_field;
                    $permitted_related_ids = morph_to_model($info['related_type'])::getAuthPermittedIds('task');

                    if (! in_array($info['related_id'], $permitted_related_ids)) {
                        $info['selectlist'][$rel_field] = $task->fixRelatedDropdown($info['related_type'], [], true);
                    }
                }

                // Modal title link useful for calendar or gantt view.
                $info['modal_title_link'] = [
                    'href'  => route('admin.task.show', $task->id),
                    'title' => str_limit($info['name'], 70, '.'),
                ];

                // Modal footer delete button useful for calendar or gantt view.
                if ($task->auth_can_delete) {
                    $info['modal_footer_delete'] = [
                        'action' => route('admin.task.destroy', $task->id),
                        'id'     => $task->id,
                        'item'   => 'task',
                    ];
                }

                $info = (object) $info;

                // If the request for render form HTML and It is useful for the common modal.
                if (isset($request->html)) {
                    $html = view('admin.task.partials.form', ['form' => 'edit'])->render();
                }
            } else {
                $status = false;
            }

            return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
        }

        return redirect()->route('admin.task.show', $task->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Task         $task
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $kanban              = [];
        $kanban_count        = [];
        $parent_kanban_count = null;

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($task) && isset($request->id) && $task->id == $request->id && $task->auth_can_edit) {
            $validation = Task::validate($request->all(), $task);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if ($task->auth_can_change_owner) {
                    $task->task_owner = null_if_empty($request->task_owner);
                }

                $old_status  = $task->task_status_id;
                $new_status  = (int) $request->task_status_id;
                $task_status = TaskStatus::find($request->task_status_id);

                // Update the kanban card position if the status has changed.
                if ($old_status != $new_status) {
                    $position = Task::getTargetPositionVal(-1);
                    $task->position = $position;
                }

                $task->name                  = $request->name;
                $task->access                = $request->access;
                $task->start_date            = null_if_empty($request->start_date);
                $task->due_date              = null_if_empty($request->due_date);
                $task->priority              = null_if_empty($request->priority);
                $task->description           = null_if_empty($request->description);
                $task->milestone_id          = null_if_empty($request->milestone_id);
                $task->task_status_id        = $request->task_status_id;
                $task->completion_percentage = $task_status->category == 'open' ? $request->completion_percentage : 100;

                if (not_null_empty($request->related_type)) {
                    $task->linked_id   = $request->related_id;
                    $task->linked_type = $request->related_type;
                } else {
                    $task->linked_id   = null;
                    $task->linked_type = null;
                }

                $task->update();

                // Delete all allowed users if request access is not private.
                if ($request->access != 'private') {
                    $task->allowedstaffs()->forceDelete();
                }

                // Realtime changes on Kanban, Calendar, Gantt after updating data.
                $kanban_count = Task::getKanbanStageCount();
                $kanban[$task->kanban_stage_key][$task->kanban_card_key] = $old_status != $new_status
                                                                           ? $task->kanban_card_html
                                                                           : $task->kanban_card;

                if (not_null_empty($task->linked_type)) {
                    $module = morph_to_model($task->linked_type)::find($task->linked_id);

                    if (isset($module)) {
                        $parent_kanban_count[$task->linked_type] = $module->getActivityKanbanStageCount('task');
                    }
                }

                if (not_null_empty($task->milestone_id)) {
                    $milestone = morph_to_model('milestone')::find($task->milestone_id);

                    if (isset($milestone)) {
                        $parent_kanban_count['milestone'] = $milestone->getActivityKanbanStageCount('task');
                    }
                }

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'            => true,
                    'gantt'             => true,
                    'kanban'            => $kanban,
                    'kanbanCount'       => $kanban_count,
                    'parentKanbanCount' => $parent_kanban_count,
                    'updateEvent'       => $task->update_calendar,
                    'saveId'            => $request->id,
                ]);

                // Notify all users associated with this record.
                if (count($task->notifees)
                    && count($task->newUpdatedArray())
                    && $task->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $task->notifees, [auth()->user()->id]),
                        new CrudNotification('task_updated', $task->id, $task->newUpdatedArray())
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
     * @param \App\Models\Task         $task
     *
     * @return \Illuminate\Http\Response
     */
    public function singleUpdate(Request $request, Task $task)
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
        if (isset($task) && $task->auth_can_edit) {
            $data['id'] = $task->id;
            $data['change_owner'] = isset($request->task_owner) && $task->auth_can_change_owner;

            if (array_key_exists('linked_type', $data) && ! empty($data['linked_type'])) {
                $related_field     = $data['linked_type'] . '_id';
                $data['linked_id'] = $data[$related_field];
            }

            $validation = Task::singleValidate($data, $task);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if (isset($request->task_status_id)) {
                    $task_status = TaskStatus::find($request->task_status_id);

                    if ($task->task_status_id != $request->task_status_id) {
                        $task->update(['completion_percentage' => $task_status->completion_percentage]);
                    }

                    if ($task_status->category == 'closed') {
                        $edit_false[] = 'completion_percentage';
                    }
                }

                $prev_linked_id = $task->linked_id;
                $update_data = replace_null_if_empty($request->all());
                $task->update($update_data);

                // Realtime HTML content changes on the page according to the updated field.
                if (isset($request->access)) {
                    $html = $task->access_html;

                    if ($request->access != 'private') {
                        $task->allowedstaffs()->forceDelete();
                    }
                } elseif (isset($request->name)) {
                    $html = $task->name;
                } elseif (isset($request->start_date)) {
                    $html = not_null_empty($task->start_date) ? $task->readableDate('start_date') : '';
                } elseif (isset($request->due_date)) {
                    $html = not_null_empty($task->due_date) ? $task->readableDate('due_date') : '';
                } elseif (isset($request->task_status_id) || isset($request->completion_percentage)) {
                    if ($task->status->category == 'closed' && $task->completion_percentage != 100) {
                        $task->update(['completion_percentage' => 100]);
                    }

                    $completion_html = "<div class='value percent' data-value='" . $task->completion_percentage . "'
                                            data-realtime='completion_percentage'>" . $task->completion_percentage .
                                        "</div>";
                    $real_replace[]  = ["[data-realtime='completion_percentage']", $completion_html];
                    $inner_html[]    = ["#completion-percentage", $task->classified_completion, false];
                } elseif (isset($request->linked_type)) {
                    if (is_null($request->linked_type) || empty($request->linked_type)) {
                        $task->update(['linked_id' => null, 'linked_type' => null, 'milestone_id' => null]);
                        $html = '';
                    } else {
                        if ($request->linked_type == 'project' && (int) $request->linked_id != $prev_linked_id) {
                            $task->update(['milestone_id' => null]);
                        }

                        $task->update(['linked_id' => $data['linked_id'], 'linked_type' => $data['linked_type']]);
                        $html = $task->fresh()->linked->name_link_icon;
                    }

                    if (is_null($task->milestone_id)) {
                        $milestone_html = "<div class='value' data-value='' data-realtime='milestone_id'></div>";
                        $real_replace[] = ["[data-realtime='milestone_id']", $milestone_html];
                    }
                }

                $inner_html[]  = ['.follower-container-box', $task->fresh()->display_followers, false];
                $inner_html[]  = ['.show-misc-actions', $task->fresh()->show_misc_actions, false];
                $inner_html[]  = ["[data-realtime='duration']", $task->fresh()->duration_html, false];
                $inner_html[]  = ["[data-realtime='overdue']", $task->fresh()->overdue_days_html, false];
                $history       = $task->recent_history_html;
                $updated_by    = "<p class='compact'>" . $task->updatedByName() . "<br>
                                    <span class='color-shadow sm'>" . $task->updated_ampm . '</span>
                                  </p>';
                $last_modified = "<p data-toggle='tooltip' data-placement='bottom'
                                    title='" . $task->readableDateAmPm('modified_at') . "'>" .
                                    time_short_form($task->modified_at->diffForHumans()) .
                                 '</p>';

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
                if (count($task->notifees)
                    && count($task->newUpdatedArray())
                    && $task->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $task->notifees, [auth()->user()->id]),
                        new CrudNotification('task_updated', $task->id, $task->newUpdatedArray())
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
     * @param \App\Models\Task         $task
     *
     * @return \Illuminate\Http\Response
     */
    public function closedOrReopen(Request $request, Task $task)
    {
        $status          = false;
        $save_id         = null;
        $checkbox        = null;
        $completion      = null;
        $mark_as_closed  = null;
        $activity_status = null;

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($task) && $task->auth_can_edit) {
            $mark_as_closed = ($task->status->category == 'open');
            $default_status = $mark_as_closed ? TaskStatus::getDefaultClosed() : TaskStatus::getDefaultOpen();

            $task->update([
                'task_status_id' => $default_status->id,
                'completion_percentage' => $default_status->completion_percentage,
            ]);

            $status          = true;
            $checkbox        = $task->fresh()->closed_open_checkbox;
            $activity_status = $task->fresh()->activity_status_html;
            $completion      = $task->completion_html;
            $save_id         = $task->id;
        }

        return response()->json([
            'item'           => 'task',
            'status'         => $status,
            'saveId'         => $save_id,
            'checkbox'       => $checkbox,
            'completion'     => $completion,
            'markAsClosed'   => $mark_as_closed,
            'activityStatus' => $activity_status,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Task         $task
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Task $task)
    {
        // Valid specified resource and the auth user has to delete permission checker.
        if ($task->id != $request->id || ! $task->auth_can_delete) {
            return response()->json(['status' => false]);
        } else {
            $kanban              = [];
            $kanban_count        = [];
            $parent_kanban_count = null;
            $redirect            = null;

            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                $prev = Task::getAuthViewData()->where('id', '>', $task->id)->get()->first();
                $next = Task::getAuthViewData()->where('id', '<', $task->id)->latest('id')->get()->first();

                if (isset($next)) {
                    $redirect = route('admin.task.show', $next->id);
                } elseif (isset($prev)) {
                    $redirect = route('admin.task.show', $prev->id);
                } else {
                    $redirect = route('admin.task.index');
                }
            }

            // After delete make changes on Kanban, Calendar and notify related users.
            $notifees         = $task->notifees;
            $module_id        = $task->linked_id;
            $module_name      = $task->linked_type;
            $milestone_id     = $task->milestone_id;
            $kanban[]         = $task->kanban_card_key;
            $task->delete();

            $kanban_count = Task::getKanbanStageCount();

            if (not_null_empty($module_name)) {
                $module = morph_to_model($module_name)::find($module_id);

                if (isset($module)) {
                    $parent_kanban_count[$module_name] = $module->getActivityKanbanStageCount('task');
                }
            }

            if (not_null_empty($milestone_id)) {
                $milestone = morph_to_model('milestone')::find($milestone_id);

                if (isset($milestone)) {
                    $parent_kanban_count['milestone'] = $milestone->getActivityKanbanStageCount('task');
                }
            }

            event(new \App\Events\TaskDeleted([$request->id]));

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
                    new CrudNotification('task_deleted', $request->id)
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
        $tasks  = $request->tasks;

        // Count requested resource data checker.
        if (isset($tasks) && count($tasks)) {
            // Only user permitted data will be deleted.
            $task_ids = Task::whereIn('id', $tasks)->get()->where('auth_can_delete', true)->pluck('id')->toArray();
            Task::whereIn('id', $task_ids)->delete();
            event(new \App\Events\TaskDeleted($task_ids));

            // Ajax quick response and notify all realted users.
            flush_response(['status' => true]);
            $notifees = array_flatten(Task::withTrashed()->whereIn('id', $task_ids)->get()->pluck('notifees'));

            if (count($notifees)) {
                Notification::send(
                    get_wherein('user', $notifees, [auth()->user()->id]),
                    new CrudNotification('task_mass_removed', 0, ['count' => count($task_ids)])
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
        $tasks  = $request->tasks;

        // Count requested resource data and update related field checker.
        if (isset($tasks) && count($tasks) && isset($request->related)) {
            $validation = Task::massValidate($request->all());

            // Update mass data if validation passes.
            if ($validation->passes()) {
                // Update only user permitted data.
                $task_ids = Task::whereIn('id', $tasks)->get()->where('auth_can_edit', true);

                // If the requested field is "owner" then the auth user needs to have "change owner" permission.
                if ($request->related == 'task_owner') {
                    $task_ids = $task_ids->where('auth_can_change_owner', true);
                }

                $task_ids = $task_ids->pluck('id')->toArray();
                $tasks    = Task::whereIn('id', $task_ids);

                if (\Schema::hasColumn('tasks', $request->related)) {
                    $field       = $request->related;
                    $value       = null_if_empty($request->$field);
                    $update_data = [$field => $value];

                    // Check for not inserting problematic data.
                    if ($request->related == 'linked_type') {
                        $linked_field             = $request->linked_type . '_id';
                        $update_data['linked_id'] = $request->$linked_field;
                    } elseif ($request->related == 'start_date') {
                        $tasks = $tasks->where('due_date', '>=', $request->start_date)->orWhere('due_date', null);
                    } elseif ($request->related == 'due_date') {
                        $tasks = $tasks->where('start_date', '<=', $request->due_date)->orWhere('start_date', null);
                    } elseif ($request->related == 'task_status_id') {
                        $task_status = TaskStatus::find($request->task_status_id);

                        if ($task_status->category == 'closed') {
                            $update_data['completion_percentage'] = 100;
                        } else {
                            Task::whereIn('id', $task_ids)
                                ->where('task_status_id', '!=', $request->task_status_id)
                                ->update(['completion_percentage' => $task_status->completion_percentage]);
                        }
                    } elseif ($request->related == 'completion_percentage'
                        && $request->completion_percentage < 100
                    ) {
                        $tasks = $tasks->whereIn('task_status_id', TaskStatus::getCategoryIds('open'));
                    }

                    // Get notifees, final task ids and pre updated tasks to keep histories
                    $notifees  = array_flatten($tasks->get()->pluck('notifees'));
                    $task_ids  = $tasks->pluck('id')->toArray();
                    $old_tasks = $tasks->get();

                    // Mass update, ajax quick response, mass updated histories, notify related users.
                    Task::whereIn('id', $task_ids)->update($update_data);
                    flush_response(['status' => true]);
                    Revision::secureBulkUpdatedHistory('task', $old_tasks, $update_data);
                    Notification::send(
                        get_wherein('user', $notifees, [auth()->user()->id]),
                        new CrudNotification('task_mass_changed', $task_ids, [
                            'field'       => display_field($field),
                            'key'         => $field == 'linked_type' ? 'linked_id' : $field,
                            'new_value'   => $field == 'linked_type' ? $update_data['linked_id'] : $value,
                            'old_value'   => null,
                            'count'       => count($task_ids),
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
            'title'             => 'Tasks Kanban',
            'item'              => 'Task',
            'view'              => 'admin.task',
            'route'             => 'admin.task',
            'permission'        => 'task',
            'modal_edit'        => false,
            'modal_bulk_update' => false,
            'modal_bulk_delete' => false,
            'filter'            => true,
            'current_filter'    => FilterView::getCurrentFilter('task'),
            'item_title'        => FilterView::getBreadcrumb('task'),
            'import'            => permit('import.task'),
        ];

        $tasks_kanban = Task::getKanbanData();

        return view('admin.task.kanban', compact('page', 'tasks_kanban'));
    }

    /**
     * Load Kanban items of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TaskStatus   $task_status
     * @param string|null              $module_name
     * @param int|null                 $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function kanbanCard(Request $request, TaskStatus $task_status, $module_name = null, $module_id = null)
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
        if (isset($task_status) && $task_status->id == $request->stageId && $ids_condition) {
            $validation = Task::kanbanCardValidate($data);

            // Kanban card validation checker.
            if ($validation->passes()) {
                $parent = null;

                if (not_null_empty($module_name) && not_null_empty($module_id)) {
                    $parent = morph_to_model($module_name)::find($module_id);
                }

                // Resource kanban card or parent children kanban card checker.
                $tasks = is_null($parent)
                         ? Task::getAuthViewData()->filterViewData()->filterMask()
                         : $parent->tasks()->authViewData()->filterMask();

                // Initial load from start checker.
                if (! $from_start) {
                    $bottom_id   = (int) last($request->ids);
                    $bottom_task = Task::find($bottom_id);
                    $tasks       = $tasks->where('tasks.position', '<', $bottom_task->position);
                }

                $tasks       = $tasks->where('task_status_id', $task_status->id)->latest('tasks.position')->get();
                $load_status = ($tasks->count() > $take_limit);

                foreach ($tasks->take($take_limit) as $task) {
                    $html .= $task->kanban_card_html;
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
            'title'               => 'Tasks Calendar',
            'item'                => 'Task',
            'view'                => 'admin.task',
            'route'               => 'admin.task',
            'permission'          => 'task',
            'modal_edit'          => false,
            'modal_bulk_update'   => false,
            'modal_bulk_delete'   => false,
            'modal_title_link'    => true,
            'filter'              => true,
            'current_filter'      => FilterView::getCurrentFilter('task'),
            'item_title'          => FilterView::getBreadcrumb('task'),
            'import'              => permit('import.task'),
            'modal_footer_delete' => permit('task.delete'),
        ];

        return view('admin.task.calendar', compact('page'));
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
        $tasks = Task::getAuthViewData()->filterViewData()->get();

        return response()->json($tasks);
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
        $task   = Task::find($request->id);

        // Check the specified resource and update the start, due date of this resource.
        if (isset($task)) {
            $status = true;
            $task->start_date = str_replace('T', ' ', $request->start);

            if (! is_null($task->due_date)) {
                $task->due_date = str_replace('T', ' ', $request->end);
            }

            $task->save();
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
    public function connectedTaskData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::withTrashed()->find($module_id);

        // If the parent module exists then get the child resource data filter by the auth user view permission.
        if (isset($module)) {
            $tasks = $module->tasks()->authViewData()->filterMask()->latest('tasks.id')->get();

            return Task::getTabTableData($tasks, $request);
        }

        return null;
    }
}
