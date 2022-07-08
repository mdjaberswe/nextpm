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

namespace App\Http\Middleware;

use Closure;

class Demo
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
        if (config('app.mode') == 'demo') {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Error Message: Not allowed in demo version.', 401);
            }

            if (url()->previous() !== null) {
                return redirect()->to(valid_app_url(url()->previous(), route('home')))->with('warning_message', 'Not allowed in demo version.');
            } else {
                return redirect()->route('home')->with('warning_message', 'Not allowed in demo version.');
            }
        }

        return $next($request);
    }
}
