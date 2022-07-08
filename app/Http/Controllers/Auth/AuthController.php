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

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'signout']);
        $this->middleware('initial.req', ['except' => 'signout']);
    }

    /**
     * Display the sign-in page to login.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function signin(Request $request)
    {
        $page = ['title' => config('app.name') . ' - Signin', 'multi_section' => true];
        // Refresh intended URL for getting after login URL to go.
        $this->refreshIntendedUrl();

        return view('auth.signin')->withPage($page);
    }

    /**
     * Post user credentials to sign in.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postSignin(Request $request)
    {
        $status     = true;
        $rules      = ['email' => 'required|email', 'password' => 'required|min:6'];
        $validation = validator($request->all(), $rules);

        // If request ajax and validation fails then respond error without loading page.
        if ($request->ajax()) {
            $errors = null;

            if ($validation->fails()) {
                $status = false;
                $errors = $validation->getMessageBag()->toArray();
            }

            return response()->json(['status' => $status, 'errors' => $errors, 'btnDisabled' => $status]);
        }

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation);
        }

        $data     = ['email' => $request->email, 'password' => $request->password, 'status' => 1];
        $remember = isset($request->remember) ? 1 : 0;

        // If the authenticate attempt successful then update the last login and redirect to home.
        if (auth()->attempt($data, $remember)) {
            // Cache data using in sign-in form.
            $user_email = $remember ? $request->email : '';
            cache()->forget('user_email');
            cache()->forget('remember_checked');
            cache()->forever('user_email', $user_email);
            cache()->forever('remember_checked', $remember);
            auth()->user()->update(['last_login' => date('Y-m-d H:i:s')]);

            return redirect()->route('home');
        } else {
            return redirect()->back()->withInput()->with('error_msg', 'Authentication failed!');
        }
    }

    /**
     * The auth user signed out and redirect to the sign-in page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function signout(Request $request)
    {
        auth()->logout();
        session()->flush();

        return redirect()->route('auth.signin');
    }

    /**
     * Update the intended URL.
     *
     * @return void
     */
    private function refreshIntendedUrl()
    {
        session()->forget('url.intended');

        if (not_null_empty(url()->previous())
            && ! in_array(url()->previous(), [route('404'), route('500')])
        ) {
            session(['url.intended' => valid_app_url(url()->previous(), route('home'))]);
        }
    }
}
