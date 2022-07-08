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

use Excel;
use Notification;
use App\Models\User;
use App\Models\Import;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminImportController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Import       $import
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function importData(Request $request, Import $import)
    {
        return $import->getTableData($request);
    }

    /**
     * Get CSV upload form to import data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getCsv(Request $request)
    {
        $status     = true;
        $html       = null;
        $csv_import = null;

        // If the posted module is valid and the auth user has permission to import data of the module.
        if (isset($request->module)
            && in_array($request->module, Import::modules())
            && Import::isAuthPermitted($request->module)
        ) {
            $csv_import = route('admin.import.map');
            $html = view('partials.modals.import.csv', ['module' => $request->module])->render();
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'html' => $html]);
    }

    /**
     * Map uploaded CSV file with database column.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function map(Request $request)
    {
        $status     = true;
        $errors     = null;
        $html       = null;
        $info       = [];
        $data       = $request->all();
        $validation = Import::validate($data);
        $title      = "Map Columns to " . ucfirst($request->module) . " Fields";

        // If validation passes and the auth user has permission to import data of the module.
        if ($validation->passes() && Import::isAuthPermitted($request->module)) {
            $file      = $request->file('import_file');
            $file_name = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $path      = $file->getRealPath();

            // Valid extension checker.
            if (in_array($extension, ['csv', 'xls', 'xlsx'])) {
                $model          = morph_to_model($request->module);
                $excel          = \Excel::load($path);
                $row_collection = $excel->formatDates(true, 'Y-m-d H:i:s')->get();
                $row_array      = $row_collection->toArray();

                // If CSV file has row data to import.
                if ($row_collection->count()) {
                    $import               = new Import;
                    $import->file_name    = $file_name;
                    $import->module_name  = $request->module;
                    $import->import_type  = $request->import_type;
                    $import->initial_data = json_encode($row_array);
                    $import->save();

                    $list = $model::fieldlist();
                    $info['import'] = $import->id;

                    asort($list);
                    config(['excel.import.heading' => 'original']);
                    $headings = $excel->get()->first()->keys()->toArray();
                    config(['excel.import.heading' => 'slugged']);

                    $keys             = $row_collection->first()->keys()->toArray();
                    $field_list       = ['' => 'Choose a field'] + $list;
                    $lower_field_list = array_map('strtolower', $field_list);
                    $selected_fields  = [];
                    $tr               = '';

                    // Loop through every column header of uploaded CSV and map with DB table field.
                    foreach ($keys as $key => $column) {
                        $auto_select = Import::csvMapping($headings[$key], $column, $field_list, $lower_field_list);

                        if (! is_null($auto_select) && ! in_array($auto_select, $selected_fields)) {
                            $selected_fields[] = $auto_select;
                        } else {
                            $auto_select = null;
                        }

                        $tr .= Import::renderMapRow($headings[$key], $column, $field_list, $auto_select);
                    }

                    $html = view('partials.modals.import.map', ['tr' => $tr, 'module' => $request->module])->render();
                } else {
                    $status = false;
                    $errors['import_file'][] = 'The import file has no data.';
                }
            } else {
                $status = false;
                $errors['import_file'][] = 'The import file extension is not valid.';
            }
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json([
            'status'     => $status,
            'errors'     => $errors,
            'html'       => $html,
            'info'       => $info,
            'modalTitle' => $title,
        ]);
    }

    /**
     * Import CSV data into DB.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $status     = true;
        $errors     = null;
        $data       = $request->all();
        $validation = Import::validateMap($data);

        // If validation passes then import data.
        if ($validation->passes()) {
            unset($data['_token']);
            $import            = Import::find($request->import);
            $is_auth_permitted = Import::isAuthPermitted($import->module_name);
            $model             = morph_to_model($import->module_name);
            $column            = array_keys($data);
            $import_data       = json_decode($import->initial_data, true);
            $column_key        = array_keys($import_data[0]);
            $import_column     = array_prepend($column_key, 'import');

            // If the posted column is equal to temp data of import DB and the auth user has permission to import.
            if ($column === $import_column && $is_auth_permitted) {
                $field = array_keys($model::fieldlist());
                $in    = 'in:' . implode(',', $field);
                $rules = array_fill_keys($column_key, $in);
                $field_validation = validator($data, $rules);

                // If field validation fails.
                if ($field_validation->fails()) {
                    $status = false;
                    $field_errors = array_flatten($field_validation->getMessageBag()->toArray());

                    foreach ($field_errors as $field_error) {
                        $errors['field'][] = $field_error;
                    }
                }

                if ($status == true) {
                    $non_repeated = array_unique($data);
                    $repeated     = array_diff_assoc($data, $non_repeated);
                    $repeated     = array_filter($repeated);

                    // Repeat selected field checker.
                    if (count($repeated)) {
                        $status = false;

                        foreach ($repeated as $repeated_field_key) {
                            $repeated_field    = $model::fieldlist()[$repeated_field_key];
                            $errors['field'][] = 'The ' . strtolower($repeated_field) . ' field is repeated.';
                        }
                    }

                    if ($status == true) {
                        $module_validation = $model::importValidate($data);
                        $status = $module_validation['status'];

                        if ($status == false) {
                            foreach ($module_validation['errors'] as $module_error) {
                                $errors['field'][] = $module_error;
                            }
                        }

                        // If related model import validation passes.
                        if ($status == true) {
                            // Ajax quick response for not delaying execution.
                            flush_response(['status' => true]);

                            // Dispatch job for importing data from CSV and store in DB.
                            $job  = '\App\Jobs\Import' . ucfirst($import->module_name) . 'Data';
                            $info = ['map' => $data, 'import_data' => $import_data];
                            dispatch(new $job($import, $info));

                            // Notify Administrators
                            // and the responsible auth user who imported data after successful completion.
                            $notifees = User::onlyStaff()->withRole('administrator')->pluck('id')->toArray();
                            array_push($notifees, auth()->user()->id);
                            Notification::send(
                                get_wherein('user', $notifees),
                                new CrudNotification('import_' . $import->module_name, $import->id, [
                                    'field'       => ucfirst($import->module_name),
                                    'key'         => $import->module_name,
                                    'file_name'   => $import->file_name,
                                    'count'       => [
                                        'created' => count($import->fresh()->getData('created')),
                                        'updated' => count($import->fresh()->getData('updated')),
                                        'skipped' => count($import->fresh()->getData('skipped')),
                                    ],
                                ])
                            );
                        }
                    }
                }
            } else {
                $status = false;

                if (! $is_auth_permitted) {
                    $errors['permission'][] = 'You don\'t have permission to import ' . $import->module_name;
                }

                if ($column !== $import_column) {
                    $errors['column'][] = 'File headers have not matched to file columns.';
                }
            }
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        if ($status == false) {
            return response()->json(['status' => $status, 'errors' => $errors]);
        }
    }
}
