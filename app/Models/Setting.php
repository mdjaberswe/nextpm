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

namespace App\Models;

use App\Models\Traits\HistoryTrait;

class Setting extends BaseModel
{
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'name', 'value'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = false;

    /**
     * General settings validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function generalSettingValidate($data)
    {
        $rules = [
            'app_name'      => 'required|max:200',
            'logo'          => 'image|mimetypes:image/jpeg,image/png,image/jpg,image/gif|max:3000',
            'dark_logo'     => 'image|mimetypes:image/jpeg,image/png,image/jpg,image/gif|max:3000',
            'favicon'       => 'mimes:jpeg,png,jpg,gif,ico|max:1000',
            'timezone'      => 'required|timezone',
            'purchase_code' => config('app.license_type') == 'free' ? 'max:200' : 'required',
        ];

        $error_messages = ['mimetypes' => ' The image must be a file of type: jpeg, png, gif. '];

        return validator($data, $rules, $error_messages);
    }

    /**
     * Email settings validation.
     *
     * @param array       $data
     * @param string|null $smtp_error_msg
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function emailSettingValidate($data, $smtp_error_msg = null)
    {
        $required = $data['mail_driver'] != 'mail' ? 'required|' : '';

        $rules = [
            'mail_from_address' => 'required|max:200',
            'mail_from_name'    => 'required|max:200',
            'mail_driver'       => 'required|in:mail,smtp',
            'mail_host'         => $required . 'max:200',
            'mail_username'     => $required . 'max:200',
            'mail_password'     => $required . 'max:200',
            'mail_port'         => $required . 'max:200',
            'mail_encryption'   => $required . 'in:tls,ssl',
        ];

        return validator($data, $rules);
    }

    /**
     * Sync with current settings.
     *
     * @param array $data
     *
     * @return void
     */
    public static function mergeSave($data)
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * Set a setting parameter.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return string
     */
    public static function set($key, $value)
    {
        $set_key = self::whereKey($key)->first();

        if (isset($set_key) & is_object($set_key)) {
            $set_key->value = $value;
        } else {
            $set_key        = new self;
            $set_key->key   = $key;
            $set_key->name  = snake_to_ucwords($key);
            $set_key->value = $value;
        }

        $set_key->save();

        return $set_key;
    }

    /**
     * Get settings value.
     *
     * @param string $key
     * @param mixed  $alternative
     *
     * @return mixed
     */
    public static function getVal($key, $alternative = null)
    {
        $key_object = self::whereKey($key)->first();

        if (isset($key_object) && is_object($key_object)) {
            return $key_object->value;
        }

        return $alternative;
    }

    /**
     * Get real-time array data of settings.
     *
     * @return array
     */
    public static function realtimeData()
    {
        return [
            'favicon'   => ['tag' => 'img', 'value' => asset(self::getVal('favicon', 'img/default-favicon.png'))],
            'logo'      => ['tag' => 'img', 'value' => asset(self::getVal('logo', 'img/default-logo.png'))],
            'dark_logo' => ['tag' => 'img', 'value' => asset(self::getVal('dark_logo', 'img/default-dark-logo.png'))],
            'logotxt'   => ['tag' => 'link', 'value' => self::getVal('app_name', config('app.name'))],
        ];
    }
}
