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
use App\Library\SystemInfo;

class SystemRequirement
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
        if (! $this->status()) {
            return redirect()->route('install.system');
        }

        return $next($request);
    }

    /**
     * Get system requirement status.
     *
     * @return bool
     */
    public function status()
    {
        return SystemInfo::getReqStatus();
    }
}
