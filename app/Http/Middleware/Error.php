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

class Error
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $status_code
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $status_code = null)
    {
        if (is_null(cache('error_redirect')) && cache('error_redirect') !== (int) $status_code) {
            if (url()->previous() !== null
                && url()->previous() !== url('404')
                && url()->previous() !== url('500')
            ) {
                return redirect()->to(valid_app_url(url()->previous(), route('home')));
            } else {
                return redirect()->route('home');
            }
        }

        cache()->forget('error_redirect');

        return $next($request);
    }
}
