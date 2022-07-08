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

namespace App\Http\Middleware\Install;

use Closure;

class ReadyForUse
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->status()) {
            unlink(storage_path('app/ready'));
            create_storage_file('app/installed');
            session_forget('install_step|install_config|install_database');

            return redirect()->route('home');
        }

        return $next($request);
    }

    /**
     * Get ready to use status.
     *
     * @return bool
     */
    public function status()
    {
        return (file_exists(storage_path('app/ready')) && ! file_exists(storage_path('app/importing')));
    }
}
