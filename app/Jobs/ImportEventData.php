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
use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\Import;
use App\Models\Revision;
use App\Jobs\Job;

class ImportEventData extends Job
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
        $created_data['heading'] = ['EVENT NAME', 'START DATE', 'END DATE', 'LOCATION', 'PRIORITY', 'RELATED TO', 'EVENT OWNER', 'WARNINGS'];
        $updated_data['heading'] = ['EVENT NAME', 'START DATE', 'END DATE', 'LOCATION', 'PRIORITY', 'RELATED TO', 'EVENT OWNER', 'WARNINGS'];
        $skipped_data['heading'] = ['EVENT NAME', 'START DATE', 'END DATE', 'LOCATION', 'PRIORITY', 'RELATED TO', 'EVENT OWNER', 'ERRORS/WARNINGS'];
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
            $priority             = null;
            $linked_type          = null;
            $linked_id            = null;
            $permitted_linked_ids = null;
            $access               = 'public';
            $owner                = auth_staff()->id;
            $start_date           = Carbon::now()->setTime(10, 0)->format('Y-m-d G:i:s');
            $end_date             = Carbon::now()->setTime(11, 0)->format('Y-m-d G:i:s');

            // If the row has "owner", then take row "owner" else by default authenticate user will be record owner.
            if (array_key_exists('event_owner', $row_format)) {
                $owner_exists = User::where('email', $row_format['event_owner'])->onlyStaff()->get();
                $owner        = $owner_exists->count() ? $owner_exists->first()->linked->id : $owner;
            }

            // If the row has "priority", then take row "priority" else by default "priority" will be null.
            if (array_key_exists('priority', $row_format)) {
                $data_priority = strtolower($row_format['priority']);

                if (in_array($data_priority, Event::prioritylist())) {
                    $priority = $data_priority;
                }
            }

            // If the row has "start date", then take row "start date"
            // else by default "start date" will be today at 10:00 AM.
            if (array_key_exists('start_date', $row_format)) {
                $start_timestamp = strtotime($row_format['start_date']);

                if ($start_timestamp) {
                    $start_date = date('Y-m-d G:i:s', $start_timestamp);
                }
            }

            // If the row has "end date", then take row "end date"
            // else by default "end date" will be today at 11:00 AM.
            if (array_key_exists('end_date', $row_format)) {
                $end_timestamp = strtotime($row_format['end_date']);

                if ($end_timestamp) {
                    $end_date = date('Y-m-d G:i:s', $end_timestamp);
                }

                if (isset($start_timestamp) && $start_timestamp > $end_timestamp) {
                    $start_date = date('Y-m-d G:i:s', strtotime('-1 hour', strtotime($end_date)));
                }
            }

            // If the row has a "related module", then take row "related module" else by default related module will be null.
            if (array_key_exists('linked_type', $row_format) && array_key_exists('linked_id', $row_format)) {
                $data_linked_type = strtolower($row_format['linked_type']);

                if (in_array($data_linked_type, Event::relatedTypes())) {
                    $linked_model     = morph_to_model($data_linked_type);
                    $linked_id_exists = $linked_model::readableIdentifier($row_format['linked_id'])->get();

                    if ($linked_id_exists->count()) {
                        $linked_type = $data_linked_type;
                        $linked_id   = $linked_id_exists->first()->id;
                        $permitted_linked_ids = $linked_model::getAuthPermittedIds('event');
                    }
                }
            }

            // If the row has "access", then take row "access" else by default "access" will be public.
            if (array_key_exists('access', $row_format)) {
                $access = strtolower($row_format['access']);
            }

            $row_format['event_owner'] = $owner;
            $row_format['priority']    = $priority;
            $row_format['start_date']  = $start_date;
            $row_format['end_date']    = $end_date;
            $row_format['linked_type'] = $linked_type;
            $row_format['linked_id']   = $linked_id;
            $row_format['access']      = $access;

            // Determine row will be created new record Or, update|overwrite existing record data.
            if (isset($row_format['name']) && $row_format['name'] != '') {
                $event_exists = Event::where('name', $row_format['name'])->get();

                if ($event_exists->count()) {
                    if ($import->import_type == 'new') {
                        $create    = true;
                        $warning[] = 'The event name field has duplicate records.';
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
            $validation_data = $row_format;
            $validation_data['id'] = $event_exists->count() ? $event_exists->first()->id : null;
            $validation_data['change_owner'] = isset($row_format['event_owner']);
            $row_validation = Event::singleValidate($validation_data);

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
            if (($update == true || $overwrite == true) && ! $event_exists->first()->auth_can_edit) {
                $skip = true;
                $warning[] = 'You don\'t have permission to edit the event.';
            } elseif (($update == true || $overwrite == true) && ! is_null($permitted_linked_ids)) {
                if (! ($row_format['linked_type'] == $event_exists->first()->linked_type
                    && $row_format['linked_id'] == $event_exists->first()->linked_id)
                    && ! in_array($row_format['linked_id'], $permitted_linked_ids)
                ) {
                    $row_format['linked_type'] = $event_exists->first()->linked_type;
                    $row_format['linked_id']   = $event_exists->first()->linked_id;
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
                $event_field = array_keys($row_data);

                if ($create) {
                    $event = new Event;
                    $event->save();
                    $event->update($row_data);
                    Revision::whereRevisionable_type('event')
                            ->whereRevisionable_id($event->id)
                            ->where('key', '!=', 'created_at')
                            ->forceDelete();
                }

                if ($update || $overwrite) {
                    $event            = $event_exists->first();
                    $row_data['name'] = $event->name;

                    if (! $event->auth_can_change_owner && array_key_exists('event_owner', $row_data)) {
                        $row_data['event_owner'] = $event->event_owner;
                    }
                }

                if ($update) {
                    foreach ($event_field as $field) {
                        if ($field != 'name' && (is_null($event->$field) || empty($event->$field))) {
                            $event->$field = $row_data[$field];
                        }
                    }

                    $event->update();
                }

                if ($overwrite) {
                    $event->update($row_data);
                }

                $report_data = [
                    'name'       => $event->name,
                    'start_date' => $event->start_date_html,
                    'end_date'   => $event->end_date_html,
                    'location'   => $event->location,
                    'priority'   => ucfirst($event->priority),
                    'related_to' => non_property_checker($event->linked, 'name'),
                    'owner'      => $event->owner_name,
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
                $skip_row_start_date = isset($skip_row['start_date']) ? $skip_row['start_date'] : null;
                $skip_row_end_date   = isset($skip_row['end_date']) ? $skip_row['end_date'] : null;
                $skip_row_location   = isset($skip_row['location']) ? $skip_row['location'] : null;
                $skip_row_priority   = isset($skip_row['priority']) ? $skip_row['priority'] : null;
                $skip_row_related    = isset($skip_row['linked_type']) && isset($skip_row['linked_id'])
                                       ? $skip_row['linked_type'] . ' - ' . $skip_row['linked_id'] : null;
                $skip_row_owner      = isset($skip_row['event_owner']) ? $skip_row['event_owner'] : null;
                $skipped_data[]      = [
                    'name'       => $skip_row_name,
                    'start_date' => $skip_row_start_date,
                    'end_date'   => $skip_row_end_date,
                    'location'   => $skip_row_location,
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
