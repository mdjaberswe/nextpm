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

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Import extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_name', 'module_name', 'is_imported', 'import_type',
        'created_data', 'updated_data', 'skipped_data', 'initial_data',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected static $modules = ['project', 'task', 'issue', 'event'];

    /**
     * Import data validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        return validator($data, [
            'import_file' => 'required|file',
            'import_type' => 'required|in:new,update,update_overwrite',
            'module'      => 'required|in:' . implode(',', self::$modules),
        ]);
    }

    /**
     * Get the auth user import permission status.
     *
     * @param string $module
     *
     * @return bool
     */
    public static function isAuthPermitted($module)
    {
        return permit($module . '.create') && permit('import.' . $module);
    }

    /**
     * CSV mapping validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validateMap($data)
    {
        return validator($data, ['import' => 'required|exists:imports,id,is_imported,0']);
    }

    /**
     * Get a valid import data modules list.
     *
     * @return array
     */
    public static function modules()
    {
        return self::$modules;
    }

    /**
     * CSV file header column auto-mapping with DB table column name.
     *
     * @param string $heading
     * @param string $column
     * @param array  $fields
     * @param array  $lower_fields
     *
     * @return string|null
     */
    public static function csvMapping($heading, $column, $fields, $lower_fields)
    {
        // Check CSV column header plain or snake case name exists in field array keys or values,
        // CSV column header only lower or together lower snake case name exists in field array lower case values.
        if (array_key_exists($heading, $fields)) {
            return $heading;
        } elseif (in_array($heading, $fields)) {
            return array_search($heading, $fields);
        } elseif (array_key_exists($column, $fields)) {
            return $column;
        } elseif (in_array($column, $fields)) {
            return array_search($column, $fields);
        } elseif (in_array(strtolower($heading), $lower_fields)) {
            return array_search(strtolower($heading), $lower_fields);
        } elseif (in_array(strtolower($column), $lower_fields)) {
            return array_search(strtolower($column), $lower_fields);
        } else {
            // Loop through all lower case fields
            foreach ($lower_fields as $key => $field) {
                $heading_semilarity = similar_text(strtolower($heading), $field, $perc_head);

                // if CSV column header name and lower case field value text matching percentage more than 70%,
                if (round($perc_head, 2) > 70) {
                    return $key;
                }

                $column_semilarity = similar_text(strtolower($column), $field, $perc_col);

                // if CSV column header snake case name and lower case field value text matching percentage more than 70%,
                if (round($perc_col, 2) > 70) {
                    return $key;
                }

                $field_words         = explode(' ', $field);
                $heading_words       = explode(' ', $heading);
                $heading_match_words = array_intersect($heading_words, $field_words);
                $heading_match       = (count($heading_match_words) / count($field_words)) * 100;

                // CSV column header plain and snake case both names cut into a piece of words and then
                // word matching algorithm, first and last word match check with lower case field words.
                if (round($heading_match, 2) > 55) {
                    return $key;
                } elseif (round($heading_match, 2) > 50
                    && (end($heading_match_words) == end($field_words)
                    || array_first($heading_match_words) == array_first($field_words))
                ) {
                    return $key;
                } elseif (round($heading_match, 2) >= 30 &&  count($heading_words) == count($heading_match_words)) {
                    return $key;
                }

                $column_words       = explode('_', $column);
                $column_match_words = array_intersect($column_words, $field_words);
                $column_match       = (count($column_match_words) / count($field_words)) * 100;

                if (round($column_match, 2) > 55) {
                    return $key;
                } elseif (round($column_match, 2) > 50
                    && (end($column_match_words) == end($field_words)
                    || array_first($column_match_words) == array_first($field_words))
                ) {
                    return $key;
                } elseif (round($column_match, 2) >= 30 && count($column_words) == count($column_match_words)) {
                    return $key;
                }
            }

            return null;
        }
    }

    /**
     * Render CSV header column and DB table column in HTML table columns.
     *
     * @param string $heading
     * @param string $column
     * @param array  $fields
     * @param mixed  $auto_select
     *
     * @return string
     */
    public static function renderMapRow($heading, $column, $fields, $auto_select = null)
    {
        $tr  = '<tr>';
        $tr .= '<td>' . $heading . '</td>';
        $tr .= '<td>' .
                    \Form::select($column, $fields, $auto_select, [
                        'class' => 'form-control white-select-single-clear',
                        'data-placeholder' => 'Choose a field',
                    ]) .
                '</td>';
        $tr .= '</tr>';

        return $tr;
    }

    /**
     * Delete import record that was not finally imported.
     *
     * @param string $module
     *
     * @return bool
     */
    public static function clearNonImported($module = null)
    {
        $lastday = date('Y-m-d H:i:s', strtotime('-1 days'));

        if (is_null($module)) {
            self::where('is_imported', 0)->where('created_at', '<', $lastday)->delete();
        } else {
            self::where('module_name', $module)->where('is_imported', 0)->where('created_at', '<', $lastday)->delete();
        }

        return true;
    }

    /**
     * Get a link with the import file name.
     *
     * @return string
     */
    public function getNameLinkIconAttribute()
    {
        return "<a class='like-txt add-multiple' {$this->data_btn}>
                   <span class='icon mdi mdi-file-excel' data-toggle='tooltip'
                   data-placement='top' title='Import From CSV'></span> " .
                   $this->file_name .
                '</a>';
    }

    /**
     * Import button attributes for import modal data records.
     *
     * @return string
     */
    public function getDataBtnAttribute()
    {
        $btn_attributes = "modal-title='Import " . ucfirst($this->module_name) . " Summary' " .
                          "modal-sub-title='{$this->file_name}' modal-datatable='true' save-new='false-all' " .
                          "cancel-txt='Close' datatable-url='import-data/{$this->id}' data-action='' " .
                          "data-content='{$this->module_name}.partials.modal-import-data'";

        return $btn_attributes;
    }

    /**
     * Get import data by type.
     *
     * @param string $type created|updated|skipped
     *
     * @return array
     */
    public function getData($type)
    {
        $type_data = $type . '_data';
        $data = json_decode($this->$type_data, true);
        unset($data['heading']);

        return collect($data);
    }

    /**
     * Get initial source data of the specified resource.
     *
     * @return string
     */
    public function getInitialDataAttribute()
    {
        $json_close_part = substr($this->attributes['initial_data'], -2);

        if ($json_close_part !== '}]') {
            $last = strrpos($this->attributes['initial_data'], '},');

            return substr($this->attributes['initial_data'], 0, $last) . '}]';
        }

        return $this->attributes['initial_data'];
    }

    /**
     * Get imported table data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTableData($request)
    {
        if ($request->has('name') && in_array($request->name, ['created', 'updated', 'skipped'])) {
            $data = $this->getData($request->name);
        } else {
            $data = collection_merge([$this->getData('created'), $this->getData('updated')]);
        }

        return \DataTable::of($data)->editColumn('error', function ($record) {
            if (count($record['error'])) {
                $error_html = '';

                foreach ($record['error'] as $error) {
                    $error_html .= "<span class='block max-overflow-ellipsis'>" . $error . '</span>';
                }

                return $error_html;
            }

            return null;
        })->make(true);
    }
}
