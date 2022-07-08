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

$(document).ready(function () {
    // Click event: Toggle side nav status expand|compress
    $('.menu-toggler').on('click', function () {
        var isCompress = 0;

        if ($('nav').hasClass('compress')) {
            isCompress = 1;
        }

        var data = { is_compress: isCompress };

        // globalVar defined in partials/footer.blade.php
        $.ajax({
            type : 'GET',
            data : data,
            url  : globalVar.baseUrl + '/set-sidenav-status'
        });
    });

    // Ajax request to update read status of all notifications when clicking the top notification bell
    $('#top-notification').on('click', function () {
        $.ajax({
            type     : 'POST',
            url      : globalVar.baseAdminUrl + '/notification-read',
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    $('#top-notification').find('.notification-signal').hide();
                    $('#top-notification').closest('div').find('.scroll-dropdown').animate({ scrollTop: 0 });
                    $('#top-notification').closest('div').find('.scroll-dropdown .unread.read').removeClass('unread');
                    $('#top-notification').closest('div').find('.scroll-dropdown .unread').addClass('read');
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler function defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    });

    // Ajax request to update the status "read" of all messages when clicking the top message icon
    $('#top-msg-notification').on('click', function () {
        $.ajax({
            type     : 'POST',
            url      : globalVar.baseAdminUrl + '/message-read',
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    $('#top-msg-notification').find('.notification-signal').hide();
                    $('#top-msg-notification').closest('div').find('.scroll-dropdown').animate({ scrollTop: 0 });
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    });

    var recentRealtimeAjax, recentAutoRefresh;
    recentRealtimeAjax = recentAutoRefresh = parseInt(moment().unix(), 10);

    // Get notification
    var getNotification = function () {
        var chatroom       = 0;
        var onlineChatRoom = [];
        var activeChatroom = null;
        var lastReceivedId = null;
        var unreadNotificationCount = $('#top-notification .notification-signal').get(0) ? parseInt($('#top-notification .notification-signal').text(), 10) : 0;
        var lastNotificationId      = $('#top-notification').closest('div').find('.scroll-dropdown li:first-child');
        lastNotificationId          = lastNotificationId.get(0) && typeof lastNotificationId.attr('data-id') !== 'undefined' ? lastNotificationId.attr('data-id') : null;

        // moment defined in plugins/moment
        var currentTimestamp    = parseInt(moment().unix(), 10);
        var realtimeInterval    = currentTimestamp - recentRealtimeAjax;
        var autoRefreshInterval = currentTimestamp - recentAutoRefresh;
        var realtimeAjax        = false;

        // If the user opened the messenger page
        if ($('#chat-room').get(0)) {
            chatroom       = 1;
            activeChatroom = $('#room').val();
            lastReceivedId = $('#chat-message-box .left:last .full:last p').attr('messageid');
            onlineChatRoom = $('#chat-room-member .navlist-item.on').map(function () {
                return parseInt($(this).attr('chatroomid'), 10);
            }).get();
        }

        // If the user gets a message recently, then the next 1 min 30 sec continuously send realtime ajax request
        if ($('#top-msg-list li:first-child').get(0)) {
            var lastMsgTime = $('#top-msg-list li:first-child').attr('data-time');

            if (typeof lastMsgTime !== 'undefined' &&
                (currentTimestamp - parseInt(lastMsgTime, 10)) < 90) {
                realtimeAjax = true;
            }
        }

        // If the user gets a notification recently, then the next 1 min continuously send realtime ajax request
        if ($('#top-notification-list li:first-child').get(0)) {
            var lastNotificationTime = $('#top-notification-list li:first-child').attr('data-time');

            if (typeof lastNotificationTime !== 'undefined' &&
                (currentTimestamp - parseInt(lastNotificationTime, 10)) < 60) {
                realtimeAjax = true;
            }
        }

        // If last realtime ajax request over 1 min 30 sec Or, in chat room page over 30 sec
        // then send new realtime ajax request
        if (realtimeInterval > 90 || ($('#chat-room').get(0) && realtimeInterval > 30)) {
            realtimeAjax = true;
        }

        if (realtimeAjax === true) {
            recentRealtimeAjax = parseInt(moment().unix(), 10);
            Offline.check();

            $.ajax({
                type : 'POST',
                url  : globalVar.baseAdminUrl + '/realtime-notification',
                data : {
                    chatroom                : chatroom,
                    activechatroom          : activeChatroom,
                    onlineChatRoom          : onlineChatRoom,
                    lastreceivedid          : lastReceivedId,
                    unreadNotificationCount : unreadNotificationCount,
                    lastNotificationId      : lastNotificationId
                },
                dataType : 'JSON',
                success  : function (data) {
                    if (data.alertMsg !== null) {
                        $.notify({ message: data.alertMsg.msg }, defaultNotifyConfig(data.alertMsg.type, { delay: 7500 }));
                    }

                    if (data.reload === true) {
                        location.reload();
                    }

                    if (data.status === true) {
                        $('#view-all-msg').attr('href', data.activeChatroomUrl);

                        // If unread messages are found then update unread messages no and play the notification sound
                        if (data.unreadMessageCount > 0) {
                            var msgNotificationSignal = $('#top-msg-notification').find('.notification-signal');
                            var playSound = false;

                            if (msgNotificationSignal.length === 0) {
                                $('#top-msg-notification').append("<p class='notification-signal bg-a'>" + data.unreadMessageCount + '</p>');
                                playSound = true;
                            } else {
                                playSound = parseInt(data.unreadMessageCount, 10) > parseInt(msgNotificationSignal.text(), 10) ||
                                            $('#top-msg-notification').find('.notification-signal:visible').length === 0;
                                msgNotificationSignal.html(data.unreadMessageCount);
                                msgNotificationSignal.show();
                            }

                            if (playSound && $('#top-msg-notification').attr('data-sound') === 'on') {
                                $('#notification-sound')[0].play();
                            }
                        }

                        // If unread notifications are found then update unread notifications no and play the notification sound
                        if (data.unreadNotificationCount > 0) {
                            var notificationSignal = $('#top-notification').find('.notification-signal');

                            if (notificationSignal.length === 0) {
                                $('#top-notification').append("<p class='notification-signal bg-a'>" + data.unreadNotificationCount + '</p>');
                            } else {
                                notificationSignal.html(data.unreadNotificationCount);
                                notificationSignal.show();
                            }
                        }

                        // Render new notifications HTML in dropdown list
                        if (data.newNoficationsHtml.length > 0) {
                            $('#top-notification').closest('div').find('li.emptylist').remove();
                            $('#top-notification').closest('div').find('.bottom-link').html('View all notifications');

                            $(data.newNoficationsHtml).each(function (index, value) {
                                if (!$('#top-notification').closest('div').find(".scroll-dropdown li[data-id='" + value.id + "']").get(0)) {
                                    $('#top-notification').closest('div').find('.scroll-dropdown').prepend(value.html);
                                }
                            });
                        }

                        // Render new messages HTML in dropdown list
                        if (data.chatMessagesHtml !== '') {
                            if ($('#top-msg-list').get(0)) {
                                $('#top-msg-list ul').html(data.chatMessagesHtml);
                            }
                        }

                        // If the messenger page is active, then update and render the newest message
                        if ($('#chat-room').get(0)) {
                            if (data.chatRoomsHtml !== '') {
                                $('.navlist').html(data.chatRoomsHtml);
                                $('[data-toggle="tooltip"]').tooltip();
                            }

                            var afterlastReceivedId = $('#chat-message-box .left:last .full:last p').attr('messageid');

                            if (data.chatRoom === 1 &&
                                data.activeChatRoom === $('#room').val() &&
                                data.lastReceivedId === afterlastReceivedId &&
                                data.messageHtml !== '' &&
                                data.messageChildHtml !== '' &&
                                data.asideChatroomHtml !== ''
                            ) {
                                var lastChild = $('#chat-message-box .chat-message:last');

                                if (lastChild.hasClass('left')) {
                                    lastChild.find('.msg-content').append(data.messageChildHtml);
                                } else {
                                    $('#chat-message-box').append(data.messageHtml);
                                }

                                $(".navlist-item[chatroomid='" + data.activeChatRoom + "'").parent('li').remove();
                                $('.navlist').prepend(data.asideChatroomHtml);
                                $('[data-toggle="tooltip"]').tooltip();
                                $('#chat-message-box').scrollTop($('#chat-message-box').prop('scrollHeight'));
                            }
                        }

                        $('[data-toggle="tooltip"]').tooltip();
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler function defined in js/app.js
                    // ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                }
            });
        }

        // Ajax request to update the dashboard report page
        if ($('.auto-refresh').get(0) &&
            typeof $('.auto-refresh').attr('data-refresh') !== 'undefined' &&
            typeof $('.auto-refresh').attr('data-interval') !== 'undefined' &&
            $.isNumeric($('.auto-refresh').attr('data-refresh')) &&
            $.isNumeric($('.auto-refresh').attr('data-interval'))
        ) {
            var nextRefresh = parseInt($('.auto-refresh').attr('data-refresh'), 10);
            var minInterval = parseInt($('.auto-refresh').attr('data-interval'), 10) * 55;

            if (nextRefresh < currentTimestamp && autoRefreshInterval > minInterval) {
                recentAutoRefresh = parseInt(moment().unix(), 10);
                // ajaxAutoRefresh function defined in js/app.js
                ajaxAutoRefresh();
            }
        }
    };

    // Get notification update every 5sec
    setInterval(getNotification, 5000);

    $('#add-new-btn').on('click', function () {
        if (globalVar.defaultDropdown.length > 0) {
            $.each(globalVar.defaultDropdown, function (index, item) {
                $(item.identifier).val(item.default).trigger('change');
            });
        }
    });
});
