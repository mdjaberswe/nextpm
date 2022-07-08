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

class IssueStatus extends BaseModel
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
    protected $table = 'issue_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'category', 'description', 'fixed', 'position'];

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
     * Set issue status permission.
     *
     * @return string
     */
    public function setPermission()
    {
        return 'custom_dropdowns.issue_status';
    }

    /**
     * Set resource route name.
     *
     * @return string
     */
    public function setRoute()
    {
        return 'administration-dropdown-issuestatus';
    }

    /**
     * Set selected displayable columns.
     *
     * @return array
     */
    public function setSelectColumn()
    {
        return ['id', 'position', 'name', 'fixed', 'category', 'description'];
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
        return $this->hasMany(Issue::class, 'issue_status_id');
    }
}
