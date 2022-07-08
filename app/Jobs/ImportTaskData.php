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

namespace App\Jobs;

use DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Import;
use App\Models\Revision;
use App\Models\Milestone;
use App\Models\TaskStatus;
use App\Jobs\Job;

class ImportTaskData extends Job
{
    protected $import;
    protected $map;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Import $import
     * @param array              $data
     *
     * @return void
     */
    public function __construct(Import $import, $data)
    {
        $this->import = $import;
        $this->map    = $data['map'];
        $this->data   = $data['import_data'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $import                  = $this->import;
        $map                     = $this->map;
        $import_data             = $this->data;
        $created_data            = [];
        $updated_data            = [];
        $skipped_data            = [];
        $created_data['heading'] = ['TASK NAME', 'DUE DATE', 'STATUS', 'PRIORITY', 'RELATED TO', 'TASK OWNER', 'WARNINGS'];
        $updated_data['heading'] = ['TASK NAME', 'DUE DATE', 'STATUS', 'PRIORITY', 'RELATED TO', 'TASK OWNER', 'WARNINGS'];
        $skipped_data['heading'] = ['TASK NAME', 'DUE DATE', 'STATUS', 'PRIORITY', 'RELATED TO', 'TASK OWNER', 'ERRORS/WARNINGS'];
        unset($map['import']);
        $exchange_key            = array_filter($map);
        $remove_key              = array_diff_assoc($map, $exchange_key);
        $remove_key              = count($remove_key) ? array_keys($remove_key) : false;

        // Check and collect import data one by one through this loop.
        foreach ($import_data as $row) {
            $row_original = $row;

            if ($remove_key) {
                array_forget($row, $remove_key);
            }

            $row_format            = rename_array_key($row, $exchange_key);
            $create                = false;
            $update                = false;
            $overwrite             = false;
            $skip                  = false;
            $warning               = [];
            $completion_percentage = 0;
            $owner                 = null;
            $priority              = null;
            $start_date            = null;
            $due_date              = null;
            $linked_type           = null;
            $linked_id             = null;
            $permitted_linked_ids  = null;
            $milestone             = null;
            $access                = 'public';
            $task_status           = TaskStatus::orderBy('position')->get()->first();

            // If the row has "task status", then take row "task status"
            // else by default "task status" will be first position task status.
            if (array_key_exists('task_status_id', $row_format)) {
                $task_status_exists = TaskStatus::where('name', $row_format['task_status_id'])->get();
                $task_status        = $task_status_exists->count() ? $task_status_exists->first() : $task_status;
            }

            // If the row has "owner", then take row "owner" else by default "owner" will be null.
            if (array_key_exists('task_owner', $row_format)) {
                $owner_exists = User::where('email', $row_format['task_owner'])->onlyStaff()->get();
                $owner        = $owner_exists->count() ? $owner_exists->first()->linked->id : $owner;
            }

            // If the row has "priority", then take row "priority" else by default "priority" will be null.
            if (array_key_exists('priority', $row_format)) {
                $data_priority = strtolower($row_format['priority']);

                if (in_array($data_priority, Task::prioritylist())) {
                    $priority = $data_priority;
                }
            }

            // If the row has "completion percentage", then take row "completion percentage"
            // else by default "completion percentage" will be 0.
            if (array_key_exists('completion_percentage', $row_format)) {
                $completion_percentage = is_numeric($row_format['completion_percentage'])
                                         ? $row_format['completion_percentage'] : $completion_percentage;
                $completion_percentage = $task_status->category == 'closed' ? 100 : $completion_percentage;
            }

            // If the row has "start date", then take row "start date" else by default "start date" will be today.
            if (array_key_exists('start_date', $row_format)) {
                $start_timestamp = strtotime($row_format['start_date']);

                if ($start_timestamp) {
                    $start_date = date('Y-m-d', $start_timestamp);
                }
            }

            // If the row has "due date", then take row "due date" else by default "due date" will be today.
            if (array_key_exists('due_date', $row_format)) {
                $due_timestamp = strtotime($row_format['due_date']);

                if ($due_timestamp) {
                    $due_date = date('Y-m-d', $due_timestamp);
                }

                if (isset($start_timestamp) && $start_timestamp > $due_timestamp) {
                    $start_date = $due_date;
                }
            }

            if (is_null($start_date)) {
                $start_date = is_null($due_date) ? date('Y-m-d') : $due_date;
            }

            if (is_null($due_date)) {
                $due_date = is_null($start_date) ? date('Y-m-d') : $start_date;
            }

            // If the row has "access", then take row "access" else by default "access" will be public.
            if (array_key_exists('access', $row_format)) {
                $access = strtolower($row_format['access']);
            }

            // If the row has a "related module", then take row "related module"
            // else by default related module will be null.
            if (array_key_exists('linked_type', $row_format) && array_key_exists('linked_id', $row_format)) {
                $data_linked_type = strtolower($row_format['linked_type']);

                if (in_array($data_linked_type, Task::relatedTypes())) {
                    $linked_model     = morph_to_model($data_linked_type);
                    $linked_id_exists = $linked_model::readableIdentifier($row_format['linked_id'])->get();

                    if ($linked_id_exists->count()) {
                        $linked_type = $data_linked_type;
                        $linked_id   = $linked_id_exists->first()->id;
                        $permitted_linked_ids = $linked_model::getAuthPermittedIds('task');

                        if ($data_linked_type == 'project' && array_key_exists('milestone_id', $row_format)) {
                            $milestone_exists = Milestone::whereName($row_format['milestone_id'])
                                ->where('project_id', $linked_id)
                                ->get();
                            $milestone = $milestone_exists->count() ? $milestone_exists->first()->id : $milestone;
                        }
                    }
                }
            }

            $row_format['task_status_id']        = $task_status->id;
            $row_format['task_owner']            = $owner;
            $row_format['priority']              = $priority;
            $row_format['completion_percentage'] = $completion_percentage;
            $row_format['start_date']            = $start_date;
            $row_format['due_date']              = $due_date;
            $row_format['linked_type']           = $linked_type;
            $row_format['linked_id']             = $linked_id;
            $row_format['milestone_id']          = $milestone;
            $row_format['access']                = $access;

            // Determine row will be created new record Or, update|overwrite existing record data.
            if (isset($row_format['name']) && $row_format['name'] != '') {
                $task_exists = Task::where('name', $row_format['name'])->get();

                if ($task_exists->count()) {
                    if ($import->import_type == 'new') {
                        $create    = true;
                        $warning[] = 'The task name field has duplicate records.';
                    } elseif ($import->import_type == 'update') {
                        $update = true;
                    } else {
                        $overwrite = true;
                    }
                } else {
                    $create = true;
                }
            } else {
                $create = true;
            }

            // Validate row data.
            $validation_data                 = $row_format;
            $validation_data['id']           = $task_exists->count() ? $task_exists->first()->id : null;
            $validation_data['change_owner'] = isset($row_format['task_owner']);
            $row_validation                  = Task::singleValidate($validation_data);

            if ($row_validation->fails()) {
                $error_msg     = $row_validation->getMessageBag()->toArray();
                $numeric_field = ['completion_percentage'];

                if (array_key_exists('name', $error_msg)) {
                    $skip      = true;
                    $warning[] = $error_msg['name'][0];
                }

                foreach ($error_msg as $error_field => $msg) {
                    if (in_array($error_field, $numeric_field)) {
                        $row_format[$error_field] = 0;
                    } elseif ($error_field == 'access') {
                        $row_format[$error_field] = 'public';
                    } else {
                        $row_format[$error_field] = null;
                    }
                }
            }

            // Check the auth user has permission to perform this operation
            // else if the related module exists or not.
            if (($update == true || $overwrite == true) && ! $task_exists->first()->auth_can_edit) {
                $skip = true;
                $warning[] = 'You don\'t have permission to edit the task.';
            } elseif (($update == true || $overwrite == true) && ! is_null($permitted_linked_ids)) {
                if (! ($row_format['linked_type'] == $task_exists->first()->linked_type
                    && $row_format['linked_id'] == $task_exists->first()->linked_id)
                    && ! in_array($row_format['linked_id'], $permitted_linked_ids)
                ) {
                    $row_format['linked_type'] = $task_exists->first()->linked_type;
                    $row_format['linked_id']   = $task_exists->first()->linked_id;

                    if ($task_exists->first()->linked_type == 'project') {
                        $row_format['milestone_id'] = $task_exists->first()->milestone_id;
                    }
                }
            } elseif ($create == true
                && ! is_null($permitted_linked_ids)
                && ! in_array($row_format['linked_id'], $permitted_linked_ids)
            ) {
                $skip = true;
                $warning[] = 'The selected related id is invalid.';
            }

            // If not skip then, create|update|overwrite else row consider as skipped data.
            if (! $skip) {
                $row_data   = replace_null_if_empty($row_format);
                $task_field = array_keys($row_data);
                $position   = Task::getTargetPositionVal(-1);

                if ($create) {
                    $task           = new Task;
                    $task->position = $position;
                    $task->save();
                    $task->update($row_data);
                    Revision::whereRevisionable_type('task')
                            ->whereRevisionable_id($task->id)
                            ->where('key', '!=', 'created_at')
                            ->forceDelete();
                }

                if ($update || $overwrite) {
                    $task             = $task_exists->first();
                    $row_data['name'] = $task->name;

                    if (! $task->auth_can_change_owner && array_key_exists('task_owner', $row_data)) {
                        $row_data['task_owner'] = $task->task_owner;
                    }
                }

                if ($update) {
                    if (array_key_exists('task_status_id', $row_data)
                        && (is_null($task->task_status_id) || empty($task->task_status_id))
                    ) {
                        $task->position = $position;
                    }

                    foreach ($task_field as $field) {
                        if ($field != 'name' && (is_null($task->$field) || empty($task->$field))) {
                            $task->$field = $row_data[$field];
                        }
                    }

                    $task->update();
                }

                if ($overwrite) {
                    if (array_key_exists('task_status_id', $row_data)
                        && $row_data['task_status_id'] != $task->task_status_id
                    ) {
                        $row_data['position'] = $position;
                    }

                    $task->update($row_data);
                }

                $report_data = [
                    'name'       => $task->name,
                    'due_date'   => $task->readableDateHtml('due_date'),
                    'status'     => $task->status->name,
                    'priority'   => ucfirst($task->priority),
                    'related_to' => non_property_checker($task->linked, 'name'),
                    'owner'      => $task->owner_name,
                    'error'      => $warning,
                ];

                if ($create) {
                    $created_data[] = $report_data;
                } else {
                    $updated_data[] = $report_data;
                }
            } else {
                $skip_row          = rename_array_key($row_original, $exchange_key);
                $skip_row_name     = isset($skip_row['name']) ? $skip_row['name'] : null;
                $skip_row_due_date = isset($skip_row['due_date']) ? $skip_row['due_date'] : null;
                $skip_row_status   = isset($skip_row['task_status_id']) ? $skip_row['task_status_id'] : null;
                $skip_row_priority = isset($skip_row['priority']) ? $skip_row['priority'] : null;
                $skip_row_related  = isset($skip_row['linked_type']) && isset($skip_row['linked_id'])
                                     ? $skip_row['linked_type'] . ' - ' . $skip_row['linked_id'] : null;
                $skip_row_owner    = isset($skip_row['task_owner']) ? $skip_row['task_owner'] : null;
                $skipped_data[]    = [
                    'name'       => $skip_row_name,
                    'due_date'   => $skip_row_due_date,
                    'status'     => $skip_row_status,
                    'priority'   => $skip_row_priority,
                    'related_to' => $skip_row_related,
                    'owner'      => $skip_row_owner,
                    'error'      => $warning,
                ];
            }
        }

        // Update import record data.
        $import->is_imported  = 1;
        $import->created_data = json_encode($created_data);
        $import->updated_data = json_encode($updated_data);
        $import->skipped_data = json_encode($skipped_data);
        $import->initial_data = null;
        $import->update();
    }
}
