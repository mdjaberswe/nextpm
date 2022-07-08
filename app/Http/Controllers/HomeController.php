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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('install');
    }

    /**
     * The auth user's initial route after logged in.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $initial_route = auth()->user()->linked->initial_route;

        if (isset($initial_route)) {
            return redirect()->intended(route($initial_route));
        } else {
            auth()->logout();

            return redirect()->route('auth.signin');
        }
    }

    /**
     * Update sidenav status compress|expand.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function setSidenavStatus(Request $request)
    {
        if (isset($request->is_compress)) {
            session(['is_compress' => $request->is_compress]);
        }
    }
}
