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

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ProjectDeleted' => [
            'App\Listeners\ProjectDeletedListener',
        ],
        'App\Events\TaskDeleted' => [
            'App\Listeners\TaskDeletedListener',
        ],
        'App\Events\IssueDeleted' => [
            'App\Listeners\IssueDeletedListener',
        ],
        'App\Events\EventDeleted' => [
            'App\Listeners\EventDeletedListener',
        ],
        'App\Events\UserCreated' => [
            'App\Listeners\UserCreatedListener',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
    }
}
