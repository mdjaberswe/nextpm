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

class ChatRoomMember extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_room_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['chat_room_id', 'linked_id', 'linked_type'];

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
    protected $casts = ['chat_room_id' => 'integer'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with ChatRoom.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    /**
     * A one-to-many relationship with ChatSender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentMessages()
    {
        return $this->hasMany(ChatSender::class, 'chat_room_member_id');
    }

    /**
     * A one-to-many relationship with ChatReceiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedMessages()
    {
        return $this->hasMany(ChatReceiver::class, 'chat_room_member_id');
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
