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

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \HighIdeas\UsersOnline\Middleware\UsersOnline::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'          => \App\Http\Middleware\Authenticate::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can'           => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'role'          => \Zizaco\Entrust\Middleware\EntrustRole::class,
        'permission'    => \Zizaco\Entrust\Middleware\EntrustPermission::class,
        'ability'       => \Zizaco\Entrust\Middleware\EntrustAbility::class,
        'auth.type'     => \App\Http\Middleware\AuthByType::class,
        'admin'         => \App\Http\Middleware\Admin::class,
        'command.chain' => \App\Http\Middleware\ChainOfCommand::class,
        'error'         => \App\Http\Middleware\Error::class,
        'demo'          => \App\Http\Middleware\Demo::class,
        'install'       => \App\Http\Middleware\Install\Install::class,
        'uninstall'     => \App\Http\Middleware\Install\Uninstall::class,
        'initial.req'   => \App\Http\Middleware\Install\InitialRequirement::class,
        'sys.req'       => \App\Http\Middleware\Install\SystemRequirement::class,
        'import.db'     => \App\Http\Middleware\Install\ImportDB::class,
        'ready'         => \App\Http\Middleware\Install\ReadyForUse::class,
        'licensed'      => \App\Http\Middleware\Install\License::class,
        'unlicensed'    => \App\Http\Middleware\Install\Unlicensed::class,
    ];
}
