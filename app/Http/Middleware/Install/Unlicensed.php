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

class Unlicensed
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
        if (config('app.license')['status']) {
            return redirect()->route('home');
        }

        if (auth()->check() && ! auth()->user()->hasRole('administrator')) {
            auth()->logout();

            return redirect()->route('auth.signin')->with('error_msg', 'Purchase code activation is required.');
        }

        return $next($request);
    }
}
