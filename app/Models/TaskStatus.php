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
use App\Models\Traits\DropdownCategoryTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class TaskStatus extends BaseModel
{
    use SoftDeletes;
    use PosionableTrait;
    use DropdownCategoryTrait;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'task_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'category', 'completion_percentage', 'description', 'fixed', 'position'];

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
     * Set task status permission.
     *
     * @return string
     */
    public function setPermission()
    {
        return 'custom_dropdowns.task_status';
    }

    /**
     * Set resource route name.
     *
     * @return string
     */
    public function setRoute()
    {
        return 'administration-dropdown-taskstatus';
    }

    /**
     * Set selected displayable columns.
     *
     * @return array
     */
    public function setSelectColumn()
    {
        return ['id', 'position', 'name', 'fixed', 'category', 'completion_percentage', 'description'];
    }

    /**
     * Get resource dropdown options HTML.
     *
     * @return string
     */
    public static function getOptionsHtml()
    {
        $status_list = self::orderBy('position')->get();
        $options     = '';

        foreach ($status_list as $status) {
            $freeze   = $status->category == 'closed' ? "freeze='true'" : "";
            $options .= "<option value='{$status->id}' relatedval='{$status->completion_percentage}' {$freeze}>" .
                            $status->name .
                        '</option>';
        }

        return $options;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A one-to-many relationship with Task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'task_status_id');
    }
}
