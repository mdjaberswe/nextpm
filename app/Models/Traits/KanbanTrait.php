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

namespace App\Models\Traits;

trait KanbanTrait
{
    /**
     * Kanban stage load validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function kanbanValidate($data)
    {
        $picked_exists = '';
        $identifier = self::getIdentifier();

        // If the previous kanban item exists and not equal to zero then check the previous picked item valid or not.
        // Note: The kanban item will be placed at the top if the picked item id is zero.
        if (array_key_exists('picked', $data) && $data['picked'] != 0) {
            $picked_exists = 'exists:' . $identifier . 's,id,deleted_at,NULL';
        }

        return validator($data, [
            'source'    => 'required|in:' . $identifier,
            'id'        => 'required|exists:' . $identifier . 's,id,deleted_at,NULL',
            'picked'    => 'required|different:id|' . $picked_exists,
            'field'     => 'required|in:' . $identifier . '_status_id',
            'stage'     => 'required|exists:' . $identifier . '_status,id,deleted_at,NULL',
            'ordertype' => 'required|in:desc',
        ]);
    }

    /**
     * Kanban card validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function kanbanCardValidate($data)
    {
        $identifier = self::getIdentifier();
        $required = not_null_empty($data['fromStart']) ? '' : 'required';
        $rules = [
            'stageId' => 'required|exists:' . $identifier . '_status,id,deleted_at,NULL',
            'ids' => $required . '|array|exists:' . $identifier . 's,id,deleted_at,NULL',
        ];

        return validator($data, $rules);
    }

    /**
     * Get kanban data.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $parent
     *
     * @return array
     */
    public static function getKanbanData($parent = null)
    {
        $outcome    = [];
        $identifier = self::getIdentifier();
        $items      = $identifier . 's';
        $all_status = morph_to_model($identifier . '_status')::getSmartOrder();

        // Get an array formatted kanban data order by kanban stage.
        foreach ($all_status as $status) {
            $key = $identifier . 'status-' . $status->id;
            $data = is_null($parent) ? self::getAuthViewData()->filterViewData() : $parent->$items()->authViewData();
            $outcome[$key]['data'] = $data->filterMask()
                                          ->where($identifier . '_status_id', $status->id)
                                          ->latest($items . '.position')
                                          ->get();

            $outcome[$key]['quick_data'] = $outcome[$key]['data']->take(5);
            $outcome[$key]['status'] = $status->toArray();
            $outcome[$key]['status']['load_url'] = route('admin.' . $identifier . '.kanban.card', $status->id);
            $outcome[$key]['status']['load_status'] = $outcome[$key]['data']->count() > 5 ? 'true' : 'false';
        }

        return $outcome;
    }

    /**
     * Get kanban stage count.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $parent
     *
     * @return array
     */
    public static function getKanbanStageCount($parent = null)
    {
        $outcome    = [];
        $identifier = self::getIdentifier();
        $items      = $identifier . 's';
        $all_status = morph_to_model($identifier . '_status')::getSmartOrder();

        // Get array formatted kanban data count order by kanban stage.
        foreach ($all_status as $status) {
            $key = $identifier . 'status-' . $status->id;
            $data = is_null($parent) ? self::getAuthViewData()->filterViewData() : $parent->$items()->authViewData();
            $outcome[$key] = $data->filterMask()->where($identifier . '_status_id', $status->id)->get()->count();
        }

        return $outcome;
    }

    /**
     * Get the specified resource kanban stage key.
     *
     * @return string
     */
    public function getKanbanStageKeyAttribute()
    {
        $stage_id  = self::getIdentifier() . '_status_id';

        return self::getIdentifier() . 'status-' . $this->$stage_id;
    }

    /**
     * Get the specified resource kanban card key.
     *
     * @return string
     */
    public function getKanbanCardKeyAttribute()
    {
        return self::getIdentifier() . '-' . $this->id;
    }

