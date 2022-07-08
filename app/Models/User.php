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

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Traits\HistoryTrait;
use Illuminate\Notifications\Notifiable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use \HighIdeas\UsersOnline\Traits\UsersOnlineTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use EntrustUserTrait {
        restore as private restoreEntrust;
    }

    use SoftDeletes {
        restore as private restoreSoftDeletes;
    }

    use HistoryTrait;
    use Notifiable;
    use UsersOnlineTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'status', 'password', 'last_login', 'linked_id', 'linked_type'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'last_login'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = false;

    /**
     * Don't keep history fields array list.
     *
     * @var array
     */
    protected $dontKeepRevisionOf = ['remember_token', 'password', 'last_login', 'linked_id', 'linked_type'];

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFields = ['status' => 'boolean:Inactive|Active'];

    /**
     * Solve conflict of EntrustUserTrait with SoftDeleteTrait.
     *
     * @return void
     */
    public function restore()
    {
        $this->restoreEntrust();
        $this->restoreSoftDeletes();
    }

    /**
     * The "booting" method of the User model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * The query only gets staff type users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyStaff($query)
    {
        return $query->where('linked_type', 'staff');
    }

    /**
     * The query for only get specific type users by using whereIn and whereNotIn clause.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $type
     * @param array                                 $type_ids
     * @param array                                 $not_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTypeInIds($query, $type, $type_ids, $not_ids = [0])
    {
        if (count($type_ids)) {
            return $query->where('linked_type', $type)
                         ->whereIn('linked_id', $type_ids)
                         ->whereNotIn('linked_id', $not_ids);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get history link of the specified resource.
     *
     * @param \App\Model\Revision $history
     *
     * @return string
     */
    public function historyLink($history)
    {
        return "<a href='{$this->linked->show_route}' class='like-txt'>
                   <span class='icon fa fa-user' data-toggle='tooltip' data-placement='top' title='User'></span> " .
                   $this->linked->name .
               '</a>';
    }

    /**
     * Get to know the user is admin typed user or not.
     *
     * @return bool
     */
    public function getAdminAttribute()
    {
        return $this->linked_type == 'staff';
    }

    /**
     * Pluck user id by user type and ids.
     *
     * @param string $user_type
     * @param array  $user_type_ids
     * @param array  $not_ids
     *
     * @return array
     */
    public static function pluckTypeId($user_type, $user_type_ids, $not_ids = [0])
    {
        if (count($user_type_ids)) {
            return self::typeInIds($user_type, $user_type_ids, $not_ids)->pluck('id')->toArray();
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A polymorphic, inverse one-to-many relationship with Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Polymorphic one-to-many relationship with Revision.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    /**
     * A many-to-many relationship with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * A one-to-many relationship with Revision.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany(Revision::class)->withTrashed();
    }
}
