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

use Illuminate\Database\Seeder;
use App\Models\ChatRoom;
use App\Models\ChatSender;
use App\Models\ChatReceiver;
use App\Models\ChatRoomMember;

class ChatRoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ChatRoom::truncate();
        ChatSender::truncate();
        ChatReceiver::truncate();
        ChatRoomMember::truncate();

        $staffs    = \App\Models\Staff::get();
        $save_date = date('Y-m-d H:i:s');
        $rooms     = [];
        $members   = [];
        $senders   = [];
        $receivers = [];
        $i         = 1;

        foreach ($staffs as $staff) {
            $rest_staffs = \App\Models\Staff::where('id', '>', $staff->id)->get();

            if ($rest_staffs->count()) {
                foreach ($rest_staffs as $chat_staff) {
                    $room_name   = 'Staff#' . $staff->id . ' and ' . 'Staff#' . $chat_staff->id;
                    $rooms[]     = ['name' => $room_name, 'created_at' => $save_date, 'updated_at' => $save_date];
                    $members[]   = ['chat_room_id' => $i, 'linked_id' => $staff->id, 'linked_type' => 'staff', 'created_at' => $save_date, 'updated_at' => $save_date];
                    $members[]   = ['chat_room_id' => $i, 'linked_id' => $chat_staff->id, 'linked_type' => 'staff', 'created_at' => $save_date, 'updated_at' => $save_date];
                    $senders[]   = ['chat_room_member_id' => (($i * 2) - 1), 'message' => 'Hi! How are you?', 'created_at' => $save_date, 'updated_at' => $save_date];
                    $senders[]   = ['chat_room_member_id' => ($i * 2), 'message' => 'fine and you?', 'created_at' => $save_date, 'updated_at' => $save_date];
                    $receivers[] = ['chat_sender_id' => (($i * 2) - 1), 'chat_room_member_id' => ($i * 2), 'created_at' => $save_date, 'updated_at' => $save_date];
                    $receivers[] = ['chat_sender_id' => ($i * 2), 'chat_room_member_id' => (($i * 2) - 1), 'created_at' => $save_date, 'updated_at' => $save_date];
                    $i++;
                }
            }
        }

        ChatRoom::insert($rooms);
        ChatRoomMember::insert($members);
        ChatSender::insert($senders);
        ChatReceiver::insert($receivers);
    }
}
