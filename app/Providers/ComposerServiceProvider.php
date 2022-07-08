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

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.master', 'App\Http\Composers\AdminViewComposer');
        View::composer('admin.dashboard.partials.filter-form', 'App\Http\Composers\AdminViewComposer@dashboardFilter');
        View::composer('admin.message.partials.announcement-form', 'App\Http\Composers\AdminViewComposer@announcementForm');
        View::composer('admin.notification.partials.filter-form', 'App\Http\Composers\AdminViewComposer@notificationFilter');
        View::composer(['admin.user.partials.form', 'admin.user.partials.modal-message'], 'App\Http\Composers\AdminViewComposer@userForm');
        View::composer(['admin.user.show', 'admin.user.partials.tabs.*'], 'App\Http\Composers\AdminViewComposer@userInformation');
        View::composer('admin.user.partials.modal-follower', 'App\Http\Composers\AdminViewComposer@follower');
        View::composer(['partials.modals.access', 'partials.modals.common-view-form'], 'App\Http\Composers\AdminViewComposer@accessModal');
        View::composer(['admin.project.partials.form', 'admin.project.partials.bulk-update-form', 'admin.project.show', 'admin.project.partials.tabs.*'], 'App\Http\Composers\AdminViewComposer@projectForm');
        View::composer('admin.project.partials.member-form', 'App\Http\Composers\AdminViewComposer@memberForm');
        View::composer('admin.projectstatus.partials.form', 'App\Http\Composers\AdminViewComposer@projectStatusForm');
        View::composer(['admin.milestone.partials.form', 'admin.milestone.show', 'admin.milestone.partials.tabs.*'], 'App\Http\Composers\AdminViewComposer@milestoneForm');
        View::composer(['admin.task.partials.form', 'admin.task.partials.bulk-update-form', 'admin.task.show', 'admin.task.partials.tabs.*'], 'App\Http\Composers\AdminViewComposer@taskForm');
        View::composer('admin.taskstatus.partials.form', 'App\Http\Composers\AdminViewComposer@taskStatusForm');
        View::composer(['admin.issue.partials.form', 'admin.issue.partials.bulk-update-form', 'admin.issue.show', 'admin.issue.partials.tabs.*'], 'App\Http\Composers\AdminViewComposer@issueForm');
        View::composer('admin.issuestatus.partials.form', 'App\Http\Composers\AdminViewComposer@issueStatusForm');
        View::composer('admin.issuetype.partials.form', 'App\Http\Composers\AdminViewComposer@issueTypeForm');
        View::composer(['admin.event.partials.form', 'admin.event.partials.bulk-update-form', 'admin.event.show', 'admin.event.partials.tabs.*'], 'App\Http\Composers\AdminViewComposer@eventForm');
        View::composer('admin.event.partials.modal-event-attendee', 'App\Http\Composers\AdminViewComposer@eventAttendee');
        View::composer('admin.setting.general', 'App\Http\Composers\AdminViewComposer@settingGeneralForm');
        View::composer('partials.tabs.*', 'App\Http\Composers\AdminViewComposer@tab');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
