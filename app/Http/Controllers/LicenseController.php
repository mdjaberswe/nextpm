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

use License;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LicenseController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display the app license verification form page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function verification(Request $request)
    {
        $page = [
            'title'         => 'Purchase Code Activation',
            'content_size'  => 'small-box',
            'multi_section' => true,
        ];

        return view('install.verifylicense', compact('page'));
    }

    /**
     * Post license verification form data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postVerification(Request $request)
    {
        $status       = false;
        $errors       = null;
        $redirect     = null;
        $validation   = validator($request->all(), ['purchase_code' => 'required']);

        // Update posted data if validation passes.
        if (isset($validation) && $validation->passes()) {
            $license_info = License::getLicenseInfo($request->purchase_code);

            if ($license_info['status']) {
                $status   = true;
                $redirect = route('home');
                \App\Models\Setting::mergeSave(['purchase_code' => encrypt($request->purchase_code)]);

                // Update license info.
                $license_info['checked_at'] = now()->format('Y-m-d H:i:s');
                $installed = fopen(storage_path('app/installed'), 'w');
                fwrite($installed, json_encode($license_info));
                fclose($installed);
                session()->forget('license_info');
            }
        }

        if ($status == false) {
            $errors = $validation->getMessageBag()->toArray();

            // Get purchase code error from the verification system.
            if (count($errors) == 0 && not_null_empty($request->purchase_code)) {
                if (is_array($license_info) && $license_info['status'] == false) {
                    $errors['purchase_code'][] = $license_info['message'];
                } elseif (! is_array($license_info)) {
                    $errors['purchase_code'][] = has_internet_connection()
                                                 ? 'Something went wrong! Please try again.'
                                                 : 'Your device lost its internet connection.';
                }
            }
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'redirect' => $redirect]);
    }

    /**
     * Remove purchase code from a domain.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return json
     */
    public function deactivate(Request $request)
    {
        if (permit('settings.general')
            && isset($request->deactivate)
            && $request->deactivate == true
            && has_internet_connection()
        ) {
            $purchase_code = config('setting.purchase_code') ? \Crypt::decrypt(config('setting.purchase_code')) : null;
            $deactive_license = License::deactivateCode($purchase_code);

            if (is_array($deactive_license) && array_key_exists('status', $deactive_license)) {
                \App\Models\Setting::mergeSave(['purchase_code' => null]);

                // Update license info after deactivation.
                $deactive_license['checked_at'] = now()->format('Y-m-d H:i:s');
                $installed = fopen(storage_path('app/installed'), 'w');
                fwrite($installed, json_encode($deactive_license));
                fclose($installed);
                session()->forget('license_info');

                return response()->json(['status' => true]);
            }
        }

        return response()->json(['status' => false]);
    }
}
