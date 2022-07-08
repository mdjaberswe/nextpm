<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');
Route::get('test', 'TestController@index');

// Middleware: Only accessible following routes iff app is installed and only for authenticated users.
Route::group(['middleware' => ['initial.req', 'install', 'auth']], function () {
    Route::get('home', ['as' => 'home', 'uses' => 'HomeController@index']);
    Route::get('set-sidenav-status', ['as' => 'sidenav.status', 'uses' => 'HomeController@setSidenavStatus']);

    // Error Page
    Route::get('404', ['as' => '404', 'uses' => 'ErrorController@notFound']);
    Route::get('500', ['as' => '500', 'uses' => 'ErrorController@fatal']);
});

// Middleware: Only accessible following routes iff app is installed.
Route::group(['namespace' => 'Auth', 'middleware' => ['install']], function () {
    Route::get('signin', ['as' => 'auth.signin', 'uses' => 'AuthController@signin']);
    Route::post('signin', ['as' => 'auth.signin.post', 'uses' => 'AuthController@postSignin']);
    Route::get('signout', ['as' => 'auth.signout', 'uses' => 'AuthController@signout']);

    // Password reset
    Route::get('password/reset/{token?}', ['as' => 'password.reset.form', 'uses' => 'PasswordController@showResetForm']);
    Route::post('password/email', ['as' => 'password.reset.link', 'uses' => 'PasswordController@sendResetLinkEmail']);
    Route::post('password/reset', ['as' => 'password.reset', 'uses' => 'PasswordController@reset']);
});

// Middleware: Only accessible following routes iff app is not installed yet.
Route::group(['middleware' => ['initial.req', 'uninstall']], function () {
    Route::get('install/system', ['as' => 'install.system', 'uses' => 'InstallController@system']);
    Route::get('install/config', ['as' => 'install.config', 'uses' => 'InstallController@config']);
    Route::get('install/database', ['as' => 'install.database', 'uses' => 'InstallController@database']);
    Route::get('install/import', ['as' => 'install.import', 'uses' => 'InstallController@import']);
    Route::get('install/importing', ['as' => 'install.import.status', 'uses' => 'InstallController@importStatus']);
    Route::get('install/complete', ['as' => 'install.complete', 'uses' => 'InstallController@complete']);
    Route::post('install/post-config', ['as' => 'install.post.config', 'uses' => 'InstallController@postConfig']);
    Route::post('install/post-database', ['as' => 'install.post.database', 'uses' => 'InstallController@postDatabase']);
    Route::post('install/post-import', ['as' => 'install.post.import', 'uses' => 'InstallController@postImport']);
});

Route::get('system-requirement', ['as' => 'system.requirement', 'uses' => 'InstallController@initialRequirement']);

// Middleware: Following routes are accessible if the app is installed but unlicensed and only for administrators.
Route::group(['middleware' => ['initial.req', 'install', 'auth', 'auth.type:staff', 'unlicensed']], function () {
    Route::get('license', ['as' => 'license.verification', 'uses' => 'LicenseController@verification']);
    Route::post('post-license', ['as' => 'license.post.verification', 'uses' => 'LicenseController@postVerification']);
});

Route::post('deactivate-license', ['as' => 'license.deactivate', 'uses' => 'LicenseController@deactivate'])->middleware(['licensed']);

