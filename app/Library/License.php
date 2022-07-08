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

namespace App\Library;

class License
{
    const ITEM_NAME    = 'nextpm';

    /**
     * Get license information.
     *
     * @param string $purchase_code
     * @param string $request_type  post|put
     * @param array  $info
     *
     * @return array
     */
    public static function getLicenseInfo($purchase_code, $request_type = 'post', $info = [])
    {
        $info['idays'] = self::getInstalledDays();
        $data = [
            'item'          => self::ITEM_NAME,
            'domain'        => url('/'),
            'purchase_code' => $purchase_code,
            'request_type'  => $request_type,
            'info'          => $info,
        ];

        if (config('app.license_type') == 'free') {
            return config('app.license_free');
        }

        return \Curl::to(config('app.license_verifier'))
                    ->withData($data)
                    ->asJson(true)
                    ->get();
    }

    /**
     * Deactivate purchase code from this domain.
     *
     * @param string $purchase_code
     *
     * @return array
     */
    public static function deactivateCode($purchase_code)
    {
        $data = [
            'item'          => self::ITEM_NAME,
            'domain'        => url('/'),
            'purchase_code' => $purchase_code,
            'request_type'  => 'delete',
            'info'          => self::getInstalledInfo(),
        ];

        if (config('app.license_type') == 'free') {
            return config('app.license_free');
        }

        return \Curl::to(config('app.license_deactivate'))
                    ->withData($data)
                    ->asJson(true)
                    ->get();
    }

    /**
     * Get app installation information
     *
     * @return array
     */
    public static function getInstalledInfo()
    {
        if (config('app.license_type') == 'free') {
            return config('app.license_free');
        } elseif (file_exists(storage_path('app/installed'))) {
            $info = \Storage::disk('local')->get('installed');
            $info = json_decode($info, true);
            $info['idays'] = self::getInstalledDays();

            return $info;
        }

        return ['status' => false];
    }

    /**
     * Get app installation days
     *
     * @return integer
     */
    public static function getInstalledDays()
    {
        if (file_exists(storage_path('app/installed')) && db_connection_status() && \Schema::hasTable('settings')) {
            return \App\Models\User::orderBy('created_at')->first()->created_at->diffInDays(now(), false);
        }

        return 0;
    }

    /**
     * Get the purchase code.
     *
     * @return string
     */
    public static function getPurchaseCode()
    {
        return config('setting.purchase_code') ? \Crypt::decrypt(config('setting.purchase_code')) : null;
    }

    public static function regularCheckup()
    {
        $license_info  = self::getInstalledInfo();
        $purchase_code = config('setting.purchase_code') ? \Crypt::decrypt(config('setting.purchase_code')) : false;
        $license_info  = self::getLicenseInfo($purchase_code, 'post', ['warn_no' => $license_info['warn_no']]);

        // Update license info.
        $license_info['checked_at'] = now()->format('Y-m-d H:i:s');
        session(['license_info' => $license_info]);
        $installed = fopen(storage_path('app/installed'), 'w');
        fwrite($installed, json_encode($license_info));
        fclose($installed);

        return true;
    }
}
