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
use App\Models\Issue;
use App\Models\Import;
use App\Models\Revision;
use App\Models\Milestone;
use App\Models\IssueType;
use App\Models\IssueStatus;
use App\Jobs\Job;

class ImportIssueData extends Job
{
    protected $import;
    protected $map;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Import $import
     * @param array              $data
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
        $created_data['heading'] = ['ISSUE', 'DUE DATE', 'STATUS', 'SEVERITY', 'RELATED TO', 'ISSUE OWNER', 'WARNINGS'];
        $updated_data['heading'] = ['ISSUE', 'DUE DATE', 'STATUS', 'SEVERITY', 'RELATED TO', 'ISSUE OWNER', 'WARNINGS'];
        $skipped_data['heading'] = ['ISSUE', 'DUE DATE', 'STATUS', 'SEVERITY', 'RELATED TO', 'ISSUE OWNER', 'ERRORS/WARNINGS'];
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

            $row_format           = rename_array_key($row, $exchange_key);
            $create               = false;
            $update               = false;
            $overwrite            = false;
            $skip                 = false;
            $warning              = [];
            $issue_type           = null;
            $owner                = null;
            $severity             = null;
            $reproducible         = null;
            $start_date           = null;
            $due_date             = null;
            $linked_type          = null;
            $linked_id            = null;
            $permitted_linked_ids = null;
            $release_milestone    = null;
            $affected_milestone   = null;
            $access               = 'public';
            $issue_status         = IssueStatus::orderBy('position')->get()->first();

            // If the row has "issue status", then take row "issue status"
            // else by default "issue status" will be first position issue status.
            if (array_key_exists('issue_status_id', $row_format)) {
                $issue_status_exists = IssueStatus::where('name', $row_format['issue_status_id'])->get();
                $issue_status        = $issue_status_exists->count() ? $issue_status_exists->first() : $issue_status;
            }

            // If the row has "issue type", then take row "issue type" else by default "issue type" will be null.
            if (array_key_exists('issue_type_id', $row_format)) {
                $issue_type_exists = IssueType::where('name', $row_format['issue_type_id'])->get();
                $issue_type        = $issue_type_exists->count() ? $issue_type_exists->first()->id : $issue_type;
            }

            // If the row has "owner", then take row "owner" else by default "owner" will be null.
            if (array_key_exists('issue_owner', $row_format)) {
                $owner_exists = User::where('email', $row_format['issue_owner'])->onlyStaff()->get();
                $owner        = $owner_exists->count() ? $owner_exists->first()->linked->id : $owner;
            }

            // If the row has "severity", then take row "severity" else by default "severity" will be null.
            if (array_key_exists('severity', $row_format)) {
                $data_severity = strtolower($row_format['severity']);

                if (in_array($data_severity, Issue::severitylist())) {
                    $severity = $data_severity;
                }
            }