    /**
     * Get the specified resource kanban card HTML.
     *
     * @return string
     */
    public function getKanbanCardAttribute()
    {
        $action      = '';
        $card_btn    = '';
        $action_html = '';
        $stage_id    = $this->identifier . '_status_id';

        // If the auth user has permission to delete the specified resource then render delete action HTML.
        if ($this->auth_can_delete) {
            $action .= "<div class='funnel-btn'>" .
                            \Form::open(['route' => ['admin.' . $this->identifier . '.destroy', $this->id], 'method' => 'delete']) .
                                \Form::hidden('id', $this->id) .
                                "<button type='submit' class='delete' data-item='{$this->identifier}'>
                                    <i class='mdi mdi-delete'></i>
                                </button>" .
                            \Form::close() .
                       "</div>";
        }

        if (! is_null($this->kanban_dropdown_action)) {
            $action .= $this->kanban_dropdown_action;
        }

        // If the auth user has permission to edit the specified resource then render edit action HTML.
        if ($this->auth_can_edit) {
            $action .= "<div class='funnel-btn'>
                            <a class='common-edit-btn' data-item='{$this->identifier}' editid='{$this->id}'
                               data-url='" . route('admin.' . $this->identifier . '.edit', $this->id) . "'
                               data-posturl='" . route('admin.' . $this->identifier . '.update', $this->id) . "'>
                               <i class='fa fa-pencil'></i>
                            </a>
                        </div>";
        }

        if (! empty($action)) {
            $card_btn    = "<a class='funnel-bottom-btn'><i class='fa fa-ellipsis-v md'></i></a>";
            $action_html = "<div class='full funnel-btn-group'>{$action}</div>";
        }

        // Render complete kanban card HTML.
        $card = "<div class='funnel-card' data-init-stage='{$this->$stage_id}'>
                    <div class='funnel-top-btn'>" .
                        \Form::hidden('positions[]', $this->id, ['data-stage' => $this->$stage_id]) .
                        $card_btn . "
                    </div>" .
                    $this->kanban_card_top_row .
                    $this->kanban_card_bottom_row .
                    $action_html .
               '</div>';

        return $card;
    }

    /**
     * Get kanban card HTML of the specified resource.
     *
     * @return string
     */
    public function getKanbanCardHtmlAttribute()
    {
        // If the auth user hasn't permission to edit the kanban item, then disable to edit the item.
        $disable_css = ! $this->auth_can_edit ? 'disable' : '';

        return "<li id='" . $this->identifier . '-' . $this->id . "' class='" . $disable_css . "'>" .
                    $this->kanban_card .
               '</li>';
    }

    /**
     * Get the specified resource kanban card top row HTML.
     *
     * @return string
     */
    public function getKanbanCardTopRowAttribute()
    {
        $info = null;

        if ($this->identifier == 'task') {
            $info = "<span data-toggle='tooltip' data-placement='bottom' title='Priority'>" . ucfirst($this->priority) . '</span>';
        } elseif ($this->identifier == 'issue') {
            $info = "<span data-toggle='tooltip' data-placement='bottom' title='Severity'>" . ucfirst($this->severity) . '</span>';
        } elseif ($this->identifier == 'project') {
            $info = "<span data-stage-field='true' data-toggle='tooltip' data-placement='bottom' title='Status'>" .
                        str_limit($this->status->name, 17, '.') .
                    '</span>';
        }

        return "<div class='full'>
                    <a href='" . route('admin.' . $this->identifier . '.show', $this->id) . "' class='title-link'>" .
                        str_limit($this->name, 30) .
                   "</a>
                </div>

                <div class='full'>
                    <div class='funnel-card-info'>
                        <i class='mdi mdi-trophy-award warning'></i>
                        <span data-toggle='tooltip' data-placement='bottom' title='" .
                            fill_up_space($this->identifier_call_name . ' Owner') . "'>" .
                            str_limit(non_property_checker($this->owner, 'name'), 17, '.') .
                       "</span>
                    </div>
                    <div class='funnel-card-info'><i class='fa fa-circle'></i> " .
                        $info .
                   '</div>
                </div>';
    }

