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

class AllowedStaff extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'allowed_staffs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['staff_id', 'linked_id', 'linked_type', 'can_view', 'can_edit', 'can_delete'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected $types = ['project', 'milestone', 'task', 'issue', 'event'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Get valid type module names that are added by commas to make rules string.
     *
     * @return string
     */
    public static function getValidTypes()
    {
        return implode(',', with(new static)->types);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get user id.
     *
     * @return int
     */
    public function getUserIdAttribute()
    {
        return $this->staff->user_id;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * A polymorphic, inverse one-to-many relationship with Project|Milestone|Task|Issue|Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo();
    }
}
