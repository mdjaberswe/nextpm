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
    // adjustChatBoxHeight function defined in js/app.js
    adjustChatBoxHeight();
    $('.navlist').scrollTop(0);

    // Messages box scroll event bind with ajax request to load message history
    setTimeout(function () {
        $('#chat-message-box').scrollTop($('#chat-message-box').prop('scrollHeight'));

        $('.message-box').each(function (index, ui) {
            this.addEventListener('ps-y-reach-end', function () {
                $('#chat-message .content-loader.history').hide();
            });

            this.addEventListener('ps-y-reach-start', function () {
                ajaxChatHistory($(this));
            });

            this.addEventListener('ps-scroll-up', function () {
                $('#chat-message .content-loader.history').hide();
            });

            this.addEventListener('ps-scroll-down', function () {
                $('#chat-message .content-loader.history').hide();
            });
        });
    }, 500);

    // Filter searching for chat room members
    $('#search-room-member').on('keyup', function () {
        var searchVal = $(this).val().toLowerCase();

        if (searchVal === '') {
            $('.navlist-item').show();
        } else {
            $('#chat-room-member .navlist').scrollTop(0);

            $('.navlist-item').each(function () {
                var filter = $(this).find('h5').html().toLowerCase().indexOf(searchVal);

                if (filter > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    // Emoji box toggle open|close click event
    $('.msg-emoji').on('click', function (e) {
        e.preventDefault();

        if ($('.jemoji-menu').css('display') === 'block') {
            $('.emoji').jemoji('close');
        } else {
            if ($('.jemoji-icons').find('img').size() === 0 && typeof globalVar.jemojiPs !== 'undefined') {
                globalVar.jemojiPs.destroy();
                globalVar.jemojiPs = null;
            }

            $('.emoji').jemoji('open');

            if (globalVar.jemojiPs === null) {
                globalVar.jemojiPs = new PerfectScrollbar('.jemoji-icons', {
                    wheelSpeed         : 2,
                    wheelPropagation   : true,
                    minScrollbarLength : 30
                });
            }
        }
    });

    // If the user press enter then send a message
    $('#send-msg').on('keydown', function (e) {
        if ((e.keyCode === 13 || e.which === 13) && e.shiftKey === false) {
            e.preventDefault();
            $('.emoji').jemoji('close');
            var form = $($(this).closest('.msg-form-box'));
            sendChatMessage(form);
        }
    });

    // If the user click "Send" button then send a message
    $('.send-msg-btn').on('click', function (e) {
        e.preventDefault();
        $('.emoji').jemoji('close');
        var form = $($(this).closest('.msg-form-box'));
        sendChatMessage(form);
    });

    // Ajax request to update the auth user setting to turn on|off notification sound
    $('.chat-sound-btn').on('click', function (e) {
        var soundBtn      = $(this);
        var currentStatus = $('#top-msg-notification').attr('data-sound');
        var keyVal        = currentStatus === 'on' ? 'off' : 'on';

        $.ajax({
            type     : 'POST',
            url      : globalVar.baseAdminUrl + '/user-setting',
            data     : { key: 'chat_sound', value: keyVal },
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    if (currentStatus === 'on') {
                        $('#top-msg-notification').attr('data-sound', 'off');
                        soundBtn.find('i').attr('class', 'mdi mdi-volume-off lg');
                        // globalVar defined in partials/footer.blade.php
                        singleNotify('Chat sound is now disabled', 'danger', globalVar.dangerNotify, false);
                    } else {
                        $('#top-msg-notification').attr('data-sound', 'on');
                        soundBtn.find('i').attr('class', 'mdi mdi-volume-high lg');
                        singleNotify('Chat sound is now enabled', 'success', globalVar.successNotify, false);
                    }
                } else {
                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                        $.each(data.errors, function (index, value) {
                            $.notify({ message: value }, globalVar.dangerNotify);
                        });
                    }
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler function defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    });

    // When messenger page active then update all unread messages status to "Read"
    $('main').on('hover', function () {
        if ($('#chat-room').get(0)) {
            var notificationSignal = $('#top-msg-notification').find('.notification-signal');

            if (notificationSignal.length > 0 && notificationSignal.css('display') !== 'none') {
                $.ajax({
                    type     : 'POST',
                    url      : globalVar.baseAdminUrl + '/message-read',
                    dataType : 'JSON',
                    success  : function (data) {
                        if (data.status === true) {
                            $('#top-msg-notification').find('.notification-signal').hide();
                        }
                    },
                    error : function (jqXHR, textStatus, errorThrown) {
                        ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
                    }
                });
            }
        }
    });

    // When clicking a chat room in side nav then load all chat histories of the chat room
    $('.navlist').on('click', '.navlist-item', function (event) {
        var chatRoomId   = $(this).attr('chatroomid');
        var thisChatRoom = $(this);

        if (!$(this).hasClass('active')) {
            $('#chat-message-box .chat-message').hide();
            $('#chat-message .content-loader.initial').show();

            $.ajax({
                type     : 'POST',
                url      : globalVar.baseAdminUrl + '/message/chatroom/history',
                data     : { id: chatRoomId },
                dataType : 'JSON',
                success  : function (data) {
                    if (data.status === true) {
                        var loadStatus = data.load ? 'true' : 'false';
                        $('#chat-message-box').attr('data-load', loadStatus);
                        $('#chat-message-box').attr('data-roomid', chatRoomId);
                        $('#room').val(chatRoomId);
                        $("*[data-item='announcement']").attr('data-default', 'active_chatroom_id:' + chatRoomId);

                        // input message field
                        $('#send-msg').val('');
                        $('#send-msg').prop('disabled', data.inactive);
                        $('#send-msg').prop('placeholder', data.inactivemsg);

                        $('.navlist-item').removeClass('active');
                        thisChatRoom.addClass('active');

                        // chat room title
                        $('#chat-message-title').children('img').remove();
                        $('#chat-message-title').prepend(data.avatar);
                        $('#chat-message-title h5').html(data.name);
                        $('#chat-message-title p').html(data.title);

                        // load and render chat histories
                        $('#chat-message-box').html(data.history);
                        $('#chat-message-box').scrollTop($('#chat-message-box').prop('scrollHeight'));
                        $('#chat-message .content-loader.initial').fadeOut(500);
                        $('[data-toggle="tooltip"]').tooltip();
                        window.history.pushState({
                            name        : data.name,
                            title       : data.title,
                            avatar      : data.avatar,
                            inactive    : data.inactive,
                            inactivemsg : data.inactivemsg,
                            html        : data.history
                        }, '', chatRoomId);
                    } else {
                        alert('Something went wrong! Please try again.');
                    }
                },
                error : function (jqXHR, textStatus, errorThrown) {
                    // ajaxErrorHandler function defined in js/app.js
                    ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
                }
            });
        }
    });

    // Get content from windows history without page loading
    window.onpopstate = function (e) {
        if (e.state) {
            var activeChat = window.location.href.split('/').last();
            $('#room').val(activeChat);
            $('.navlist-item').removeClass('active');
            $(".navlist-item[chatroomid='" + activeChat + "']").addClass('active');

            $('#send-msg').val('');
            $('#send-msg').prop('disabled', e.state.inactive);
            $('#send-msg').prop('placeholder', e.state.inactivemsg);

            $('#chat-message-title').children('img').remove();
            $('#chat-message-title').prepend(e.state.avatar);
            $('#chat-message-title h5').html(e.state.name);
            $('#chat-message-title p').html(e.state.title);
            $('#chat-message-box').html(e.state.html);
        }
    };

    $(window).resize(function () {
        adjustChatBoxHeight();
    });
});

/**
 * Adjust chatbox height with the window height.
 *
 * @return {void}
 */
function adjustChatBoxHeight () {
    var roomHeight        = 560;
    var memberListHeight  = 427;
    var messageContHeight = 425;

    if ($(window).height()) {
        roomHeight        = $(window).height() - 51;
        memberListHeight  = roomHeight - 133;
        messageContHeight = roomHeight - 135;
    }

    $('#chat-room').css('height', roomHeight + 'px');
    $('#chat-room .navlist ').css('height', memberListHeight + 'px');
    $('#chat-message-box').css('height', messageContHeight + 'px');
}

/**
 * Send a message by ajax request and respond to real-time HTML.
 *
 * @param {Object} form
 *
 * @return {void}
 */
function sendChatMessage (form) {
    var formData   = form.find('textarea, input').serialize();
    var chatRoom   = form.find("input[name='room']").val();
    var message    = form.find("textarea[name='message']").val();
    var uploadFile = form.find("input[name='uploaded_files[]']").get(0);

    if (typeof uploadFile !== 'undefined' || $.trim(message).length !== 0) {
        $.ajax({
            type     : 'POST',
            url      : globalVar.baseAdminUrl + '/message',
            data     : formData,
            dataType : 'JSON',
            success  : function (data) {
                if (data.status === true) {
                    var lastChild = $('#chat-message-box .chat-message:last');

                    // message placement position left|right
                    if (lastChild.hasClass('right')) {
                        lastChild.find('.msg-content').append(data.messagechildhtml);
                    } else {
                        $('#chat-message-box').append(data.messagehtml);
                    }

                    // active chat room render on the top list, reset message input field
                    $(".navlist-item[chatroomid='" + chatRoom + "'").parent('li').remove();
                    $('.navlist').prepend(data.chatroomhtml);
                    $('[data-toggle="tooltip"]').tooltip();
                    adjustChatBoxHeight();
                    $('#send-msg').val('');
                    $('#chat-message-box').scrollTop($('#chat-message-box').prop('scrollHeight'));

                    // Reset upload files field
                    var dropzoneId = form.find('.modalfree-dropzone').attr('id');
                    globalVar.dropzone[dropzoneId].files = [];
                    form.find('.dz-preview-container').html('');
                    form.find("input[name='uploaded_files[]']").remove();

                    $('.msg-file-container').scrollTop(0);
                    $('.msg-file-container').css('height', 'auto');
                } else {
                    alert('Something went wrong! Please try again.');
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler function defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'notify', false, 2500);
            }
        });
    } else {
        form.find("textarea[name='message']").focus();
    }
}

/**
 * Ajax request to load old chat histories.
 *
 * @param {Object} chatBox
 *
 * @return {void}
 */
function ajaxChatHistory (chatBox) {
    var oldHeight  = chatBox.prop('scrollHeight');
    var loadStatus = chatBox.attr('data-load');
    var chatRoom   = parseInt(chatBox.attr('data-roomid'), 10);
    var latestId   = $('#chat-message-box .chat-message:first .full:first p').attr('messageid');

    if (loadStatus === 'true') {
        $('#chat-message .content-loader.history').show();

        $.ajax({
            type     : 'GET',
            url      : globalVar.baseAdminUrl + '/chat-history-data/' + chatRoom,
            data     : { room: chatRoom, id: parseInt(latestId, 10) },
            dataType : 'JSON',
            success  : function (data) {
                $('#chat-message .content-loader.history').hide();

                if (data.status === true) {
                    // load and render old chat histories
                    $($(data.html).get().reverse()).each(function (index, chatLine) {
                        $(chatLine).hide().prependTo(chatBox).fadeIn(2000);
                    });

                    var goUp = 45;

                    if ($(data.html).find('.msg-content .full img').get(0)) {
                        goUp = 0;
                    }

                    var newHeight = chatBox.prop('scrollHeight');
                    var scrollTop = newHeight - oldHeight - goUp;
                    chatBox.scrollTop(scrollTop);
                    $('[data-toggle="tooltip"]').tooltip();

                    // If don't have any more old message then next data load status value is false
                    if (!data.load) {
                        chatBox.attr('data-load', 'false');
                    }
                } else {
                    if (!$(".alert.alert-danger[role='alert']").get(0)) {
                        $.each(data.errors, function (index, value) {
                            // globalVar defined in partials/footer.blade.php
                            $.notify({ message: value }, globalVar.dangerNotify);
                        });

                        if (data.errors == null) {
                            $.notify({ message: 'Something went wrong.' }, globalVar.dangerNotify);
                        }
                    }
                }
            },
            error : function (jqXHR, textStatus, errorThrown) {
                // ajaxErrorHandler function defined in js/app.js
                ajaxErrorHandler(jqXHR, textStatus, errorThrown, 'confirm', true, 2500);
            }
        });
    }
}