    /**
     * Get the specified resource kanban card bottom row HTML.
     *
     * @return string
     */
    public function getKanbanCardBottomRowAttribute()
    {
        if ($this->identifier == 'task' || $this->identifier == 'issue') {
            return "<div class='full'>
                        <div class='funnel-card-info'>
                            <i class='{$this->related_icon_or_dot}'></i>
                            <span data-toggle='tooltip' data-placement='bottom' title='" .
                                fill_up_space('Related To ' . ucfirst($this->linked_type)) . "'>" .
                                str_limit(non_property_checker($this->linked, 'name'), 15, '.') .
                           "</span>
                        </div>

                        <div class='funnel-card-info'>
                            <i class='fa fa-circle'></i>
                            <span data-toggle='tooltip' data-placement='bottom' title='" . fill_up_space('Due Date') . "'>" .
                                $this->readableDate('due_date') .
                           '</span>
                        </div>
                    </div>';
        } elseif ($this->identifier == 'project') {
            return "<div class='full'>
                        <div class='funnel-card-info'>
                            <i class='fa fa-circle'></i>
                            " . $this->init_date_html . "
                        </div>

                        <div class='funnel-card-info'>
                            <i class='fa fa-circle'></i>
                            <span data-toggle='tooltip' data-placement='bottom' title='" . fill_up_space('End Date') . "'>" .
                                $this->readableDate('end_date') .
                           '</span>
                        </div>
                    </div>';
        }

        return null;
    }

    /**
     * Get the related module icon.
     *
     * @return string
     */
    public function getRelatedIconOrDotAttribute()
    {
        if (! is_null($this->linked_type)) {
            return module_icon($this->linked_type);
        }

        return 'fa fa-circle';
    }

    /**
     * Get the specified resource kanban dropdown actions.
     *
     * @return string
     */
    public function getKanbanDropdownActionAttribute()
    {
        if ($this->identifier == 'project') {
            if ($this->authCanDo('task.create')
                || $this->authCanDo('issue.create')
                || $this->authCanDo('event.create')
            ) {
                $add_task  = '';
                $add_issue = '';
                $add_event = '';

                // If the auth user has permission to create the project's task.
                if ($this->authCanDo('task.create')) {
                    $add_task = "<span>
                                    <a class='add-multiple' data-item='task' data-content='task.partials.form'
                                       data-action='" . route('admin.task.store') . "' data-show='project_id'
                                       data-default='related_type:project|related_id:{$this->id}' save-new='false'>
                                       <i class='fa fa-check-square'></i> Add Task
                                    </a>
                                </span>";
                }

                // If the auth user has permission to create the project's issue.
                if ($this->authCanDo('issue.create')) {
                    $add_issue = "<span>
                                    <a class='add-multiple' data-item='issue' data-content='issue.partials.form'
                                       data-action='" . route('admin.issue.store') . "' data-show='project_id'
                                       data-default='related_type:project|related_id:{$this->id}' save-new='false'>
                                       <i class='fa fa-bug'></i> Add Issue
                                    </a>
                                  </span>";
                }

                // If the auth user has permission to create the project's event.
                if ($this->authCanDo('event.create')) {
                    $add_event = "<span>
                                    <a class='add-multiple' data-item='event' data-content='event.partials.form'
                                       data-action='" . route('admin.event.store') . "' data-show='project_id'
                                       data-default='related_type:project|related_id:{$this->id}' save-new='false'>
                                       <i class='fa fa-calendar'></i> Add Event
                                    </a>
                                  </span>";
                }

                return "<div class='funnel-btn dropdown clean'>
                            <a class='dropdown-toggle' animation='fadeIn|fadeOut' data-toggle='dropdown'
                               aria-expanded='false'><i class='mdi mdi-plus-circle-multiple-outline'></i>
                            </a>
                            <div class='dropdown-menu up-caret'>" . $add_task . $add_issue . $add_event . '</div>
                        </div>';
            }
        }

        return null;
    }
}
