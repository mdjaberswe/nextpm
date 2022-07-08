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

class ErrorController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('error:404', ['only' => ['notFound']]);
        $this->middleware('error:500', ['only' => ['fatal']]);
    }

    /**
     * Display 404 not found error page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function notFound(Request $request)
    {
        $page = ['title' => '404 Page not found'];

        if (auth()->user()->admin) {
            return view('errors.admin.404', compact('page'));
        }
    }

    /**
     * Display 500 internal server error page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function fatal(Request $request)
    {
        $page = ['title' => '500 Internal server error'];

        if (auth()->user()->admin) {
            return view('errors.admin.500', compact('page'));
        }
    }
}
