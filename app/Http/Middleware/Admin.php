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

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $permission
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $permission = null)
    {
        if ((! is_null(auth_staff()) && ! auth_staff()->super_admin) || is_null(auth_staff())) {
            // If the auth user doesn't have permission or the auth user's status is inactive or not authenticate.
            if (! permit($permission) || ! auth()->user()->status || is_null(auth_staff())) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response('Unauthorized.', 401);
                } else {
                    if (auth()->check()) {
                        // The inactive auth user will log out and go to the login page.
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
        }

        return $next($request);
    }
}
