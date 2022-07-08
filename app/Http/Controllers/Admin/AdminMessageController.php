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

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\ChatRoom;
use App\Models\ChatSender;
use App\Models\ChatReceiver;
use App\Models\ChatRoomMember;
use App\Jobs\SaveUploadedFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminMessageController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setUploadDirectoryLocation('chat_sender');
    }

    /**
     * Display a listing of the chat rooms.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = ['title' => 'Messenger'];
        $data = [
            'chat_rooms'      => auth_staff()->chat_rooms,
            'active_chatroom' => auth_staff()->chat_rooms->isEmpty()
                                 ? null : ChatRoom::find(auth_staff()->latest_chat_id),
        ];

        return view('admin.message.index', compact('page', 'data'));
    }

    /**
     * Display the specified chat room resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ChatRoom     $chatroom
     *
     * @return \Illuminate\Http\Response
     */
    public function chatroom(Request $request, ChatRoom $chatroom)
    {
        // If the auth user is not associated with the chat room then redirect to the index page.
        if (! in_array($chatroom->id, auth_staff()->chat_rooms->pluck('id')->toArray())) {
            return redirect()->route('admin.message.index');
        }

        $page = ['title' => 'Messenger'];
        $data = ['chat_rooms' => auth_staff()->chat_rooms, 'active_chatroom' => $chatroom];

        return view('admin.message.index', compact('page', 'data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status              = true;
        $errors              = null;
        $message_html        = '';
        $message_child_html  = '';
        $aside_chatroom_html = '';
        $data                = $request->all();

        // If the message has attached files.
        if ($request->has('uploaded_files') && null_or_empty($request->message)) {
            $files_count     = count($request->uploaded_files);
            $data['message'] = 'Attached ' . $files_count . ' ' . str_plural('file', $files_count);
        }

        $validation     = ChatRoom::validate($data);
        $chat_room_id   = intval($request->room);
        $chat_room      = ChatRoom::find($chat_room_id);
        $auth_chat_room = in_array($chat_room_id, auth_staff()->chat_rooms->pluck('id')->toArray());

        // If validation passes then send the message.
        if ($validation->passes() && $auth_chat_room && isset($chat_room) && ! $chat_room->inactive) {
            $chat_room_member_id   = auth_staff()->chatRoomMembers
                                                 ->where('chat_room_id', $chat_room_id)
                                                 ->first()
                                                 ->id;

            $rest_members_id_array = array_diff(
                ChatRoom::find($chat_room_id)->members->pluck('id')->toArray(),
                [$chat_room_member_id]
            );

            $chat_sender = new ChatSender;
            $chat_sender->message = $data['message'];
            $chat_sender->chat_room_member_id = $chat_room_member_id;
            $chat_sender->save();

            foreach ($rest_members_id_array as $rest_member) {
                $chat_receiver = new ChatReceiver;
                $chat_receiver->chat_sender_id = $chat_sender->id;
                $chat_receiver->chat_room_member_id = $rest_member;
                $chat_receiver->save();
            }

            dispatch(new SaveUploadedFile(
                $request->uploaded_files,
                'chat_sender',
                $chat_sender->id,
                $this->directory,
                $this->location
            ));

            // Realtime update HTML content by ajax response.
            $message_html        = $chat_sender->message_html;
            $message_child_html  = $chat_sender->child_msg;
            $aside_chatroom_html = $chat_room->getNavHtmlAttribute($chat_sender);
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json([
            'status'           => $status,
            'errors'           => $errors,
            'messagehtml'      => $message_html,
            'messagechildhtml' => $message_child_html,
            'chatroomhtml'     => $aside_chatroom_html,
        ]);
    }

    /**
     * Send an announcement to users.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function announcementStore(Request $request)
    {
        $validation = ChatSender::announcementValidate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $chat_rooms_id      = auth_staff()->chat_rooms_id;
            $active_chatroom_id = (int) $request->active_chatroom_id;
            $active_chat_sender = null;

            // Get selected users by 'sending condition' value.
            if ($request->send_to_condition == 'equal') {
                $chat_rooms_id = ChatRoom::join(
                    'chat_room_members',
                    'chat_room_members.chat_room_id',
                    '=',
                    'chat_rooms.id'
                )
                ->whereIn('chat_room_id', $chat_rooms_id)
                ->where('linked_type', 'staff')
                ->whereIn('linked_id', $request->send_to)
                ->groupBy('chat_rooms.id')
                ->orderBy('chat_rooms.id', 'desc')
                ->pluck('chat_rooms.id')
                ->toArray();
            } elseif ($request->send_to_condition == 'not_equal') {
                $chat_rooms_id = ChatRoomMember::where('linked_type', 'staff')
                                               ->whereNot('linked_id', auth_staff()->id)
                                               ->whereNotIn('linked_id', $request->send_to)
                                               ->whereIn('chat_room_id', $chat_rooms_id)
                                               ->groupBy('chat_room_id')
                                               ->orderBy('chat_room_id', 'desc')
                                               ->pluck('chat_room_id')
                                               ->toArray();
            }

            // Place top position of active chat room id.
            if (in_array($active_chatroom_id, $chat_rooms_id)) {
                array_unshift($chat_rooms_id, $active_chatroom_id);
                $chat_rooms_id = array_unique($chat_rooms_id);
            }

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true, 'rooms' => $chat_rooms_id,'announce' => true]);

            // Send an announcement to receivers.
            if (count($chat_rooms_id)) {
                foreach ($chat_rooms_id as $chat_room_id) {
                    $chat_room_member_id   = auth_staff()->chatRoomMembers
                                                         ->where('chat_room_id', (int) $chat_room_id)
                                                         ->first()
                                                         ->id;

                    $rest_members_id_array = array_diff(
                        ChatRoom::find($chat_room_id)->members->pluck('id')->toArray(),
                        [$chat_room_member_id]
                    );

                    $chat_sender = new ChatSender;
                    $chat_sender->message = $request->message;
                    $chat_sender->chat_room_member_id = $chat_room_member_id;
                    $chat_sender->announcement = 1;
                    $chat_sender->save();

                    foreach ($rest_members_id_array as $rest_member) {
                        $chat_receiver = new ChatReceiver;
                        $chat_receiver->chat_sender_id = $chat_sender->id;
                        $chat_receiver->chat_room_member_id = $rest_member;
                        $chat_receiver->save();
                    }

                    if ($active_chatroom_id == $chat_room_id) {
                        $active_chat_sender = $chat_sender;
                    }
                }
            }

            if ($active_chat_sender !== null) {
                // Place top active chat sender room.
                $active_chat_sender->updated_at = $chat_sender->updated_at->addSeconds(1)->format('Y-m-d H:i:s');
                $active_chat_sender->save();
            }
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource of chat room initial history.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function chatroomHistory(Request $request)
    {
        if ($request->ajax()) {
            $status        = true;
            $load          = true;
            $info          = null;
            $history_html  = null;
            $chatroom_name = null;
            $title         = null;
            $inactive      = null;
            $inactive_txt  = null;
            $chatroom      = isset($request->id) ? ChatRoom::find($request->id) : null;

            // If the chat room exists and is associated with the auth user then load chat room history.
            if (isset($chatroom)) {
                $auth_chat_room = in_array($chatroom->id, auth_staff()->chat_rooms->pluck('id')->toArray());

                if ($auth_chat_room) {
                    $info            = $chatroom;
                    $history_html    = $chatroom->history_html;
                    $load            = $chatroom->load_status;
                    $chatroom_name   = $chatroom->getMeaningfulNameAttribute(true);
                    $chatroom_avatar = "<img src='" . $chatroom->avatar . "' alt='" . $chatroom->meaningful_name . "'>";
                    $title           = is_null($chatroom->chat_partner) ? null : $chatroom->chat_partner->title;
                    $inactive        = $chatroom->inactive;
                    $inactive_msg    = $chatroom->inactive
                                       ? 'You can not reply to this conversation' : 'Type a message...';
                } else {
                    $status = false;
                }
            } else {
                $status = false;
            }

            return response()->json([
                'status'      => $status,
                'name'        => $chatroom_name,
                'title'       => $title,
                'avatar'      => $chatroom_avatar,
                'inactive'    => $inactive,
                'inactivemsg' => $inactive_msg,
                'history'     => $history_html,
                'load'        => $load,
            ]);
        }

        return redirect()->route('admin.message.index');
    }

    /**
     * Load the specified resource of chat room history.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ChatRoom     $chatroom
     *
     * @return \Illuminate\Http\Response
     */
    public function historyData(Request $request, ChatRoom $chatroom)
    {
        $data   = $request->all();
        $html   = null;
        $load   = false;
        $status = false;
        $errors = [];

        // If the chat room is valid then get the requested history and next load status.
        if (isset($request->room) && $request->room == $chatroom->id && isset($request->id)) {
            $validation = ChatRoom::historyValidate($data);

            if ($validation->passes()) {
                $html   = $chatroom->getHistoryHtmlAttribute($request->id);
                $load   = $chatroom->getLoadStatusAttribute($request->id);
                $status = true;
            } else {
                $messages = $validation->getMessageBag()->toArray();

                foreach ($messages as $msg) {
                    $errors[] = $msg;
                }
            }
        }

        return response()->json([
            'status' => $status,
            'errors' => $errors,
            'html'   => $html,
            'load'   => $load,
        ]);
    }

    /**
     * Update unread message status to read|seen.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function read(Request $request)
    {
        $unread_id_array = ChatReceiver::authStaffOnly()->whereNull('read_at')->pluck('id')->toArray();
        ChatReceiver::whereIn('id', $unread_id_array)->update(['read_at' => Carbon::now()]);

        return response()->json(['status' => true]);
    }
}
