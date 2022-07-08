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

class EventAttendee extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_attendees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'linked_id', 'linked_type', 'status'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['id_type', 'user_id'];

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

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get event attendee type with its id.
     *
     * @return string
     */
    public function getIdTypeAttribute()
    {
        return $this->linked_type . '-' . $this->linked_id;
    }

    /**
     * Get event attendee display type.
     *
     * @return string
     */
    public function getDisplayTypeAttribute()
    {
        return $this->linked_type == 'staff' ? 'User' : ucfirst($this->linked_type);
    }

    /**
     * Get event attendee user-id.
     *
     * @return int
     */
    public function getUserIdAttribute()
    {
        return $this->linked->user_id;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * A polymorphic, inverse one-to-many relationship with Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo()->withTrashed();
    }
}
