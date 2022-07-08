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

class ChatReceiver extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_receivers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['chat_sender_id', 'chat_room_member_id', 'read_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'read_at'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * The query for getting all resource data associated with the auth user.
     *
     * @param \Illuminate\Database\Eloquent\Model $query
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeAuthStaffOnly($query)
    {
        $query->join('chat_room_members', 'chat_room_members.id', '=', 'chat_receivers.chat_room_member_id')
              ->where('chat_room_members.linked_type', 'staff')
              ->where('chat_room_members.linked_id', auth_staff()->id)
              ->select('chat_receivers.*');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with ChatSender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(ChatSender::class, 'chat_sender_id');
    }

    /**
     * An inverse one-to-many relationship with ChatRoomMember.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiverInfo()
    {
        return $this->belongsTo(ChatRoomMember::class, 'chat_room_member_id');
    }
}
