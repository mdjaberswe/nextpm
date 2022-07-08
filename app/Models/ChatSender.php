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

class ChatSender extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_senders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['chat_room_member_id', 'message', 'announcement'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['position', 'opposite_position', 'can_open'];

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
     * Announcement validation.
     *
     * @param array $data
     *
     * @return Illuminate\Validation\Validator
     */
    public static function announcementValidate($data)
    {
        $rules = [
            'active_chatroom_id' => 'required|exists:chat_rooms,id,deleted_at,NULL',
            'send_to_condition'  => 'required|in:all,equal,not_equal',
            'message'            => 'required|max:65535',
        ];

        if (array_key_exists('send_to_condition', $data) && $data['send_to_condition'] != 'all') {
            $rules['send_to'] = 'required|array|exists:users,linked_id,linked_type,staff,status,1,deleted_at,NULL';
        }

        return validator($data, $rules);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the user message position.
     *
     * @return string
     */
    public function getPositionAttribute()
    {
        $outcome = 'left';

        if ($this->senderInfo->linked_id == auth()->user()->linked_id
            && $this->senderInfo->linked_type == auth()->user()->linked_type
        ) {
            $outcome = 'right';
        }

        return $outcome;
    }

    /**
     * Get the chat room partner message position.
     *
     * @return string
     */
    public function getOppositePositionAttribute()
    {
        $outcome = 'right';

        if ($this->position == 'right') {
            $outcome = 'left';
        }

        return $outcome;
    }

    /**
     * Get the prev message.
     *
     * @return \App\Models\ChatSender
     */
    public function getPrevAttribute()
    {
        return self::whereIn('chat_room_member_id', $this->chat_room_members)
                   ->where('id', '<', $this->id)
                   ->orderBy('id', 'desc')
                   ->first();
    }

    /**
     * Get the next message.
     *
     * @return \App\Models\ChatSender
     */
    public function getNextAttribute()
    {
        return self::whereIn('chat_room_member_id', $this->chat_room_members)
                   ->where('id', '>', $this->id)
                   ->orderBy('id')
                   ->first();
    }

    /**
     * Get to know the next message that has files or not.
     *
     * @return bool
     */
    public function getNextHasFilesAttribute()
    {
        return isset($this->next) && $this->next->attachfiles->count();
    }

    /**
     * Get the next message that has center date status.
     *
     * @return bool
     */
    public function getNextHasCenterDateAttribute()
    {
        return isset($this->next) && ! empty($this->next->center_date);
    }

    /**
     * Get message HTML.
     *
     * @return string
     */
    public function getMessageHtmlAttribute()
    {
        return $this->open_msg . $this->child_msg . $this->close_msg;
    }

    /**
     * Get child message HTML.
     *
     * @return string
     */
    public function getChildMsgAttribute()
    {
        // Consider announcement or normal message, attach files, center date divider.
        $strong               = $this->announcement ? 'strong' : '';
        $attach_css           = $this->attachfiles->count() ? 'attachfiles' : '';
        $next_attach_css      = $this->next_has_files ? 'nextfiles' : '';
        $tooltip              = "data-toggle='tooltip' data-placement='{$this->opposite_position}'
                                 title='" . fill_up_space($this->readable_created_full_ampm) . "'";
        $center_date_css      = ! empty($this->center_date) ? 'centerdate' : '';
        $next_center_date_css = $this->next_has_center_date ? 'nextcenterdate' : '';

        $html = "<div class='full $attach_css $next_attach_css'>" . $this->center_date . "
                    <p class='$strong $center_date_css $next_center_date_css' $tooltip messageid='{$this->id}'>"
                        . emoji($this->message) . '
                    </p>
                </div>';

        // If it has attached files then render all attach files according to file types.
        if ($this->attachfiles->count()) {
            $last_id = $this->attachfiles->last()->id;

            foreach ($this->attachfiles as $key => $file) {
                $image_css = $file->is_image ? 'chat-attach-image' : '';
                $last_css  = ($file->id == $last_id) ? 'lastfile' : '';

                $html.= "<div class='full $image_css $last_css'>
                            <p messageid='{$this->id}'>"
                                . $file->getChatThumbHtmlAttribute($tooltip) . $file->download_link . '
                            </p>
                        </div>';
            }
        }

        return $html;
    }

    /**
     * Can this message open up a new group of messages? Or not.
     *
     * @return bool
     */
    public function getCanOpenAttribute()
    {
        return is_null($this->prev) || (! is_null($this->prev) && $this->prev->position != $this->position);
    }

    /**
     * Get open message HTML.
     *
     * @return string
     */
    public function getOpenMsgAttribute()
    {
        $open = '';

        if ($this->can_open) {
            $open = "<div class='full chat-message {$this->position}'>
                        <img src='{$this->avatar}' class='avt'>
                        <div class='msg-content'>";
        }

        return $open;
    }

    /**
     * Get close message HTML.
     *
     * @return string
     */
    public function getCloseMsgAttribute()
    {
        $close = '';

        if (is_null($this->next) || (! is_null($this->next) && $this->next->position != $this->position)) {
            $close = '</div></div>';
        }

        return $close;
    }

    /**
     * Get messages divider center date HTML.
     *
     * @return string
     */
    public function getCenterDateAttribute()
    {
        $center_date = '';

        // If it doesn't have any previous message
        // Or, It has the previous message and the previous message was more than 1 hour ago
        // Or, It has the previous message and the previous message is not the same date.
        if (is_null($this->prev)
            || (! is_null($this->prev) && $this->created_at->diffInMinutes($this->prev->created_at) > 60)
            || (! is_null($this->prev) && $this->created_at->format('Y-m-d') != $this->prev->created_at->format('Y-m-d'))
        ) {
            $center_date = "<span class='msg-center-day'>{$this->created_short_day}</span>";
        }

        return $center_date;
    }

    /**
     * Get the chat sender avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->senderInfo->linked->avatar;
    }

    /**
     * Get associated chat room id.
     *
     * @return int
     */
    public function getChatRoomIdAttribute()
    {
        return $this->senderInfo->chat_room_id;
    }

    /**
     * Get associated chat room member ids array.
     *
     * @return array
     */
    public function getChatRoomMembersAttribute()
    {
        if (isset($this->senderInfo)) {
            return $this->senderInfo->room->members->pluck('id')->toArray();
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with ChatRoomMember.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function senderInfo()
    {
        return $this->belongsTo(ChatRoomMember::class, 'chat_room_member_id');
    }

    /**
     * A one-to-many relationship with ChatReceiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivers()
    {
        return $this->hasMany(ChatReceiver::class, 'chat_sender_id');
    }

    /**
     * Polymorphic one-to-many relationship with AttachFile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachfiles()
    {
        return $this->morphMany(AttachFile::class, 'linked');
    }
}
