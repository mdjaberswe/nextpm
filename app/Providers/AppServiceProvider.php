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

use License;
use Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'user'             => \App\Models\User::class,
            'staff'            => \App\Models\Staff::class,
            'role'             => \App\Models\Role::class,
            'milestone'        => \App\Models\Milestone::class,
            'project'          => \App\Models\Project::class,
            'task'             => \App\Models\Task::class,
            'issue'            => \App\Models\Issue::class,
            'event'            => \App\Models\Event::class,
            'setting'          => \App\Models\Setting::class,
            'import'           => \App\Models\Import::class,
            'chat_room'        => \App\Models\ChatRoom::class,
            'note_info'        => \App\Models\NoteInfo::class,
            'note'             => \App\Models\Note::class,
            'project_status'   => \App\Models\ProjectStatus::class,
            'task_status'      => \App\Models\TaskStatus::class,
            'issue_status'     => \App\Models\IssueStatus::class,
            'issue_type'       => \App\Models\IssueType::class,
            'attach_file'      => \App\Models\AttachFile::class,
            'chat_sender'      => \App\Models\ChatSender::class,
            'chat_receiver'    => \App\Models\ChatReceiver::class,
            'event_attendee'   => \App\Models\EventAttendee::class,
            'social_media'     => \App\Models\SocialMedia::class,
            'allowed_staff'    => \App\Models\AllowedStaff::class,
            'follower'         => \App\Models\Follower::class,
            'filter_view'      => \App\Models\FilterView::class,
            'chat_room_member' => \App\Models\ChatRoomMember::class,
            'notification'     => \Illuminate\Notifications\DatabaseNotification::class,
        ]);

        table_config_set('settings');
        override_config('app', 'setting');
        override_config('mail', 'setting', null, ['username', 'password']);
        config(['app.license' => License::getInstalledInfo()]);

        Validator::extend('valid_domain', function ($attribute, $value, $parameters, $validator) {
            return valid_url_or_domain($value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
