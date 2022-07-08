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

use License;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\ChatRoom;
use App\Models\FilterView;
use App\Models\ChatSender;
use App\Models\ChatReceiver;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminNotificationController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Page information like title, current filter, breadcrumb, and resource table format(heading, columns),
        // mark as read all unread notifications.
        $page = [
            'title'             => 'Notifications',
            'item'              => 'Notification',
            'field'             => 'notifications',
            'view'              => 'admin.notification',
            'route'             => 'admin.notification',
            'modal_create'      => false,
            'modal_edit'        => false,
            'modal_delete'      => false,
            'modal_bulk_delete' => false,
            'filter'            => true,
            'breadcrumb'        => FilterView::getBreadcrumb('notification'),
            'current_filter'    => FilterView::getCurrentFilter('notification'),
        ];

        $table = DatabaseNotification::getNotificationTableFormat();
        auth_staff()->unread_notifications->markAsRead();

        return view('admin.notification.index', compact('page', 'table'));
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function notificationData(Request $request)
    {
        // Filter by current filter parameter.
        $notification_ids = auth()->user()->notifications->pluck('id')->toArray();
        $notifications    = DatabaseNotification::whereIn('notifications.id', $notification_ids)
                                                ->filterViewData()
                                                ->latest('created_at')
                                                ->select('notifications.*')
                                                ->get();

        return DatabaseNotification::getNotificationTableData($notifications, $request);
    }

    /**
     * Notification mark as read.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function read(Request $request)
    {
        auth_staff()->unread_notifications->markAsRead();

        return response()->json(['status' => true]);
    }

    /**
     * Get real-time notification without page refresh.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function realtimeNotification(Request $request)
    {
        $alert_msg = null;
        $reload = null;

        // Check license information.
        if (session()->has('license_info')) {
            $license_info = session('license_info');
            $load_new_license = false;

            if ($license_info['warning'] == true) {
                if (auth()->user()->hasRole('administrator')) {
                    $checked_at = new Carbon($license_info['checked_at']);
                    $interval = $checked_at->diffInMinutes(now());

                    if ($interval > $license_info['interval']) {
                        if ($license_info['warn_no'] >= $license_info['max_warn'] || License::getInstalledDays() > $license_info['max_trial']) {
                            $purchase_code = config('setting.purchase_code') ? \Crypt::decrypt(config('setting.purchase_code')) : false;
                            $license_info = License::getLicenseInfo($purchase_code, 'post', ['warn_no' => $license_info['warn_no']]);
                            $reload = true;
                            $load_new_license = true;
                        } else {
                            $license_info['warn_no'] = $license_info['warn_no'] + 1;
                            $msg = 'Purchase Code: ' . session('license_info')['message'] . '. ' . "<a href='" . route('admin.administration-setting.general') . "'>Click here to setup</a>";
                            $alert_msg = ['type' => 'warning', 'msg' => $msg];
                        }

                        // Update license info.
                        $license_info['checked_at'] = now()->format('Y-m-d H:i:s');
                        session(['license_info' => $license_info]);
                        $installed = fopen(storage_path('app/installed'), 'w');
                        fwrite($installed, json_encode($license_info));
                        fclose($installed);

                        if ($load_new_license) {
                            session()->forget('license_info');
                        }
                    }
                }
            }
        } else {
            session(['license_info' => License::getInstalledInfo(), 'license_checkup' => License::regularCheckup()]);
        }

        $check_online_users = is_null($request->onlineChatRoom)
                              || (isset($request->onlineChatRoom)
                              && ($request->onlineChatRoom == auth_staff()->online_chatroom_Ids));

        // If the auth user has no new notification, recent message, and online chat room then the response is false.
        if (! auth_staff()->has_new_notification && ! auth_staff()->has_recent_sent_msg && $check_online_users) {
            return response()->json(['status' => false, 'alertMsg' => $alert_msg, 'reload' => $reload]);
        }

        $status = true;
        $new_notifications = [];
        $unread_notifications_count = auth_staff()->unread_notifications_count;

        // New notifications found if posted unread notification is less than DB unread notification count.
        if ($request->unreadNotificationCount < $unread_notifications_count) {
            $last_notification = DatabaseNotification::authOnly()->find($request->lastNotificationId);
            $last_created_at   = non_property_checker($last_notification, 'created_at');
            $new_notifications = DatabaseNotification::getNewNotificationsData($last_created_at)['html'];
        }

        // Get a minimum of 15 unread messages collection, render HTML.
        $unread_messages_count = auth_staff()->unread_messages_count;
        $take_messages         = $unread_messages_count > 15 ? $unread_messages_count : 15;
        $chat_messages         = auth_staff()->getChatRoomsAttribute($take_messages);
        $chat_messages_html    = '';

        if (count($chat_messages) > 0) {
            foreach ($chat_messages as $chat_message) {
                $chat_messages_html .= $chat_message->dropdown_list;
            }
        }

        $chat_rooms          = auth_staff()->chat_rooms;
        $active_chatroom     = auth_staff()->chat_rooms->isEmpty() ? null : auth_staff()->latest_chat_id;
        $active_chatroom_url = is_null($active_chatroom) ? route('admin.message.index') : route('admin.message.chatroom', $active_chatroom);
        $active_chatroom     = is_numeric($request->activechatroom) ? intval($request->activechatroom) : $active_chatroom;
        $chat_rooms_html     = '';

        if (count($chat_rooms) && ! is_null($active_chatroom)) {
            foreach ($chat_rooms as $chat_room) {
                $chat_rooms_html .= $chat_room->getNavHtmlAttribute(null, $active_chatroom);
            }
        }

        $notification_message_list = null;
        $chatroom_messsage         = null;
        $message_html              = '';
        $message_child_html        = '';
        $aside_chatroom            = '';

        // Chat room console in real-time.
        if ($request->chatroom == true
            && is_numeric($request->activechatroom)
            && is_numeric($request->lastreceivedid)
        ) {
            $chat_room_id     = intval($request->activechatroom);
            $last_received_id = intval($request->lastreceivedid);
            $auth_chat_room   = in_array($chat_room_id, auth_staff()->chat_rooms->pluck('id')->toArray());

            // If the auth user's chatroom then realtime response with render HTML data.
            if ($auth_chat_room) {
                $chat_room = ChatRoom::find($chat_room_id);
                $received_history = $chat_room->getReceivedHistoryAttribute($last_received_id);

                if (! $received_history->isEmpty()) {
                    $message_html = "<div class='full chat-message left'>
                                        <img src='" . $chat_room->avatar . "' class='avt'>
                                        <div class='msg-content'>";

                    foreach ($received_history as $single_history) {
                        $message_child_html .= $single_history->child_msg;
                    }

                    $message_html  .= $message_child_html;
                    $message_html  .= "</div></div>";
                    $aside_chatroom = $chat_room->getNavHtmlAttribute($received_history->last(), $chat_room_id);
                }
            }
        }

        $data = [
            'status'                  => true,
            'unreadNotificationCount' => $unread_notifications_count,
            'newNoficationsHtml'      => $new_notifications,
            'unreadMessageCount'      => $unread_messages_count,
            'chatMessagesHtml'        => $chat_messages_html,
            'activeChatroomUrl'       => $active_chatroom_url,
            'chatRoomsHtml'           => $chat_rooms_html,
            'chatRoom'                => (int) $request->chatroom,
            'activeChatRoom'          => $request->activechatroom,
            'lastReceivedId'          => $request->lastreceivedid,
            'messageHtml'             => $message_html,
            'messageChildHtml'        => $message_child_html,
            'asideChatroomHtml'       => $aside_chatroom,
            'alertMsg'                => $alert_msg,
            'reload'                  => $reload,
        ];

        return response()->json($data);
    }
}
