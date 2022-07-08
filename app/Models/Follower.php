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

class Follower extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'followers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['staff_id', 'linked_id', 'linked_type'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_id', 'notifiable'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['staff_id' => 'integer'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Keep history only field array list.
     *
     * @var array
     */
    protected $keepRevisionOf = ['deleted_at'];

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected $types = ['project', 'milestone', 'task', 'issue', 'event'];

    /**
     * Get valid type module names that are added by commas to make rules string.
     *
     * @return string
     */
    public static function getValidTypes()
    {
        return implode(',', with(new static)->types);
    }

    /**
     * Follower validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        return validator($data, [
            'type'   => 'required|in:' . self::getValidTypes(),
            'id'     => "required|exists:{$data['type']}s,id,deleted_at,NULL",
            'follow' => 'required|boolean',
        ]);
    }

    /**
     * Get follower data table format.
     *
     * @return array
     */
    public static function getFollowerTableFormat()
    {
        return [
            'json_columns' => \DataTable::jsonColumn(['name', 'phone', 'email']),
            'thead'        => ['NAME', 'PHONE', 'EMAIL'],
            'checkbox'     => false,
            'action'       => false,
        ];
    }

    /**
     * Get follower table data.
     *
     * @param \Illuminate\Http\Request            $request
     * @param \Illuminate\Database\Eloquent\Model $module
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getFollowersData($request, $module)
    {
        $followers = $module->followers()
                            ->whereNotIn('staff_id', $module->getStaffNotifeesAttribute(true))
                            ->orderBy('created_at')
                            ->groupBy('staff_id')
                            ->get()
                            ->sortByDesc(function ($follower, $key) {
                                if ($follower->staff_id == auth_staff()->id) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            });

        return \DataTable::of($followers)->addColumn('name', function ($follower) {
            return $follower->staff->profile_html;
        })->addColumn('phone', function ($follower) {
            return $follower->staff->phone;
        })->addColumn('email', function ($follower) {
            return $follower->staff->email;
        })->filter(function ($instance) use ($request) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                return $row->staff->globalSearch($request, ['name', 'email', 'phone']);
            });
        })->make(true);
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

    /**
     * Get to know the user can now actually follow the specified module.
     *
     * @return bool
     */
    public function getNotifiableAttribute()
    {
        if (! $this->staff->user->can($this->linked_type . '.view')) {
            return false;
        }

        if ($this->staff->admin
            || $this->linked->access == 'public'
            || $this->linked->access == 'public_rwd'
            || ($this->linked->access == 'private' && $this->linked->createdBy()->linked_id == $this->staff_id)
        ) {
            return true;
        }

        return false;
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
