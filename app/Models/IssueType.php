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

use App\Models\Traits\PosionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class IssueType extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;
    use PosionableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'issue_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'position'];

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
     * Issue type form validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        $position_ids = self::commaSeparatedIds([0, -1]);
        $unique_name  = 'unique:issue_types,name';

        if (isset($data['id'])) {
            $unique_name .= ',' . $data['id'];
        }

        $rules = [
            'name'        => 'required|max:200|' . $unique_name,
            'position'    => 'required|integer|in:' . $position_ids,
            'description' => 'max:65535',
        ];

        return validator($data, $rules);
    }

    /**
     * Set resource permission.
     *
     * @return string
     */
    public function setPermission()
    {
        return 'custom_dropdowns.issue_type';
    }

    /**
     * Set resource route name.
     *
     * @return string
     */
    public function setRoute()
    {
        return 'administration-dropdown-issuetype';
    }

    /**
     * Set selected displayable columns.
     *
     * @return array
     */
    public function setSelectColumn()
    {
        return ['id', 'position', 'name', 'description'];
    }

    /**
     * Get resource data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        return [
            'thead'        => ['NAME', 'DESCRIPTION'],
            'action'       => self::allowAction(),
            'drag_drop'    => permit(self::getPermission() . '.edit'),
            'json_columns' => \DataTable::jsonColumn([
                'sequence' => ['className' => 'reorder'], 'name', 'description', 'action',
            ], self::hideColumns()),
        ];
    }

    /**
     * Get resource tabel data.
     *
     * @param array                    $data
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($data, $request)
    {
        return \DataTable::of($data)->addColumn('sequence', function ($issue_type) {
            return $issue_type->drag_and_drop;
        })->addColumn('action', function ($issue_type) {
            return $issue_type->getActionHtml('Type', $issue_type->del_route['name'], null, [
                'edit'   => permit('custom_dropdowns.issue_type.edit'),
                'delete' => permit('custom_dropdowns.issue_type.delete'),
            ]);
        })->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A one-to-many relationship with Issue.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issues()
    {
        return $this->hasMany(Issue::class, 'issue_type_id');
    }
}
