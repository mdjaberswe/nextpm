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

namespace App\Listeners;

use App\Models\ChatRoom;
use App\Models\ChatSender;
use App\Models\ChatReceiver;
use App\Models\ChatRoomMember;
use App\Events\UserCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\UserCreated $event
     *
     * @return void
     */
    public function handle(UserCreated $event)
    {
        // Check newly created user has gravatar associated with user email.
        if (\Gravatar::exists($event->data['email'])) {
            $gravar_url       = \Gravatar::get($event->data['email'], ['size' => 200]);
            $extension        = pathinfo($gravar_url, PATHINFO_EXTENSION);
            $extension        = explode('?', $extension);
            $extension        = $extension[0];
            $filename         = time() . '_' . str_random(10) . '_staff_' . $event->staff->id . '.' . $extension;
            $upload_directory = 'app/staffs/';
            $storage_path     = storage_path($upload_directory);

            if (! file_exists($storage_path)) {
                mkdir($storage_path, 0777, true);
            }

            // Save gravatar image
            \Image::make($gravar_url)->save(storage_path($upload_directory . $filename));
            $event->staff->image = $filename;
            $event->staff->update();
        }

        // Create chat rooms for the newly created user with the rest of the existing users.
        if (array_key_exists('staffs_id', $event->data) && count($event->data['staffs_id'])) {
            foreach ($event->data['staffs_id'] as $event->staff_id) {
                $chat_room       = new ChatRoom;
                $chat_room->name = 'staff#' . $event->staff_id . ' and ' . 'staff#' . $event->staff->id;
                $chat_room->save();

                $first_room_member               = new ChatRoomMember;
                $first_room_member->chat_room_id = $chat_room->id;
                $first_room_member->linked_id    = $event->staff_id;
                $first_room_member->linked_type  = 'staff';
                $first_room_member->save();

                $second_room_member               = new ChatRoomMember;
                $second_room_member->chat_room_id = $chat_room->id;
                $second_room_member->linked_id    = $event->staff->id;
                $second_room_member->linked_type  = 'staff';
                $second_room_member->save();

                $chat_sender                      = new ChatSender;
                $chat_sender->chat_room_member_id = $first_room_member->id;
                $chat_sender->message             = 'Hi, Congrats! You are now connected on Messenger.';
                $chat_sender->save();

                $chat_receiver                      = new ChatReceiver;
                $chat_receiver->chat_sender_id      = $chat_sender->id;
                $chat_receiver->chat_room_member_id = $second_room_member->id;
                $chat_receiver->save();
            }
        }
    }
}
