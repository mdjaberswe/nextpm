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
use App\Models\Project;
use App\Models\Import;
use App\Models\Revision;
use App\Models\ProjectStatus;
use App\Jobs\Job;

class ImportProjectData extends Job
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
        $created_data['heading'] = ['PROJECT NAME', 'STATUS', 'START DATE', 'END DATE', 'PROJECT OWNER', 'WARNINGS'];
        $updated_data['heading'] = ['PROJECT NAME', 'STATUS', 'START DATE', 'END DATE', 'PROJECT OWNER', 'WARNINGS'];
        $skipped_data['heading'] = ['PROJECT NAME', 'STATUS', 'START DATE', 'END DATE', 'PROJECT OWNER', 'ERRORS/WARNINGS'];
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

            $row_format     = rename_array_key($row, $exchange_key);
            $create         = false;
            $update         = false;
            $overwrite      = false;
            $skip           = false;
            $warning        = [];
            $member         = null;
            $start_date     = null;
            $end_date       = null;
            $access         = 'public';
            $owner          = auth_staff()->id;
            $project_status = ProjectStatus::orderBy('position')->get()->first();

            // If the row has "project status", then take row "project status"
            // else by default "project status" will be first position project status.
            if (array_key_exists('project_status_id', $row_format)) {
                $project_status_exists = ProjectStatus::where('name', $row_format['project_status_id'])->get();
                $project_status = $project_status_exists->count() ? $project_status_exists->first() : $project_status;
            }

            // If the row has "owner", then take row "owner" else by default authenticate user will be record owner.
            if (array_key_exists('project_owner', $row_format)) {
                $owner_exists = User::where('email', $row_format['project_owner'])->onlyStaff()->get();
                $owner        = $owner_exists->count() ? $owner_exists->first()->linked->id : $owner;
            }

            // If the row has "member", then take row "member" else by default "member" will be null.
            if (array_key_exists('member', $row_format)) {
                $all_member   = explode(',', $row_format['member']);
                $member_array = [];

                foreach ($all_member as $single_member) {
                    $single_member = trim($single_member);
                    $member_exists = User::where('email', $single_member)->onlyStaff()->get();

                    if ($member_exists->count()) {
                        array_push($member_array, $member_exists->first()->linked->id);
                    }
                }

                if (count($member_array)) {
                    $member = $member_array;
                }
            }

            // If the row has "start date", then take row "start date" else by default "start date" will be null.
            if (array_key_exists('start_date', $row_format)) {
                $start_timestamp = strtotime($row_format['start_date']);

                if ($start_timestamp) {
                    $start_date = date('Y-m-d', $start_timestamp);
                }
            }

            // If the row has "end date", then take row "end date" else by default "end date" will be null.
            if (array_key_exists('end_date', $row_format)) {
                $end_timestamp = strtotime($row_format['end_date']);

                if ($end_timestamp) {
                    $end_date = date('Y-m-d', $end_timestamp);
                }

                if (isset($start_timestamp) && $start_timestamp > $end_timestamp) {
                    $start_date = null;
                }
            }

            // If the row has "access", then take row "access" else by default "access" will be public.
            if (array_key_exists('access', $row_format)) {
                $access = strtolower($row_format['access']);
            }

            $row_format['project_status_id'] = $project_status->id;
            $row_format['project_owner']     = $owner;
            $row_format['member']            = $member;
            $row_format['start_date']        = $start_date;
            $row_format['end_date']          = $end_date;
            $row_format['access']            = $access;

            // Determine row will be created new record Or, update|overwrite existing record data.
            if (isset($row_format['name']) && $row_format['name'] != '') {
                $project_exists = Project::where('name', $row_format['name'])->get();

                if ($project_exists->count()) {
                    if ($import->import_type == 'new') {
                        $create    = true;
                        $warning[] = 'The project name field has duplicate records.';
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
            $validation_data['id']           = $project_exists->count() ? $project_exists->first()->id : null;
            $validation_data['change_owner'] = isset($row_format['project_owner']);
            $row_validation                  = Project::singleValidate($validation_data);

            if ($row_validation->fails()) {
                $error_msg = $row_validation->getMessageBag()->toArray();

                if (array_key_exists('name', $error_msg)) {
                    $skip      = true;
                    $warning[] = $error_msg['name'][0];
                }

                foreach ($error_msg as $error_field => $msg) {
                    if ($error_field == 'access') {
                        $row_format[$error_field] = 'public';
                    } else {
                        $row_format[$error_field] = null;
                    }
                }
            }

            // Check the auth user has permission to perform this operation.
            if (($update == true || $overwrite == true) && ! $project_exists->first()->auth_can_edit) {
                $skip = true;
                $warning[] = 'You don\'t have permission to edit the project.';
            }

            // If not skip then, create|update|overwrite else row consider as skipped data.
            if (! $skip) {
                $row_data      = replace_null_if_empty($row_format);
                $project_field = array_keys($row_data);
                $position      = Project::getTargetPositionVal(-1);

                if ($create) {
                    $project = new Project;
                    $project->position = $position;
                    $project->save();
                    $project->update($row_data);
                    Revision::whereRevisionable_type('project')
                            ->whereRevisionable_id($project->id)
                            ->where('key', '!=', 'created_at')
                            ->forceDelete();

                    if (! is_null($row_data['member']) && count($row_data['member'])) {
                        $project->members()->attach($row_data['member'], Project::getMinimalPermissions());
                    }
                }

                if ($update || $overwrite) {
                    $project = $project_exists->first();
                    $row_data['name'] = $project->name;

                    if (! $project->auth_can_change_owner && array_key_exists('project_owner', $row_data)) {
                        $row_data['project_owner'] = $project->project_owner;
                    }
                }

                if ($update) {
                    if (array_key_exists('project_status_id', $row_data)
                        && (is_null($project->project_status_id) || empty($project->project_status_id))
                    ) {
                        $project->position = $position;
                    }

                    foreach ($project_field as $field) {
                        if ($field != 'name'
                            && $field != 'member'
                            && (is_null($project->$field) || empty($project->$field))
                        ) {
                            $project->$field = $row_data[$field];
                        }
                    }

                    $project->update();

                    if (! is_null($row_data['member'])
                        && count($row_data['member'])
                        && $project->members->count() == 0
                    ) {
                        $project->members()->attach($row_data['member'], Project::getMinimalPermissions());
                    }
                }

                if ($overwrite) {
                    if (array_key_exists('project_status_id', $row_data)
                        && $row_data['project_status_id'] != $project->project_status_id
                    ) {
                        $row_data['position'] = $position;
                    }

                    $project->update($row_data);

                    if (! is_null($row_data['member']) && count($row_data['member'])) {
                        $project->members()->detach();
                        $project->members()->attach($row_data['member'], Project::getMinimalPermissions());
                    }
                }

                $project->members()->detach($project->project_owner);
                $project->members()->attach($project->project_owner, Project::getAllPermissions());

                $report_data = [
                    'name'       => $project->name,
                    'status'     => $project->status->name,
                    'start_date' => $project->start_date_html,
                    'end_date'   => $project->end_date_html,
                    'owner'      => $project->owner_name,
                    'error'      => $warning,
                ];

                if ($create) {
                    $created_data[] = $report_data;
                } else {
                    $updated_data[] = $report_data;
                }
            } else {
                $skip_row            = rename_array_key($row_original, $exchange_key);
                $skip_row_name       = isset($skip_row['name']) ? $skip_row['name'] : null;
                $skip_row_status     = isset($skip_row['project_status_id']) ? $skip_row['project_status_id'] : null;
                $skip_row_start_date = isset($skip_row['start_date']) ? $skip_row['start_date'] : null;
                $skip_row_end_date   = isset($skip_row['end_date']) ? $skip_row['end_date'] : null;
                $skip_row_owner      = isset($skip_row['project_owner']) ? $skip_row['project_owner'] : null;
                $skipped_data[]      = [
                    'name'       => $skip_row_name,
                    'status'     => $skip_row_status,
                    'start_date' => $skip_row_start_date,
                    'end_date'   => $skip_row_end_date,
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
