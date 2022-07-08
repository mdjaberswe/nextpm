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

use Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        Route::bind('user', function ($id) {
            $user = \App\Models\Staff::withTrashed()->find($id);

            return isset($user) ? $user : abort('404');
        });

        Route::model('staff', \App\Models\Staff::class);
        Route::model('role', \App\Models\Role::class);
        Route::model('project', \App\Models\Project::class);
        Route::model('projectstatus', \App\Models\ProjectStatus::class);
        Route::model('milestone', \App\Models\Milestone::class);
        Route::model('task', \App\Models\Task::class);
        Route::model('taskstatus', \App\Models\TaskStatus::class);
        Route::model('issue', \App\Models\Issue::class);
        Route::model('issuestatus', \App\Models\IssueStatus::class);
        Route::model('issuetype', \App\Models\IssueType::class);
        Route::model('filterview', \App\Models\FilterView::class);
        Route::model('note', \App\Models\Note::class);
        Route::model('attachfile', \App\Models\AttachFile::class);
        Route::model('event', \App\Models\Event::class);
        Route::model('chatroom', \App\Models\ChatRoom::class);
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapWebRoutes($router);
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        $router->group(['namespace' => $this->namespace, 'middleware' => 'web'], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
