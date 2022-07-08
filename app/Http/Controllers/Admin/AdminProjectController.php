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
use App\Models\User;
use App\Models\Staff;
use App\Models\Project;
use App\Models\Revision;
use App\Models\FilterView;
use App\Models\AllowedStaff;
use App\Models\ProjectStatus;
use App\Jobs\SaveAllowedStaff;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminProjectController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:project.view', ['only' => ['index', 'projectData', 'show']]);
        $this->middleware('admin:project.create', ['only' => ['store']]);
        $this->middleware('admin:project.edit', ['only' => ['edit', 'update', 'bulkUpdate']]);
        $this->middleware('admin:mass_update.project', ['only' => ['bulkUpdate']]);
        $this->middleware('admin:project.delete', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('admin:mass_delete.project', ['only' => ['bulkDestroy']]);
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
            'title'          => 'Projects List',
            'item'           => 'Project',
            'field'          => 'projects',
            'view'           => 'admin.project',
            'route'          => 'admin.project',
            'permission'     => 'project',
            'modal_size'     => 'medium',
            'bulk'           => 'update',
            'filter'         => true,
            'current_filter' => FilterView::getCurrentFilter('project'),
            'breadcrumb'     => FilterView::getBreadcrumb('project'),
            'export'         => permit('export.project'),
            'data_default'   => 'project_owner:' . auth_staff()->id,
        ];

        $table = Project::getTableFormat();

        return view('admin.project.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function projectData(Request $request)
    {
        // Filter by user view permission and current filter parameter.
        $projects = Project::getAuthViewData()->filterViewData()->filterMask()->latest('projects.id')->get();

        return Project::getTableData($projects, $request);
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
        $kanban       = [];
        $kanban_count = [];
        $validation   = Project::validate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $position                   = Project::getTargetPositionVal(-1);
            $project                    = new Project;
            $project->position          = $position;
            $project->name              = $request->name;
            $project->access            = $request->access;
            $project->description       = $request->description;
            $project->project_owner     = $request->project_owner;
            $project->project_status_id = $request->project_status_id;
            $project->start_date        = null_if_empty($request->start_date);
            $project->end_date          = null_if_empty($request->end_date);
            $project->save();

            // Add the owner as of the project’s member and real-time changes on kanban.
            $project->members()->attach($request->project_owner, Project::getAllPermissions());
            $kanban_count = Project::getKanbanStageCount();
            $kanban[$project->kanban_stage_key][] = $project->kanban_card_html;

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'      => true,
                'kanban'      => $kanban,
                'kanbanCount' => $kanban_count,
                'saveId'      => $project->id,
            ]);

            // Save allowed staff with permitted action.
            if ($request->access == 'private') {
                dispatch(new SaveAllowedStaff(
                    $request->staffs,
                    'project',
                    $project->id,
                    $request->can_write,
                    $request->can_delete
                ));
            }

            // Notify all users associated with this record.
            Notification::send(
                get_wherein('user', $project->notifees, [auth()->user()->id]),
                new CrudNotification('project_created', $project->id)
            );
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     * @param string|null              $infotype
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Project $project, $infotype = null)
    {
        // If the auth user has permission to view this record then show the page
        // and pass $page variable with title, breadcrumb, tabs information.
        if ($project->auth_can_view) {
            $page = [
                'title'       => 'Project: ' . $project->name,
                'item_title'  => $project->show_page_breadcrumb,
                'item'        => 'Project',
                'view'        => 'admin.project',
                'tabs'        => [
                    'list'    => Project::informationTypes($project),
                    'default' => Project::defaultInfoType($infotype, $project),
                    'item_id' => $project->id,
                    'url'     => 'tab/project',
                ],
            ];

            return view('admin.project.show', compact('page', 'project'));
        }

        return redirect()->route('admin.project.index');
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Project $project)
    {
        if ($request->ajax()) {
            $status = true;
            $info   = null;
            $html   = null;

            // If the specified resource is valid and the auth user has permission to edit.
            if (isset($project) && isset($request->id) && $project->id == $request->id && $project->auth_can_edit) {
                $info = $project->toArray();
                $info['freeze'] = [];

                // If the auth user doesn't have permission to change "owner" then freeze "owner" field.
                if (! $project->auth_can_change_owner) {
                    $info['freeze'][] = 'project_owner';
                }

                $info = (object) $info;

                // If the request for render form HTML and It is useful for the common modal.
                if (isset($request->html)) {
                    $html = view('admin.project.partials.form', ['form' => 'edit'])->render();
                }
            } else {
                $status = false;
            }

            return response()->json([
                'status' => $status,
                'info'   => $info,
                'html'   => $html,
            ]);
        }

        return redirect()->route('admin.project.show', $project->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $kanban       = [];
        $kanban_count = [];

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($project) && isset($request->id) && $project->id == $request->id && $project->auth_can_edit) {
            $validation = Project::validate($request->all());

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if ($project->auth_can_change_owner) {
                    if ($project->project_owner != (int) $request->project_owner) {
                        $project->members()->detach((int) $request->project_owner);
                        $project->members()->attach((int) $request->project_owner, Project::getAllPermissions());
                    }

                    $project->project_owner = null_if_empty($request->project_owner);
                }

                $old_status = $project->project_status_id;
                $new_status = (int) $request->project_status_id;

                // Update the kanban card position if the status has changed.
                if ($old_status != $new_status) {
                    $position = Project::getTargetPositionVal(-1);
                    $project->position = $position;
                }

                $project->name              = $request->name;
                $project->access            = $request->access;
                $project->description       = $request->description;
                $project->project_status_id = $request->project_status_id;
                $project->start_date        = null_if_empty($request->start_date);
                $project->end_date          = null_if_empty($request->end_date);
                $project->update();

                // Delete all allowed users if request access is not private.
                if ($request->access != 'private') {
                    $project->allowedstaffs()->forceDelete();
                }

                // Realtime changes on Kanban after updating data.
                $kanban_count = Project::getKanbanStageCount();
                $kanban[$project->kanban_stage_key][$project->kanban_card_key] = $old_status != $new_status
                                                                                 ? $project->kanban_card_html
                                                                                 : $project->kanban_card;

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'      => true,
                    'kanban'      => $kanban,
                    'kanbanCount' => $kanban_count,
                    'saveId'      => $request->id,
                ]);

                // Notify all users associated with this record.
                if (count($project->notifees)
                    && count($project->newUpdatedArray())
                    && $project->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $project->notifees, [auth()->user()->id]),
                        new CrudNotification('project_updated', $project->id, $project->newUpdatedArray())
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
     * @param \App\Models\Project      $project
     *
     * @return \Illuminate\Http\Response
     */
    public function singleUpdate(Request $request, Project $project)
    {
        $html          = null;
        $history       = null;
        $updated_by    = null;
        $last_modified = null;
        $inner_html    = [];
        $tab_table     = '#project-member';
        $data          = $request->all();

        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($project) && $project->auth_can_edit) {
            $data['id'] = $project->id;
            $data['change_owner'] = (isset($request->project_owner) && $project->auth_can_change_owner);
            $validation = Project::singleValidate($data, $project);

            // Update posted data if validation passes.
            if ($validation->passes()) {
                if (isset($request->project_status_id)) {
                    $old_status = $project->project_status_id;
                    $new_status = (int) $request->project_status_id;

                    if ($old_status != $new_status) {
                        $position = Project::getTargetPositionVal(-1);
                        $project->position = $position;
                    }
                }

                if (isset($request->project_owner) && $project->project_owner != (int) $request->project_owner) {
                    $project->members()->detach((int) $request->project_owner);
                    $project->members()->attach((int) $request->project_owner, Project::getAllPermissions());
                }

                $update_data = replace_null_if_empty($request->all());
                $project->update($update_data);

                // Realtime HTML content changes on the page according to the updated field.
                if (isset($request->access)) {
                    $html = $project->access_html;

                    if ($request->access != 'private') {
                        $project->allowedstaffs()->forceDelete();
                    }
                } elseif (isset($request->name)) {
                    $html = $project->name;
                } elseif (isset($request->start_date)) {
                    $html = not_null_empty($project->start_date) ? $project->readableDate('start_date') : '';
                } elseif (isset($request->end_date)) {
                    $html = not_null_empty($project->end_date) ? $project->readableDate('end_date') : '';
                }

                $inner_html[]  = ['.follower-container-box', $project->fresh()->display_followers, false];
                $inner_html[]  = ['.show-misc-actions', $project->fresh()->show_misc_actions, false];
                $inner_html[]  = ["[data-realtime='duration']", $project->fresh()->duration_html, false];
                $inner_html[]  = ["[data-realtime='age']", $project->fresh()->age_html, false];
                $history       = $project->recent_history_html;
                $updated_by    = "<p class='compact'>" . $project->updatedByName() . "<br>
                                     <span class='color-shadow sm'>" . $project->updated_ampm . "</span>
                                  </p>";
                $last_modified = "<p data-toggle='tooltip' data-placement='bottom'
                                     title='" . $project->readableDateAmPm('modified_at') . "'>" .
                                     time_short_form($project->modified_at->diffForHumans()) .
                                 "</p>";

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status'       => true,
                    'tabTable'     => $tab_table,
                    'innerHtml'    => $inner_html,
                    'updatedBy'    => $updated_by,
                    'lastModified' => $last_modified,
                    'history'      => $history,
                    'html'         => $html,
                ]);

                // Notify all users associated with this record.
                if (count($project->notifees)
                    && count($project->newUpdatedArray())
                    && $project->newUpdatedArray()[0]['key'] !== 'created_at'
                ) {
                    Notification::send(
                        get_wherein('user', $project->notifees, [auth()->user()->id]),
                        new CrudNotification('project_updated', $project->id, $project->newUpdatedArray())
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
     * @param \App\Models\Project      $project
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Project $project)
    {
        // Valid specified resource and the auth user has to delete permission checker.
        if ($project->id != $request->id || ! $project->auth_can_delete) {
            return response()->json(['status' => false]);
        } else {
            $kanban       = [];
            $kanban_count = [];
            $redirect     = null;

            // Redirect to the proper page if requested to redirect.
            if ($request->redirect) {
                $prev = Project::getAuthViewData()->where('id', '>', $project->id)->get()->first();
                $next = Project::getAuthViewData()->where('id', '<', $project->id)->latest('id')->get()->first();

                if (isset($next)) {
                    $redirect = route('admin.project.show', $next->id);
                } elseif (isset($prev)) {
                    $redirect = route('admin.project.show', $prev->id);
                } else {
                    $redirect = route('admin.project.index');
                }
            }

            // After delete make changes on Kanban, Calendar and notify related users.
            $notifees    = $project->notifees;
            $kanban[]    = $project->kanban_card_key;
            $project->delete();

            $kanban_count = Project::getKanbanStageCount();

            event(new \App\Events\ProjectDeleted([$request->id]));

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'      => true,
                'kanban'      => $kanban,
                'kanbanCount' => $kanban_count,
                'eventId'     => (int) $request->id,
                'redirect'    => $redirect,
            ]);

            // Notify all users associated with this record.
            Notification::send(
                get_wherein('user', $notifees, [auth()->user()->id]),
                new CrudNotification('project_deleted', $request->id)
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
        $projects = $request->projects;

        // Count requested resource data checker and only the auth user permitted data will be deleted.
        if (isset($projects) && count($projects) > 0) {
            $ids = Project::whereIn('id', $projects)->get()->where('auth_can_delete', true)->pluck('id')->toArray();
            Project::whereIn('id', $ids)->delete();
            event(new \App\Events\ProjectDeleted($ids));
            // Ajax quickly responds and notify all related users.
            flush_response(['status' => true]);
            $notifees = array_flatten(Project::withTrashed()->whereIn('id', $ids)->get()->pluck('notifees'));
            Notification::send(
                get_wherein('user', $notifees, [auth()->user()->id]),
                new CrudNotification('project_mass_removed', 0, ['count' => count($ids)])
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
        $projects = $request->projects;

        // Count requested resource data and update related field checker.
        if (isset($projects) && count($projects) && isset($request->related)) {
            $validation = Project::massValidate($request->all());

            // Update mass data if validation passes.
            if ($validation->passes()) {
                // Update only user permitted data.
                $project_ids = Project::whereIn('id', $projects)->get()->where('auth_can_edit', true);

                // If the requested field is "owner" then the auth user needs to have "change owner" permission.
                if ($request->related == 'project_owner') {
                    $project_ids = $project_ids->where('auth_can_change_owner', true);
                }

                $project_ids = $project_ids->pluck('id')->toArray();
                $projects    = Project::whereIn('id', $project_ids);

                if (\Schema::hasColumn('projects', $request->related) && count($project_ids)) {
                    $field       = $request->related;
                    $value       = null_if_empty($request->$field);
                    $update_data = [$field => $value];

                    // Check for not inserting problematic data.
                    if ($request->related == 'start_date') {
                        $projects = $projects->where('end_date', '>=', $request->start_date)
                                             ->orWhere('end_date', null);
                    } elseif ($request->related == 'end_date') {
                        $projects = $projects->where('end_date', '<=', $request->end_date)
                                             ->orWhere('end_date', null);
                    }

                    // Get notifees, final project ids, and pre updated projects to keep histories
                    $notifees     = array_flatten($projects->get()->pluck('notifees'));
                    $project_ids  = $projects->pluck('id')->toArray();
                    $old_projects = $projects->get();

                    // Mass update projects
                    Project::whereIn('id', $project_ids)->update($update_data);

                    // If Owner field then delete prev owner and add new Owner with all permissions
                    if ($request->related == 'project_owner') {
                        \DB::table('project_member')
                           ->whereIn('project_id', $project_ids)
                           ->where('staff_id', (int) $request->project_owner)
                           ->delete();

                        $insert_data = [];
                        $attach_data = Project::getAllPermissions() + [
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];

                        foreach ($project_ids as $project_id) {
                            $insert_data[] = [
                                'project_id' => $project_id,
                                'staff_id'   => (int) $request->project_owner,
                            ] + $attach_data;
                        }

                        \DB::table('project_member')->insert($insert_data);
                    }

                    // Ajax quick response, mass updated histories, notify related users.
                    flush_response(['status' => true]);
                    Revision::secureBulkUpdatedHistory('project', $old_projects, $update_data);
                    Notification::send(
                        get_wherein('user', $notifees, [auth()->user()->id]),
                        new CrudNotification('project_mass_changed', $project_ids, [
                            'key'         => $field,
                            'field'       => display_field($field),
                            'old_value'   => null,
                            'new_value'   => $value,
                            'count'       => count($project_ids),
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
            'title'             => 'Projects Kanban',
            'item'              => 'Project',
            'view'              => 'admin.project',
            'route'             => 'admin.project',
            'permission'        => 'project',
            'modal_size'        => 'medium',
            'modal_edit'        => false,
            'modal_bulk_update' => false,
            'modal_bulk_delete' => false,
            'filter'            => true,
            'current_filter'    => FilterView::getCurrentFilter('project'),
            'item_title'        => FilterView::getBreadcrumb('project'),
            'import'            => permit('import.project'),
        ];

        $projects_kanban = Project::getKanbanData();

        return view('admin.project.kanban', compact('page', 'projects_kanban'));
    }

    /**
     * Load Kanban items of the resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\ProjectStatus $project_status
     * @param string|null               $module_name
     * @param int|null                  $module_id
     *
     * @return \Illuminate\Http\Response
     */
    public function kanbanCard(Request $request, ProjectStatus $project_status, $module_name = null, $module_id = null)
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
        if (isset($project_status) && $project_status->id == $request->stageId && $ids_condition) {
            $validation = Project::kanbanCardValidate($data);

            // Kanban card validation checker
            if ($validation->passes()) {
                $parent = null;

                if (not_null_empty($module_name) && not_null_empty($module_id)) {
                    $parent = morph_to_model($module_name)::find($module_id);
                }

                // Resource kanban card or parent children kanban card checker.
                $projects = is_null($parent)
                            ? Project::getAuthViewData()->filterViewData()->filterMask()
                            : $parent->projects()->authViewData()->filterMask();

                // Initial load from start checker.
                if (! $from_start) {
                    $bottom_id      = (int) last($request->ids);
                    $bottom_project = Project::find($bottom_id);
                    $projects       = $projects->where('projects.position', '<', $bottom_project->position);
                }

                $projects    = $projects->where('project_status_id', $project_status->id)
                                        ->latest('projects.position')
                                        ->get();
                $load_status = ($projects->count() > $take_limit);

                foreach ($projects->take($take_limit) as $project) {
                    $html .= $project->kanban_card_html;
                }
            } else {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }
        }

        return response()->json([
            'html'       => $html,
            'status'     => $status,
            'errors'     => $errors,
            'loadStatus' => $load_status,
        ]);
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
    public function connectedProjectData(Request $request, $module_name, $module_id)
    {
        $module = morph_to_model($module_name)::find($module_id);

        // If parent module exists then get child resource data filter by user view permission
        if (isset($module)) {
            $projects = $module->projects()->authViewData()->filterMask()->latest('projects.id')->get();

            return Project::getTableData($projects, $request, true);
        }

        return collect();
    }

    /**
     * JSON format resource data for Gantt View of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     * @param string|null              $filter
     *
     * @return \Illuminate\Http\Response
     */
    public function ganttData(Request $request, Project $project, $filter = null)
    {
        if (isset($project)) {
            if (not_null_empty($filter) && array_key_exists($filter, Project::getGanttFilterList())) {
                session(['gantt_filter' => $filter]);
            }

            return response()->json($project->getGanttData());
        }

        return [];
    }

    /**
     * JSON format member data of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     * @param bool|null                $view_only
     *
     * @return \Illuminate\Http\Response
     */
    public function memberData(Request $request, Project $project, $view_only = null)
    {
        if (isset($project)) {
            return $project->getMemberData($request, $view_only);
        }

        return [];
    }

    /**
     * Store a newly created member in the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     *
     * @return \Illuminate\Http\Response
     */
    public function memberStore(Request $request, Project $project)
    {
        $validation = Project::memberValidate($request->all());

        // If validation passes and the auth user has permission to create 'member' then save posted data.
        if ($validation->passes() && $project->authCanDo('member_create', 'local')) {
            $permissions        = Project::getPermissionKeys();
            $fixed_permissions  = Project::getFixedPermissionsList();
            $all_permissions    = array_fill_keys($permissions, 1);
            $attach_permissions = [];

            foreach ($permissions as $permission) {
                $attach_permissions[$permission] = $request->has($permission) || in_array($permission, $fixed_permissions)
                                                   ? 1 : 0;
            }

            $old_members = $project->members->pluck('id')->toArray();

            // Loop through posted members and add them with permission.
            foreach ($request->members as $member) {
                $member_permissions = $attach_permissions;
                $new_member = $project->members()->wherePivot('staff_id', (int) $member)->get();

                if (! is_null($new_member->first())
                    && ($new_member->first()->admin
                    || (int) $member == $project->created_by
                    || (int) $member == $project->project_owner)
                ) {
                    $member_permissions = $all_permissions;
                }

                if ($new_member->count()) {
                    $sync_update = ['updated_at' => date('Y-m-d H:i:s')] + $member_permissions;

                    \DB::table('project_member')
                       ->where('project_id', $project->id)
                       ->where('staff_id', (int) $member)
                       ->update($sync_update);
                } else {
                    $project->members()->attach($member, $member_permissions);
                }
            }

            $inner_html = [
                ['.follower-container-box', $project->fresh()->display_followers],
                ['.show-misc-actions', $project->fresh()->show_misc_actions],
            ];

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'innerHtml' => $inner_html, 'tabTable' => '#project-member']);

            // Notify all users associated with this record.
            $new_notifees = User::pluckTypeId('staff', $request->members, $old_members);

            if (count($new_notifees)) {
                Notification::send(
                    get_wherein('user', $new_notifees, [auth()->user()->id]),
                    new CrudNotification('project_member_added', $project->id, ['new_member' => true])
                );
            }

            $new_members_count = $project->fresh()->members->count() - count($old_members);

            if (count($old_members) && $new_members_count > 0) {
                $old_notifees = User::pluckTypeId('staff', $old_members);

                Notification::send(
                    get_wherein('user', $old_notifees, [auth()->user()->id]),
                    new CrudNotification('project_member_added', $project->id, ['new_member' => $new_members_count])
                );
            }
        } else {
            $errors = $validation->getMessageBag()->toArray();

            if (! $project->authCanDo('member_create', 'local')) {
                $br = isset($errors['members']) ? '<br>' : null;
                $errors['members'][] = $br . 'You don\'t have permission to add member.';
            }

            return response()->json(['status' => false, 'errors' => $errors]);
        }
    }

    /**
     * Show the form to edit a member of the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function memberEdit(Request $request, Project $project, Staff $staff)
    {
        $status    = true;
        $info      = null;
        $html      = null;
        $tab_table = '#project-member';
        $member    = \DB::table('project_member')->find($request->id);

        // If the member is valid then follow the next execution.
        if (isset($member) && $member->staff_id == $staff->id && $member->project_id == $project->id) {
            $member_permissions = array_forget_keys((array) $member, [
                'id', 'project_id', 'staff_id', 'created_at', 'updated_at'
            ]);

            // Project Owner, Creator, Admin users will get all permissions.
            if ($project->isElite($staff) && $member_permissions != Project::getAllPermissions()) {
                $sync_update = ['updated_at' => date('Y-m-d H:i:s')] + Project::getAllPermissions();
                \DB::table('project_member')
                   ->where('project_id', $project->id)
                   ->where('staff_id', $staff->id)
                   ->update($sync_update);

                $member = \DB::table('project_member')
                             ->where('project_id', $project->id)
                             ->where('staff_id', $staff->id)
                             ->first();
            }

            // Member Form permission toggle enabled|disabled according to member permission.
            $info               = (array) $member;
            $info['project']    = 1;
            $info['member']     = $member->member_view == 1 ? 1 : 0;
            $info['milestone']  = $member->milestone_view == 1 ? 1 : 0;
            $info['task']       = $member->task_view == 1 ? 1 : 0;
            $info['issue']      = $member->issue_view == 1 ? 1 : 0;
            $info['event']      = $member->event_view == 1 ? 1 : 0;
            $info['note']       = $member->note_view == 1 ? 1 : 0;
            $info['attachment'] = $member->attachment_view == 1 ? 1 : 0;

            // If the request for render form HTML and It is useful for the common modal.
            if (isset($request->html)) {
                $html = view('admin.project.partials.member-form', [
                    'form'     => 'edit',
                    'disabled' => ! $project->authCanEditMember($staff),
                ])->render();
            }
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'info' => $info, 'html' => $html]);
    }

    /**
     * Update a member of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function memberUpdate(Request $request, Project $project, Staff $staff)
    {
        $status    = true;
        $tab_table = '#project-member';
        $member    = \DB::table('project_member')->find($request->id);

        // If the member is valid and the auth user can edit the member then follow the next execution.
        if (isset($member)
            && $member->staff_id == $staff->id
            && $member->project_id == $project->id
            && $project->authCanEditMember($staff)
        ) {
            $permissions        = Project::getPermissionKeys();
            $all_permissions    = array_fill_keys($permissions, 1);
            $fixed_permissions  = Project::getFixedPermissionsList();
            $attach_permissions = [];

            foreach ($permissions as $permission) {
                $attach_permissions[$permission] = $request->has($permission) || in_array($permission, $fixed_permissions)
                                                   ? 1 : 0;
            }

            $member_permissions = $project->isElite($staff) ? $all_permissions : $attach_permissions;
            $sync_update = ['updated_at' => date('Y-m-d H:i:s')] + $member_permissions;

            // Add member with permissions
            \DB::table('project_member')
               ->where('project_id', $project->id)
               ->where('staff_id', $staff->id)
               ->update($sync_update);
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'tabTable' => $tab_table]);
    }

    /**
     * Remove a member of the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project      $project
     * @param \App\Models\Staff        $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function memberDelete(Request $request, Project $project, Staff $staff)
    {
        $status        = false;
        $history       = null;
        $updated_by    = null;
        $last_modified = null;
        $inner_html    = [];
        $real_replace  = [];
        $tab_table     = '#project-member';
        $member        = \DB::table('project_member')->find($request->id);

        // If the member is valid and the auth user can delete the member then follow the next execution.
        if (isset($member)
            && $member->staff_id == $staff->id
            && $member->project_id == $project->id
            && $project->authCanDeleteMember($staff)
        ) {
            // If the deleted member was Owner then the auth user will be the next Owner.
            if ($staff->id == $project->project_owner) {
                $project->update(['project_owner' => auth_staff()->id]);
                $project->members()->detach(auth_staff()->id);
                $project->members()->attach(auth_staff()->id, Project::getAllPermissions());
                $owner_html     = "<div class='value' data-value='" . $project->project_owner . "'
                                        data-realtime='project_owner'>" . $project->owner->name .
                                  "</div>";
                $real_replace[] = ["[data-realtime='project_owner']", $owner_html];
                $history        = $project->recent_history_html;
                $updated_by     = "<p class='compact'>" . $project->updatedByName() . "<br>
                                      <span class='color-shadow sm'>" . $project->updated_ampm . "</span>
                                   </p>";
                $last_modified  = "<p data-toggle='tooltip' data-placement='bottom'
                                      title='" . $project->readableDateAmPm('modified_at') . "'>" .
                                      time_short_form($project->modified_at->diffForHumans()) .
                                  "</p>";
            }

            // Deleted member milestones owner will be project owner and
            // tasks, issues, events owner will be null
            $project->milestones()->where('milestone_owner', $staff->id)->update([
                'milestone_owner' => $project->project_owner
            ]);
            $project->tasks()->where('task_owner', $staff->id)->update(['task_owner' => null]);
            $project->issues()->where('issue_owner', $staff->id)->update(['issue_owner' => null]);
            $project->events()->where('event_owner', $staff->id)->update(['event_owner' => null]);
            $project->members()->detach($staff->id);
            $status = true;

            // Notify the project owner.
            if ($project->owner_id != auth_staff()->id) {
                $project->owner->user->notify(new CrudNotification('project_member_removed', $project->id));
            }
        }

        $inner_html[] = ['.follower-container-box', $project->fresh()->display_followers];
        $inner_html[] = ['.show-misc-actions', $project->fresh()->show_misc_actions];

        return response()->json([
            'status'       => $status,
            'tabTable'     => $tab_table,
            'updatedBy'    => $updated_by,
            'innerHtml'    => $inner_html,
            'lastModified' => $last_modified,
            'realReplace'  => $real_replace,
            'history'      => $history,
        ]);
    }
}
