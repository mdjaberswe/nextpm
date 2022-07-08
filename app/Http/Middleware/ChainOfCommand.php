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

class ChainOfCommand
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $action
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $action = null)
    {
        $staff  = $request->route('user');
        $permit = $this->chainLaw($request, $staff, $action);

        if ($permit == true) {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            if (auth()->check()) {
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

    /**
     * Maintain user hierarchy of Super Admin, Admin, normal users.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     * @param string                   $action
     *
     * @return bool
     */
    protected function chainLaw($request, $staff, $action)
    {
        $conclusion = false;

        switch ($action) {
            case 'edit':
                if ($staff->logged_in) {
                    return true;
                } else {
                    $conclusion = $this->secureAdmin($request, $staff);
                }

                break;
            case 'delete':
                if ($staff->logged_in) {
                    return false;
                } else {
                    $conclusion = $this->secureAdmin($request, $staff);
                }

                break;
            default:
                return false;
        }

        return $conclusion;
    }

    /**
     * Secure Admin users from lower hierarchy users.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Staff        $staff
     *
     * @return bool
     */
    protected function secureAdmin($request, $staff)
    {
        // If the auth user is not an admin and the specified user is admin
        // Or, the specified user is Super Admin.
        if ((auth_staff()->admin == false && $staff->admin == true) || $staff->super_admin == true) {
            return false;
        }

        return true;
    }
}
