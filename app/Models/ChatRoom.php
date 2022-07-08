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

class ChatRoom extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'type'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['meaningful_name', 'inactive', 'is_online'];

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
     * Chat room validation.
     *
     * @param array $data
     *
     * @return Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        return validator($data, [
            'room'    => 'required|exists:chat_rooms,id,deleted_at,NULL',
            'message' => 'required|max:65535',
        ]);
    }

    /**
     * Load history data validation.
     *
     * @param array $data
     *
     * @return Illuminate\Validation\Validator
     */
    public static function historyValidate($data)
    {
        return validator($data, [
            'room' => 'required|exists:chat_rooms,id,deleted_at,NULL',
            'id'   => 'required|exists:chat_senders,id,deleted_at,NULL',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the meaningful name of the specified resource.
     *
     * @param bool $name_link
     *
     * @return string
     */
    public function getMeaningfulNameAttribute($name_link = false)
    {
        // If it is a shared chat room then return its plain name.
        if ($this->type == 'shared') {
            return $this->name;
        }

        // Check the auth user is in this chat room.
        $has_auth_user = $this->members->where('linked_id', auth()->user()->linked_id)
                                       ->where('linked_type', auth()->user()->linked_type)
                                       ->first();

        // If the auth user exists in this chat room then find out the other member and return meaningful name.
        if (! is_null($has_auth_user)) {
            $id = array_diff($this->members->pluck('id')->toArray(), [$has_auth_user->id]);

            if (isset($name_link) && $name_link == true) {
                return $this->members->whereIn('id', $id)->first()->linked->name_link;
            } else {
                return $this->members->whereIn('id', $id)->first()->linked->name;
            }
        }

        return $this->members->first()->linked->last_name . ' and ' . $this->members->last()->linked->last_name;
    }

    /**
     * Get the member of this chatroom associated with the auth user.
     *
     * @return \App\Models\ChatRoomMember
     */
    public function getAuthMemberAttribute()
    {
        return $this->members->where('linked_id', auth()->user()->linked_id)
                             ->where('linked_type', auth()->user()->linked_type)
                             ->first();
    }

    /**
     * Get the other member besides the auth user of the chat room.
     *
     * @return App\Models\Staff
     */
    public function getChatPartnerAttribute()
    {
        $has_auth_member = $this->auth_member;

        // If the auth user is in this chat room and this chat room is dedicated then have a partner.
        if (! is_null($has_auth_member) && $this->type == 'dedicated') {
            $id = array_diff($this->members->pluck('id')->toArray(), [$has_auth_member->id]);

            return $this->members->whereIn('id', $id)->first()->linked;
        }

        return null;
    }

    /**
     * Get the chat room partner's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        if (isset($this->chat_partner)) {
            return $this->chat_partner->avatar;
        }

        return Avatar::create($this->meaningful_name)->toBase64();
    }

    /**
     * Get the chat room partner's online status.
     *
     * @return bool
     */
    public function getIsOnlineAttribute()
    {
        return isset($this->chat_partner) && $this->chat_partner->is_online;
    }

    /**
     * Get chat room inactive status.
     *
     * @return bool
     */
    public function getInactiveAttribute()
    {
        $inactive = false;

        if (isset($this->chat_partner) && isset($this->chat_partner->user)) {
            $inactive = $this->chat_partner->status ? false : true;
        }

        return $inactive;
    }

    /**
     * Get the latest activities of the specified resource.
     *
     * @return \App\Models\ChatSender
     */
    public function getLatestActivityAttribute()
    {
        return ChatSender::join('chat_room_members', 'chat_room_members.id', '=', 'chat_senders.chat_room_member_id')
                         ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
                         ->where('chat_rooms.id', $this->id)
                         ->latest('chat_senders.id')
                         ->select(
                             'chat_rooms.id',
                             'chat_rooms.name',
                             'chat_rooms.type',
                             'chat_room_members.linked_id',
                             'chat_room_members.linked_type',
                             'chat_senders.created_at',
                             'chat_senders.message'
                         )->first();
    }

    /**
     * Get all histories of the specified resource.
     *
     * @param int|null $latest_id
     *
     * @return App\Models\ChatSender
     */
    public function allHistories($latest_id = null)
    {
        $msg = ChatSender::join('chat_room_members', 'chat_room_members.id', '=', 'chat_senders.chat_room_member_id')
                         ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
                         ->where('chat_room_members.chat_room_id', $this->id);

        if (isset($latest_id)) {
            $msg = $msg->where('chat_senders.id', '<', $latest_id);
        }

        return $msg->orderBy('chat_senders.id', 'desc')->select(
            'chat_senders.*',
            'chat_room_members.linked_id',
            'chat_room_members.linked_type'
        );
    }

    /**
     * Get chat room history.
     *
     * @param int|null $latest_id
     *
     * @return App\Models\ChatSender
     */
    public function getHistoryAttribute($latest_id = null)
    {
        return $this->allHistories($latest_id)->take($this->getRoundTake($latest_id))->get()->sortBy('id');
    }

    /**
     * Get chat room history HTML.
     *
     * @param int|null $latest_id
     *
     * @return string
     */
    public function getHistoryHtmlAttribute($latest_id = null)
    {
        $html = '';
        $histories = $this->getHistoryAttribute($latest_id);

        if (! $histories->isEmpty()) {
            foreach ($histories as $message) {
                $html .= $message->message_html;
            }
        }

        return $html;
    }

    /**
     * Get chat room history load status.
     *
     * @param int|null $latest_id
     *
     * @return bool
     */
    public function getLoadStatusAttribute($latest_id = null)
    {
        return $this->allHistories($latest_id)->count() > $this->getRoundTake($latest_id);
    }

    /**
     * Get proper round number of taking prev histories so that group of chat messages is not broken.
     *
     * @param int|null $latest_id
     *
     * @return int
     */
    public function getRoundTake($latest_id = null)
    {
        $default_take = 20;
        $add = 0;

        // If this chat room all messages count is less than default no of taken messages.
        if ($this->allHistories($latest_id)->count() <= $default_take) {
            return $default_take;
        }

        // If the latest id is null then find out where to start load messages.
        if (is_null($latest_id)) {
            $add = 1;
            $latest_id = ChatSender::join(
                'chat_room_members',
                'chat_room_members.id',
                '=',
                'chat_senders.chat_room_member_id'
            )
            ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
            ->where('chat_room_members.chat_room_id', $this->id)
            ->select('chat_senders.*')
            ->latest('id')
            ->first()
            ->id;
        }

        // Last message according to default taken no.
        $taken = ChatSender::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_senders.chat_room_member_id'
        )
        ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
        ->where('chat_room_members.chat_room_id', $this->id)
        ->where('chat_senders.id', '<', $latest_id)
        ->select('chat_senders.*')
        ->latest('id')
        ->take($default_take)
        ->get()
        ->last();

        // If the default last message can open then return round no by adding proper value.
        if ($taken->can_open) {
            return $default_take + $add;
        }

        // Get closest round id according to default taken the last message's id
        // and get proper round no for not to break a group of messages.
        $histories = ChatSender::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_senders.chat_room_member_id'
        )
        ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
        ->where('chat_room_members.chat_room_id', $this->id)
        ->where('chat_senders.id', '<=', $taken->id)
        ->orderBy('chat_senders.id', 'desc')
        ->select('chat_senders.*')
        ->pluck('id')
        ->toArray();

        $round_histories = ChatSender::whereIn('id', $histories)
                                     ->orderBy('id', 'desc')
                                     ->get()
                                     ->pluck('can_open')
                                     ->toArray();

        $round_index = array_search(true, $round_histories);
        $round_id    = $histories[$round_index];
        $round_take  = ChatSender::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_senders.chat_room_member_id'
        )
        ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
        ->where('chat_room_members.chat_room_id', $this->id)
        ->where('chat_senders.id', '<', $latest_id)
        ->where('chat_senders.id', '>=', $round_id)
        ->count();

        return $round_take + $add;
    }

    /**
     * Get chat room received message history.
     *
     * @param int|null $receivedId
     *
     * @return \App\Models\ChatSender
     */
    public function getReceivedHistoryAttribute($receivedId = null)
    {
        if (is_null($this->auth_member)) {
            return null;
        }

        $messages = ChatSender::join(
            'chat_room_members',
            'chat_room_members.id',
            '=',
            'chat_senders.chat_room_member_id'
        )
        ->join('chat_rooms', 'chat_rooms.id', '=', 'chat_room_members.chat_room_id')
        ->where('chat_room_members.chat_room_id', $this->id)
        ->where('chat_room_member_id', '!=', $this->auth_member->id)
        ->orderBy('chat_senders.id')
        ->select('chat_senders.*', 'chat_room_members.linked_id', 'chat_room_members.linked_type');

        return is_null($receivedId) ? $messages->get() : $messages->where('chat_senders.id', '>', $receivedId)->get();
    }

    /**
     * Get chat room sidebar nav HTML.
     *
     * @param int|null $last_sender
     * @param int|null $active_id
     *
     * @return string
     */
    public function getNavHtmlAttribute($last_sender = null, $active_id = null)
    {
        if (isset($active_id)) {
            $active = $active_id == $this->id ? 'active' : null;
        } else {
            $active = $this->is_active ? 'active' : null;
        }

        // Find out chat room online CSS, latest message.
        $online_css    = $this->is_online ? 'on' : 'off';
        $last_activity = is_null($last_sender) ? $this : $last_sender;
        $message       = is_null($last_sender) ? $this->message : $last_sender->message;
        $last_msg      = str_limit($message, 30);

        if ($this->is_auth_last || isset($last_sender)) {
            $last_msg = 'You: ' . str_limit($message, 25);
        }

        return "<li>
                    <a class='navlist-item $online_css $active' chatroomid='{$this->id}'>
                        <i class='status'></i>
                        <img src='{$this->avatar}' alt='{$this->meaningful_name}'>
                        <p class='time' data-toggle='tooltip' data-placement='left'
                           title='" . $last_activity->getCreatedTimeAmpmAttribute(true) . "'>" .
                           $last_activity->getCreatedShortFormatAttribute(true) . "
                        </p>
                        <h5>" . str_limit($this->meaningful_name, 17, '.') . "</h5>
                        <p>" . emoji($last_msg) . "</p>
                    </a>
                </li>";
    }

    /**
     * Get chat room notification dropdown list HTML.
     *
     * @return string
     */
    public function getDropdownListAttribute()
    {
        $last_msg = str_limit($this->message, 30);

        if ($this->is_auth_last) {
            $last_msg = 'You: ' . str_limit($this->message, 25);
        }

        return "<li data-time='" . $this->created_at->timestamp . "'>
                    <a href='" . route('admin.message.chatroom', $this->id) . "' class='dropdown-notification'>
                        <img src='{$this->avatar}' alt='{$this->meaningful_name}'>
                        <p class='time' data-toggle='tooltip' data-placement='left'
                           title='" . $this->getCreatedTimeAmpmAttribute(true) . "'>" .
                           $this->getCreatedShortFormatAttribute(true) . "
                        </p>
                        <h5>" . str_limit($this->meaningful_name, 20, '.') . "</h5>
                        <p>" . emoji($last_msg) . "</p>
                    </a>
                </li>";
    }

    /**
     * Get the chatroom is now active or not status.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        return auth_staff()->latest_chat_id == $this->id;
    }

    /**
     * Get the auth user is last message sender or not status.
     *
     * @return bool
     */
    public function getIsAuthLastAttribute()
    {
        return $this->linked_id == auth()->user()->linked_id && $this->linked_type == auth()->user()->linked_type;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A one-to-many relationship with ChatRoomMember.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members()
    {
        return $this->hasMany(ChatRoomMember::class, 'chat_room_id');
    }
}
