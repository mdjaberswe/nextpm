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

namespace App\Http\Controllers\Admin;

use License;
use FileHelper;
use App\Models\Staff;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminSettingController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:settings.general', ['only' => ['index', 'postGeneral']]);
        $this->middleware('admin:settings.email', ['only' => ['email', 'postEmail']]);

        // Demo mode middleware
        $this->middleware('demo', ['only' => ['postGeneral', 'postEmail']]);
    }

    /**
     * Show the form to edit general settings.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = ['title' => 'General Settings', 'multi_section' => true];

        return view('admin.setting.general', compact('page'));
    }

    /**
     * Post and update general settings.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postGeneral(Request $request)
    {
        $status       = true;
        $realtime     = [];
        $removecss    = [];
        $validation   = Setting::generalSettingValidate($request->all());
        $errors       = $validation->getMessageBag()->toArray();
        $present_code = config('setting.purchase_code') ? \Crypt::decrypt(config('setting.purchase_code')) : false;
        $license_info = count($errors) == 0 ? License::getLicenseInfo($request->purchase_code, 'put', ['purchase_code' => $present_code]) : ['status' => true];

        // Update posted data if validation passes.
        if (isset($validation) && $validation->passes() && $license_info['status']) {
            $data = [
                'app_name'      => $request->app_name,
                'timezone'      => $request->timezone,
                'purchase_code' => encrypt($request->purchase_code),
            ];

            if (isset($request->logo) && $request->logo != '') {
                $data['logo'] = FileHelper::filePublicUploads($request->file('logo'), 'uploads/app/', config('setting.logo'));
            }

            if (isset($request->dark_logo) && $request->dark_logo != '') {
                $data['dark_logo'] = FileHelper::filePublicUploads($request->file('dark_logo'), 'uploads/app/', config('setting.dark_logo'));
            }

            if (isset($request->favicon) && $request->favicon != '') {
                $data['favicon'] = FileHelper::filePublicUploads($request->file('favicon'), 'uploads/app/', config('setting.favicon'));
            }

            // Update license info.
            $license_info['checked_at'] = now()->format('Y-m-d H:i:s');
            $installed = fopen(storage_path('app/installed'), 'w');
            fwrite($installed, json_encode($license_info));
            fclose($installed);
            session()->forget('license_info');

            Setting::mergeSave($data);
            $realtime = Setting::realtimeData();
            $removecss[] = ['.deactive-license', 'disabled'];
        } else {
            $status = false;

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

        return response()->json([
            'status'    => $status,
            'errors'    => $errors,
            'realtime'  => $realtime,
            'removeCss' => $removecss,
        ]);
    }

    /**
     * Show the form to edit email settings.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function email(Request $request)
    {
        $page = ['title' => 'Email Settings', 'multi_section' => true];
        $data = [
            'mail_from_name'    => config('setting.mail_from_name')
                                   ? config('setting.mail_from_name') : config('setting.app_name'),
            'mail_from_address' => config('setting.mail_from_address')
                                   ? config('setting.mail_from_address') : Staff::superAdmin()->email,
            'mail_driver'       => config('setting.mail_driver'),
            'mail_driver_type'  => in_array(config('setting.mail_driver'), ['mail', 'smtp'])
                                   ? [config('setting.mail_driver')] : ['smtp', config('setting.mail_driver')],
        ];

        return view('admin.setting.email', compact('page', 'data'));
    }

    /**
     * Post and update email settings.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postEmail(Request $request)
    {
        $status = false;
        $errors = null;
        $smtp_error_msg = null;
        $validation = Setting::emailSettingValidate($request->all());

        // Email settings validation and SMTP connection test check.
        if (isset($validation) && $validation->passes()) {
            if ($request->mail_driver == 'smtp') {
                try {
                    $transport = \Swift_SmtpTransport::newInstance($request->mail_host, $request->mail_port, $request->mail_encryption);
                    $transport->setUsername($request->mail_username);
                    $transport->setPassword($request->mail_password);
                    $mailer = \Swift_Mailer::newInstance($transport);
                    $mailer->getTransport()->start();
                } catch (\Swift_TransportException $e) {
                    if ($e->getMessage() != '') {
                        $smtp_error_msg = $e->getMessage();
                    }
                } catch (\Exception $e) {
                    if ($e->getMessage() != '') {
                        $smtp_error_msg = $e->getMessage();
                    }
                }
            }

            $status = is_null($smtp_error_msg);
        }

        // If validation passes and SMTP connection successfully established then store email settings parameter.
        if ($status) {
            $data = [
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name'    => $request->mail_from_name,
                'mail_driver'       => $request->mail_driver,
                'mail_host'         => null_if_empty($request->mail_host),
                'mail_username'     => encrypt_if_has_value($request->mail_username),
                'mail_password'     => encrypt_if_has_value($request->mail_password),
                'mail_port'         => null_if_empty($request->mail_port),
                'mail_encryption'   => null_if_empty($request->mail_encryption),
            ];

            Setting::mergeSave($data);
        } else {
            $errors = $validation->getMessageBag()->toArray();

            if (! is_null($smtp_error_msg)) {
                $errors['smtp_connection'][] = 'SMTP server connection test failed. <strong>' . str_replace('but got code "", with message ""', '', $smtp_error_msg) . '<strong>';
            }
        }

        return response()->json(['status' => $status, 'errors' => $errors]);
    }
}