// Middleware: Only accessible following routes iff app is installed, licensed, and only for authenticated admin users.
Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['initial.req', 'install', 'auth', 'auth.type:staff', 'licensed']], function () {
    // Dashboard
    Route::get('dashboard', ['as' => 'admin.dashboard.index', 'uses' => 'AdminDashboardController@index']);
    Route::post('dashboard', ['as' => 'admin.dashboard.post.index', 'uses' => 'AdminDashboardController@index']);
    Route::post('dashboard-widget-table/{widget}', ['as' => 'admin.dashboard.widget.table', 'uses' => 'AdminDashboardController@widgetTableData']);
    Route::post('calendar-data', ['as' => 'admin.calendar.data', 'uses' => 'AdminDashboardController@calendarData']);

    // Project
    Route::resource('project', 'AdminProjectController', ['except' => ['create', 'show']]);
    Route::get('project-kanban', ['as' => 'admin.project.kanban', 'uses' => 'AdminProjectController@indexKanban']);
    Route::get('project/{project}/{infotype?}', ['as' => 'admin.project.show', 'uses' => 'AdminProjectController@show']);
    Route::get('project-gantt-data/{project}/{filter?}', ['as' => 'admin.project.gantt.data', 'uses' => 'AdminProjectController@ganttData']);
    Route::post('project-data', ['as' => 'admin.project.data', 'uses' => 'AdminProjectController@projectData']);
    Route::post('project/{project}/single-update', ['as' => 'admin.project.single.update', 'uses' => 'AdminProjectController@singleUpdate']);
    Route::post('connected-project/{module_name}/{module_id}', ['as' => 'admin.connected.project.data', 'uses' => 'AdminProjectController@connectedProjectData']);
    Route::post('project-kanban-card/{projectstatus}/{module_name?}/{module_id?}', ['as' => 'admin.project.kanban.card', 'uses' => 'AdminProjectController@kanbanCard']);
    Route::post('project-bulk-update', ['as' => 'admin.project.bulk.update', 'uses' => 'AdminProjectController@bulkUpdate']);
    Route::post('project-bulk-delete', ['as' => 'admin.project.bulk.delete', 'uses' => 'AdminProjectController@bulkDestroy']);
    Route::get('project-member-edit/{project}/{staff}', ['as' => 'admin.member.edit', 'uses' => 'AdminProjectController@memberEdit']);
    Route::post('project-member-data/{project}/{viewonly?}', ['as' => 'admin.member.data', 'uses' => 'AdminProjectController@memberData']);
    Route::post('project-member-store/{project}', ['as' => 'admin.member.store', 'uses' => 'AdminProjectController@memberStore']);
    Route::put('project-member-update/{project}/{staff}', ['as' => 'admin.member.update', 'uses' => 'AdminProjectController@memberUpdate']);
    Route::delete('project-member-delete/{project}/{staff}', ['as' => 'admin.member.destroy', 'uses' => 'AdminProjectController@memberDelete']);

    // Task
    Route::resource('task', 'AdminTaskController', ['except' => ['create', 'show']]);
    Route::get('task-kanban', ['as' => 'admin.task.kanban', 'uses' => 'AdminTaskController@indexKanban']);
    Route::get('task-calendar', ['as' => 'admin.task.calendar', 'uses' => 'AdminTaskController@indexCalendar']);
    Route::get('task/{task}/{infotype?}', ['as' => 'admin.task.show', 'uses' => 'AdminTaskController@show']);
    Route::post('task-data', ['as' => 'admin.task.data', 'uses' => 'AdminTaskController@taskData']);
    Route::post('task-calendar-data', ['as' => 'admin.task.calendar.data', 'uses' => 'AdminTaskController@calendarData']);
    Route::post('task-calendar-update-position', ['as' => 'admin.task.calendar.update.position', 'uses' => 'AdminTaskController@updateCalendarPosition']);
    Route::post('connected-task/{module_name}/{module_id}', ['as' => 'admin.connected.task.data', 'uses' => 'AdminTaskController@connectedTaskData']);
    Route::post('task/{task}/single-update', ['as' => 'admin.task.single.update', 'uses' => 'AdminTaskController@singleUpdate']);
    Route::post('task-closed-reopen/{task}', ['as' => 'admin.task.closed.reopen', 'uses' => 'AdminTaskController@closedOrReopen']);
    Route::post('task-kanban-card/{taskstatus}/{module_name?}/{module_id?}', ['as' => 'admin.task.kanban.card', 'uses' => 'AdminTaskController@kanbanCard']);
    Route::post('task-bulk-update', ['as' => 'admin.task.bulk.update', 'uses' => 'AdminTaskController@bulkUpdate']);
    Route::post('task-bulk-delete', ['as' => 'admin.task.bulk.delete', 'uses' => 'AdminTaskController@bulkDestroy']);

    // Issue
    Route::resource('issue', 'AdminIssueController', ['except' => ['create', 'show']]);
    Route::get('issue-kanban', ['as' => 'admin.issue.kanban', 'uses' => 'AdminIssueController@indexKanban']);
    Route::get('issue-calendar', ['as' => 'admin.issue.calendar', 'uses' => 'AdminIssueController@indexCalendar']);
    Route::get('issue/{issue}/{infotype?}', ['as' => 'admin.issue.show', 'uses' => 'AdminIssueController@show']);
    Route::post('issue-data', ['as' => 'admin.issue.data', 'uses' => 'AdminIssueController@issueData']);
    Route::post('issue-calendar-data', ['as' => 'admin.issue.calendar.data', 'uses' => 'AdminIssueController@calendarData']);
    Route::post('issue-calendar-update-position', ['as' => 'admin.issue.calendar.update.position', 'uses' => 'AdminIssueController@updateCalendarPosition']);
    Route::post('connected-issue/{module_name}/{module_id}', ['as' => 'admin.connected.issue.data', 'uses' => 'AdminIssueController@connectedIssueData']);
    Route::post('issue/{issue}/issue-update', ['as' => 'admin.issue.single.update', 'uses' => 'AdminIssueController@singleUpdate']);
    Route::post('issue-closed-reopen/{issue}', ['as' => 'admin.issue.closed.reopen', 'uses' => 'AdminIssueController@closedOrReopen']);
    Route::post('issue-kanban-card/{issuestatus}/{module_name?}/{module_id?}', ['as' => 'admin.issue.kanban.card', 'uses' => 'AdminIssueController@kanbanCard']);
    Route::post('issue-bulk-update', ['as' => 'admin.issue.bulk.update', 'uses' => 'AdminIssueController@bulkUpdate']);
    Route::post('issue-bulk-delete', ['as' => 'admin.issue.bulk.delete', 'uses' => 'AdminIssueController@bulkDestroy']);

    // Milestone
    Route::resource('milestone', 'AdminMilestoneController', ['except' => ['index', 'create', 'show']]);
    Route::get('milestone/{milestone}/{infotype?}', ['as' => 'admin.milestone.show', 'uses' => 'AdminMilestoneController@show']);
    Route::post('milestone/{milestone}/single-update', ['as' => 'admin.milestone.single.update', 'uses' => 'AdminMilestoneController@singleUpdate']);
    Route::post('connected-milestone/{module_name}/{module_id}', ['as' => 'admin.connected.milestone.data', 'uses' => 'AdminMilestoneController@connectedMilestoneData']);
    Route::post('sequence-milestone/{module_name}/{module_id}', ['as' => 'admin.sequence.milestone.data', 'uses' => 'AdminMilestoneController@sequenceMilestoneData']);
    Route::post('milestone-calendar-update-position', ['as' => 'admin.milestone.calendar.update.position', 'uses' => 'AdminMilestoneController@updateCalendarPosition']);

    // Event
    Route::resource('event', 'AdminEventController', ['except' => ['create', 'show']]);
    Route::get('calendar', ['as' => 'admin.event.calendar', 'uses' => 'AdminEventController@indexCalendar']);
    Route::get('event/{event}/{infotype?}', ['as' => 'admin.event.show', 'uses' => 'AdminEventController@show']);
    Route::get('event-calendar-filter', ['as' => 'calendar.filter', 'uses' => 'AdminEventController@setCalendarFilter']);
    Route::post('event-data', ['as' => 'admin.event.data', 'uses' => 'AdminEventController@eventData']);
    Route::post('event-calendar-data', ['as' => 'admin.event.calendar.data', 'uses' => 'AdminEventController@calendarData']);
    Route::post('related-calendar-data/{module_name}/{module_id}', ['as' => 'admin.related.calendar.data', 'uses' => 'AdminEventController@relatedCalendarData']);
    Route::post('connected-event/{module_name}/{module_id}', ['as' => 'admin.connected.event.data', 'uses' => 'AdminEventController@connectedEventData']);
    Route::post('event/{event}/single-update', ['as' => 'admin.event.single.update', 'uses' => 'AdminEventController@singleUpdate']);
    Route::post('event-update-position', ['as' => 'admin.event.calendar.update.position', 'uses' => 'AdminEventController@updatePosition']);
    Route::post('event-bulk-update', ['as' => 'admin.event.bulk.update', 'uses' => 'AdminEventController@bulkUpdate']);
    Route::post('event-bulk-delete', ['as' => 'admin.event.bulk.delete', 'uses' => 'AdminEventController@bulkDestroy']);
    Route::post('event-attendee-data/{event}', ['as' => 'admin.event.attendee.data', 'uses' => 'AdminEventController@eventAttendeeData']);
    Route::post('event-attendee-store/{event}', ['as' => 'admin.event.attendee.store', 'uses' => 'AdminEventController@eventAttendeeStore']);
    Route::delete('event-attendee-destroy/{event_attendee}', ['as' => 'admin.event.attendee.destroy', 'uses' => 'AdminEventController@eventAttendeeDestroy']);

    // Note
    Route::get('note-data/{type}', ['as' => 'admin.note.data', 'uses' => 'AdminNoteController@getData']);
    Route::post('note-store', ['as' => 'admin.note.store', 'uses' => 'AdminNoteController@store']);
    Route::get('note-edit/{note}', ['as' => 'admin.note.edit', 'uses' => 'AdminNoteController@edit']);
    Route::post('note-update/{note}', ['as' => 'admin.note.update', 'uses' => 'AdminNoteController@update']);
    Route::post('note-pin/{note}', ['as' => 'admin.note.pin', 'uses' => 'AdminNoteController@pin']);
    Route::delete('note-destroy/{note}', ['as' => 'admin.note.destroy', 'uses' => 'AdminNoteController@destroy']);
    Route::get('history-data/{type}', ['as' => 'admin.history.data', 'uses' => 'AdminHistoryController@getData']);

    // Import
    Route::get('import-csv', ['as' => 'admin.import.csv', 'uses' => 'AdminImportController@getCsv']);
    Route::post('import-map', ['as' => 'admin.import.map', 'uses' => 'AdminImportController@map']);
    Route::post('import-post', ['as' => 'admin.import.post', 'uses' => 'AdminImportController@import']);
    Route::post('import-data/{import}', ['as' => 'admin.import.data', 'uses' => 'AdminImportController@importData']);

    // File
    Route::get('file-show/{attachfile}/{filename}/{download?}', ['as' => 'admin.file.show', 'uses' => 'AdminFileController@show']);
    Route::post('file-data/{linked_type}/{linked_id}', ['as' => 'admin.file.data', 'uses' => 'AdminFileController@fileData']);
    Route::post('file-upload', ['as' => 'admin.file.upload', 'uses' => 'AdminFileController@upload']);
    Route::post('avatar-upload', ['as' => 'admin.avatar.upload', 'uses' => 'AdminFileController@uploadAvatar']);
    Route::post('file-store', ['as' => 'admin.file.store', 'uses' => 'AdminFileController@store']);
    Route::post('link-store', ['as' => 'admin.link.store', 'uses' => 'AdminFileController@linkStore']);
    Route::post('file-remove', ['as' => 'admin.file.remove', 'uses' => 'AdminFileController@remove']);
    Route::delete('file-destroy/{attachfile}', ['as' => 'admin.file.destroy', 'uses' => 'AdminFileController@destroy']);

    // Notification
    Route::resource('notification', 'AdminNotificationController', ['only' => ['index']]);
    Route::post('notification-data', ['as' => 'admin.notification.data', 'uses' => 'AdminNotificationController@notificationData']);
    Route::post('notification-read', ['as' => 'admin.notification.read', 'uses' => 'AdminNotificationController@read']);
    Route::post('realtime-notification', ['as' => 'admin.notification.realtime', 'uses' => 'AdminNotificationController@realtimeNotification']);

    // Message
    Route::resource('message', 'AdminMessageController', ['only' => ['index', 'store']]);
    Route::get('message/{chatroom}', ['as' => 'admin.message.chatroom', 'uses' => 'AdminMessageController@chatroom']);
    Route::get('chat-history-data/{chatroom}', ['as' => 'admin.chat.history.data', 'uses' => 'AdminMessageController@historyData']);
    Route::post('message/chatroom/history', ['as' => 'admin.message.chatroom.history', 'uses' => 'AdminMessageController@chatroomHistory']);
    Route::post('message-read', ['as' => 'admin.message.read', 'uses' => 'AdminMessageController@read']);
    Route::post('announcement-store', ['as' => 'admin.announcement.store', 'uses' => 'AdminMessageController@announcementStore']);

    // Custom Dropdown Settings
    Route::resource('administration-dropdown-projectstatus', 'AdminProjectStatusController', ['except' => ['create', 'show'],'parameters' => ['administration-dropdown-projectstatus' => 'projectstatus']]);
    Route::post('projectstatus-data', ['as' => 'admin.projectstatus.data', 'uses' => 'AdminProjectStatusController@projectStatusData']);
    Route::resource('administration-dropdown-taskstatus', 'AdminTaskStatusController', ['except' => ['create', 'show'],'parameters' => ['administration-dropdown-taskstatus' => 'taskstatus']]);
    Route::post('taskstatus-data', ['as' => 'admin.taskstatus.data', 'uses' => 'AdminTaskStatusController@taskStatusData']);
    Route::resource('administration-dropdown-issuestatus', 'AdminIssueStatusController', ['except' => ['create', 'show'],'parameters' => ['administration-dropdown-issuestatus' => 'issuestatus']]);
    Route::post('issuestatus-data', ['as' => 'admin.issuestatus.data', 'uses' => 'AdminIssueStatusController@issueStatusData']);
    Route::resource('administration-dropdown-issuetype', 'AdminIssueTypeController', ['except' => ['create', 'show'],'parameters' => ['administration-dropdown-issuetype' => 'issuetype']]);
    Route::post('issuetype-data', ['as' => 'admin.issuetype.data', 'uses' => 'AdminIssueTypeController@issueTypeData']);

    // System Settings
    Route::get('administration-setting-general', ['as' => 'admin.administration-setting.general', 'uses' => 'AdminSettingController@index']);
    Route::post('setting-general-post', ['as' => 'setting.general.post', 'uses' => 'AdminSettingController@postGeneral']);
    Route::get('administration-setting-email', ['as' => 'admin.administration-setting.email', 'uses' => 'AdminSettingController@email']);
    Route::post('setting-email-post', ['as' => 'setting.email.post', 'uses' => 'AdminSettingController@postEmail']);

    // User
    Route::resource('user', 'AdminUserController', ['except' => ['create', 'show']]);
    Route::get('user/{user}/{infotype?}', ['as' => 'admin.user.show', 'uses' => 'AdminUserController@show']);
    Route::get('allowed-user-data/{type}/{id}', ['as' => 'admin.allowed.type.data', 'uses' => 'AdminUserController@allowedTypeData']);
    Route::post('user-data', ['as' => 'admin.user.data', 'uses' => 'AdminUserController@userData']);
    Route::post('allowed-user-data', ['as' => 'admin.allowed.user.data', 'uses' => 'AdminUserController@allowedUserData']);
    Route::post('allowed-user/{type}/{id}', ['as' => 'admin.post.allowed.user', 'uses' => 'AdminUserController@postAllowedUser']);
    Route::post('follower/{type}/{id}', ['as' => 'admin.post.follower', 'uses' => 'AdminUserController@postFollower']);
    Route::post('follower-data/{module_name}/{module_id}', ['as' => 'admin.follower.data', 'uses' => 'AdminUserController@followerData']);
    Route::post('user/{user}/single-update', ['as' => 'admin.user.single.update', 'uses' => 'AdminUserController@singleUpdate']);
    Route::post('user-status/{user}', ['as' => 'admin.user.status', 'uses' => 'AdminUserController@updateStatus']);
    Route::post('user-password/{user}', ['as' => 'admin.user.password', 'uses' => 'AdminUserController@updatePassword']);
    Route::post('user-bulk-delete', ['as' => 'admin.user.bulk.delete', 'uses' => 'AdminUserController@bulkDestroy']);
    Route::post('user-bulk-status', ['as' => 'admin.user.bulk.status', 'uses' => 'AdminUserController@bulkStatus']);
    Route::post('user-message', ['as' => 'admin.user.message', 'uses' => 'AdminUserController@message']);
    Route::post('user-setting', ['as' => 'admin.user.setting', 'uses' => 'AdminUserController@setting']);

    // Role
    Route::resource('role', 'AdminRoleController');
    Route::post('role-users/{role}', ['as' => 'admin.role.user.list', 'uses' => 'AdminRoleController@usersList']);
    Route::post('role-data', ['as' => 'admin.role.data', 'uses' => 'AdminRoleController@roleData']);
    Route::post('role-bulk-delete', ['as' => 'admin.role.bulk.delete', 'uses' => 'AdminRoleController@bulkDestroy']);

    // Base
    Route::get('images/{img}', ['as' => 'admin.image', 'uses' => 'AdminBaseController@image']);
    Route::get('dropdown-list', ['as' => 'admin.dropdown.list', 'uses' => 'AdminBaseController@dropdownList']);
    Route::get('dropdown-append-list/{parent}/{child}', ['as' => 'admin.dropdown.append.list', 'uses' => 'AdminBaseController@dropdownAppendList']);
    Route::get('view-toggle/{module_name}', ['as' => 'admin.view.toggle', 'uses' => 'AdminBaseController@viewToggle']);
    Route::get('view-content', ['as' => 'admin.view.content', 'uses' => 'AdminBaseController@viewContent']);
    Route::post('tab/{module_name}/{module_id}/{tab}', ['as' => 'admin.tab.content', 'uses' => 'AdminBaseController@tabContent']);
    Route::get('filter-form-content/{module_name}', ['as' => 'admin.filter.form.content', 'uses' => 'AdminBaseController@filterFormContent']);
    Route::get('view/edit/{filterview}', ['as' => 'admin.view.edit', 'uses' => 'AdminBaseController@viewEdit']);
    Route::put('view/update/{filterview}', ['as' => 'admin.view.update', 'uses' => 'AdminBaseController@viewUpdate']);
    Route::post('filter-form-post/{module_name}', ['as' => 'admin.filter.form.post', 'uses' => 'AdminBaseController@filterFormPost']);
    Route::post('save-view/{module_name}', ['as' => 'admin.view.store', 'uses' => 'AdminBaseController@viewStore']);
    Route::post('dropdown-view/{filterview_id?}', ['as' => 'admin.view.dropdown', 'uses' => 'AdminBaseController@viewDropdown']);
    Route::delete('delete-view/{filterview}', ['as' => 'admin.view.destroy', 'uses' => 'AdminBaseController@viewDestroy']);
    Route::get('kanban-reorder', ['as' => 'admin.kanban.reorder', 'uses' => 'AdminBaseController@kanbanReorder']);
    Route::post('dropdown-reorder', ['as' => 'admin.dropdown.reorder', 'uses' => 'AdminBaseController@dropdownReorder']);
});