            // If the row has "reproducible", then take row "reproducible" else by default "reproducible" will be null.
            if (array_key_exists('reproducible', $row_format)) {
                $data_reproducible = trim_lower_snake($row_format['reproducible']);

                if (in_array($data_reproducible, Issue::reproduciblelist())) {
                    $reproducible = $data_reproducible;
                }
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

            // If the row has a "related module", then take row "related module"
            // else by default related module will be null.
            if (array_key_exists('linked_type', $row_format) && array_key_exists('linked_id', $row_format)) {
                $data_linked_type = strtolower($row_format['linked_type']);

                if (in_array($data_linked_type, Issue::relatedTypes())) {
                    $linked_model     = morph_to_model($data_linked_type);
                    $linked_id_exists = $linked_model::readableIdentifier($row_format['linked_id'])->get();

                    if ($linked_id_exists->count()) {
                        $linked_type = $data_linked_type;
                        $linked_id   = $linked_id_exists->first()->id;
                        $permitted_linked_ids = $linked_model::getAuthPermittedIds('issue');

                        if ($data_linked_type == 'project') {
                            if (array_key_exists('release_milestone_id', $row_format)) {
                                $release_milestone_exists = Milestone::whereName($row_format['release_milestone_id'])
                                    ->where('project_id', $linked_id)
                                    ->get();
                                $release_milestone = $release_milestone_exists->count()
                                                     ? $release_milestone_exists->first()->id
                                                     : $release_milestone;
                            }

                            if (array_key_exists('affected_milestone_id', $row_format)) {
                                $affected_milestone_exists = Milestone::whereName($row_format['affected_milestone_id'])
                                    ->where('project_id', $linked_id)
                                    ->get();
                                $affected_milestone = $affected_milestone_exists->count()
                                                      ? $affected_milestone_exists->first()->id
                                                      : $affected_milestone;
                            }
                        }
                    }
                }
            }

            // If the row has "access", then take row "access" else by default "access" will be public.
            if (array_key_exists('access', $row_format)) {
                $access = strtolower($row_format['access']);
            }

            $row_format['issue_status_id']       = $issue_status->id;
            $row_format['issue_type_id']         = $issue_type;
            $row_format['issue_owner']           = $owner;
            $row_format['severity']              = $severity;
            $row_format['reproducible']          = $reproducible;
            $row_format['start_date']            = $start_date;
            $row_format['due_date']              = $due_date;
            $row_format['linked_type']           = $linked_type;
            $row_format['linked_id']             = $linked_id;
            $row_format['release_milestone_id']  = $release_milestone;
            $row_format['affected_milestone_id'] = $affected_milestone;
            $row_format['access']                = $access;

            // Determine row will be created new record Or, update|overwrite existing record data.
            if (isset($row_format['name']) && $row_format['name'] != '') {
                $issue_exists = Issue::where('name', $row_format['name'])->get();

                if ($issue_exists->count()) {
                    if ($import->import_type == 'new') {
                        $create    = true;
                        $warning[] = 'The issue name field has duplicate records.';
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
            $validation_data['id']           = $issue_exists->count() ? $issue_exists->first()->id : null;
            $validation_data['change_owner'] = isset($row_format['issue_owner']);
            $row_validation                  = Issue::singleValidate($validation_data);

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

            // Check the auth user has permission to perform this operation
            // else if the related module exists or not.
            if (($update == true || $overwrite == true) && ! $issue_exists->first()->auth_can_edit) {
                $skip = true;
                $warning[] = 'You don\'t have permission to edit the issue.';
            } elseif (($update == true || $overwrite == true) && ! is_null($permitted_linked_ids)) {
                if (! ($row_format['linked_type'] == $issue_exists->first()->linked_type
                    && $row_format['linked_id'] == $issue_exists->first()->linked_id)
                    && ! in_array($row_format['linked_id'], $permitted_linked_ids)
                ) {
                    $row_format['linked_type'] = $issue_exists->first()->linked_type;
                    $row_format['linked_id']   = $issue_exists->first()->linked_id;

                    if ($issue_exists->first()->linked_type == 'project') {
                        $row_format['release_milestone_id'] = $issue_exists->first()->release_milestone_id;
                        $row_format['affected_milestone_id'] = $issue_exists->first()->affected_milestone_id;
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
                $row_data    = replace_null_if_empty($row_format);
                $issue_field = array_keys($row_data);
                $position    = Issue::getTargetPositionVal(-1);

                if ($create) {
                    $issue           = new Issue;
                    $issue->position = $position;
                    $issue->save();
                    $issue->update($row_data);
                    Revision::whereRevisionable_type('issue')
                            ->whereRevisionable_id($issue->id)
                            ->where('key', '!=', 'created_at')
                            ->forceDelete();
                }

                if ($update || $overwrite) {
                    $issue            = $issue_exists->first();
                    $row_data['name'] = $issue->name;

                    if (! $issue->auth_can_change_owner && array_key_exists('issue_owner', $row_data)) {
                        $row_data['issue_owner'] = $issue->issue_owner;
                    }
                }

                if ($update) {
                    if (array_key_exists('issue_status_id', $row_data)
                        && (is_null($issue->issue_status_id) || empty($issue->issue_status_id))
                    ) {
                        $issue->position = $position;
                    }

                    foreach ($issue_field as $field) {
                        if ($field != 'name' && (is_null($issue->$field) || empty($issue->$field))) {
                            $issue->$field = $row_data[$field];
                        }
                    }

                    $issue->update();
                }

                if ($overwrite) {
                    if (array_key_exists('issue_status_id', $row_data)
                        && $row_data['issue_status_id'] != $issue->issue_status_id
                    ) {
                        $row_data['position'] = $position;
                    }

                    $issue->update($row_data);
                }

                $report_data = [
                    'name'       => $issue->name,
                    'due_date'   => $issue->readableDateHtml('due_date'),
                    'status'     => $issue->status->name,
                    'severity'   => ucfirst($issue->severity),
                    'related_to' => non_property_checker($issue->linked, 'name'),
                    'owner'      => $issue->owner_name,
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
                $skip_row_status   = isset($skip_row['issue_status_id']) ? $skip_row['issue_status_id'] : null;
                $skip_row_severity = isset($skip_row['severity']) ? $skip_row['severity'] : null;
                $skip_row_related  = isset($skip_row['linked_type']) && isset($skip_row['linked_id'])
                                     ? $skip_row['linked_type'] . ' - ' . $skip_row['linked_id'] : null;
                $skip_row_owner    = isset($skip_row['issue_owner']) ? $skip_row['issue_owner'] : null;
                $skipped_data[]    = [
                    'name'       => $skip_row_name,
                    'due_date'   => $skip_row_due_date,
                    'status'     => $skip_row_status,
                    'severity'   => $skip_row_severity,
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
