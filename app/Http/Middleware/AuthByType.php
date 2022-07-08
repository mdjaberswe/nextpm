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

class AuthByType
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $linked_type
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $linked_type = null)
    {
        // If not authenticated user or the auth user's type doesn't match or the auth user's status is inactive.
        if (auth()->guest() || auth()->user()->linked_type != $linked_type || ! auth()->user()->status) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                if (auth()->check()) {
                    if (! auth()->user()->status) {
                        auth()->logout();

                        return redirect()->route('auth.signin');
                    }

                    if (session()->has('url_previous') && session('url_previous') == url()->previous()) {
                        auth()->logout();
                        session()->forget('url_previous');

                        return redirect()->route('auth.signin');
                    }

                    session(['url_previous' => valid_app_url(url()->previous(), route('home'))]);

                    return redirect()->to(valid_app_url(url()->previous(), route('home')));
                } else {
                    return redirect()->route('auth.signin');
                }
            }
        }

        return $next($request);
    }
}
