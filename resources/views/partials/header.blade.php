<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{!! csrf_token() !!}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{!! asset(config('setting.favicon')) !!}">
        <title>{!! isset($page['title']) ? $page['title'] : config('setting.app_name') !!}</title>

        @include('partials.global-css')
        @stack('css')

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="{!! array_key_exists('multi_section', $page) ? 'multiple-section' : null !!}">
        <header>
            <div id="logo" class="header-logo left-justify {!! $class['logo'] !!}">
                @if (not_null_empty(config('setting.logo')) && file_exists(public_path(config('setting.logo'))))
                    <a class="logo-link logo-img"><img src="{!! asset(config('setting.logo')) !!}" alt="logo" realtime="logo"/></a>
                @else
                    <a class="logo-link max-overflow-ellipsis" realtime="logotxt">{!! config('setting.app_name') !!}</a>
                @endif

                <a class="menu-toggler"><i class="fa fa-bars"></i></a>
                <a class="mob-menu-toggler"><i class="fa fa-bars"></i></a>
            </div> <!-- end logo -->

            <div id="top-nav" class="header-nav left-justify {!! $class['top_nav'] !!}">
                <div class="btn-group">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" data-avt="{!! auth()->user()->linked_type . auth()->user()->linked_id !!}">
                        <img class="circle-avt" src="{!! auth_staff()->avatar !!}" alt="{!! auth_staff()->name !!}" title="{!! auth_staff()->name !!}">
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{!! route('admin.user.show', auth_staff()->id) !!}"><i class="fa fa-user"></i> My Profile</a></li>
                        <li><a class="add-multiple" data-item="user" data-action="{!! route('admin.user.password', auth_staff()->id) !!}" data-content="user.partials.modal-password" data-default="{!! 'id:' . auth_staff()->id !!}" modal-title="Change Password" modal-sub-title="{!! auth_staff()->name !!}" data-modalsize="tiny" save-new="false"><i class="fa fa-key"></i> Change Password</a></li>
                        <li class="divider"></li>
                        <li><a href="{!! route('auth.signout') !!}"><i class="fa fa-power-off"></i> Sign Out</a></li>
                    </ul>
                </div>

                <div class="btn-group">
                    <a id="top-notification" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell"></i>
                        @if ($unread_notifications_count)
                            @if ($unread_notifications_count > 99)
                                <p class="notification-signal font-sm bg-a">99+</p>
                            @else
                                <p class="notification-signal bg-a">{!! $unread_notifications_count !!}</p>
                            @endif
                        @endif
                    </a>
                    <div id="top-notification-list" class="dropdown-menu top-notification-list">
                        @if (count($notifications) > 0)
                            <ul class="scroll-dropdown mob-compress">
                                @foreach ($notifications as $notification)
                                    {!! $notification->list_html !!}
                                @endforeach
                            </ul>
                            <a href="{!! route('admin.notification.index') !!}" class="bottom-link">View all notifications</a>
                        @else
                            <ul class="scroll-dropdown mob-compress">
                                <li class="emptylist">
                                    <a class="dropdown-notification"></a>
                                </li>
                            </ul>
                            <a href="{!! route('admin.notification.index') !!}" class="bottom-link">No notifications found</a>
                        @endif
                    </div>
                </div>

                @if (count($chat_messages) > 0)
                    <div class="btn-group">
                        <a id="top-msg-notification" class="nav-link dropdown-toggle" data-toggle="dropdown" data-sound="{!! auth_staff()->getSettingVal('chat_sound') !!}">
                            <i class="fa fa-comments"></i>
                            @if ($unread_messages_count)
                                @if ($unread_messages_count > 99)
                                    <p class="notification-signal font-sm bg-a">99+</p>
                                @else
                                    <p class="notification-signal bg-a">{!! $unread_messages_count !!}</p>
                                @endif
                            @endif
                        </a>
                        <div id="top-msg-list" class="dropdown-menu">
                            <ul class="scroll-dropdown mob-compress">
                                @foreach ($chat_messages as $chat_message)
                                    {!! $chat_message->dropdown_list !!}
                                @endforeach
                            </ul>
                            <a id="view-all-msg" href="{!! route('admin.message.chatroom', auth_staff()->latest_chat_id) !!}" class="bottom-link">View all in Messenger</a>
                        </div>
                    </div>
                @else
                    <a id="view-all-msg" href="{!! route('admin.message.index') !!}" class="nav-link"><i class="fa fa-comments"></i></a>
                @endif

                @if (permit('task.create') || permit('issue.create') || permit('event.create') || permit('user.create') || permit('project.create') || permit('milestone.create'))
                    <div class="btn-group compact">
                        <a class="nav-link green dropdown-toggle" data-toggle="dropdown"><i class="mdi mdi-plus"></i></a>

                        <ul class="dropdown-menu">
                            @permission('task.create')
                                <li><a class="add-multiple" data-item="task" data-action="{!! route('admin.task.store') !!}" data-content="task.partials.form"><i class="mdi mdi-plus"></i> Add Task</a></li>
                            @endpermission

                            @permission('issue.create')
                                <li><a class="add-multiple" data-item="issue" data-action="{!! route('admin.issue.store') !!}" data-content="issue.partials.form"><i class="mdi mdi-plus"></i> Add Issue</a></li>
                            @endpermission

                            @permission('event.create')
                                <li><a class="add-multiple" data-item="event" data-action="{!! route('admin.event.store') !!}" data-content="event.partials.form"><i class="mdi mdi-plus"></i> Add Event</a></li>
                            @endpermission

                            @permission('milestone.create')
                                <li><a class="add-multiple" data-item="milestone" data-action="{!! route('admin.milestone.store') !!}" data-content="milestone.partials.form" data-modalsize="medium"><i class="mdi mdi-plus"></i> Add Milestone</a></li>
                            @endpermission

                            @permission('project.create')
                                <li><a class="add-multiple" data-item="project" data-action="{!! route('admin.project.store') !!}" data-content="project.partials.form" data-modalsize="medium"><i class="mdi mdi-plus"></i> Add Project</a></li>
                            @endpermission

                            @permission('user.create')
                                <li><a class="add-multiple" data-item="user" data-action="{!! route('admin.user.store') !!}" data-content="user.partials.form" data-modalsize="tiny"><i class="mdi mdi-plus"></i> Add User</a></li>
                            @endpermission
                        </ul>
                    </div>
                @endif

                <a class="nav-link expand" id="fullscreen"><i class="mdi mdi-crop-free"></i></a>
            </div> <!-- end top-nav -->
        </header> <!-- end header -->
