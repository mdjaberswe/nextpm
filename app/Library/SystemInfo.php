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

class SystemInfo
{
    /**
     * Get minimum initial requirements array.
     *
     * @var array
     */
    protected static $minReq = [
        'php_version', 'mbstring', 'proc_close', 'escapeshellarg', 'storage_app', 'storage_framework',
        'storage_logs', 'bootstrap_cache', 'public',
    ];

    /**
     * Get system requirements information.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRequirements()
    {
        $requirements = [
            [
                'name'       => 'PHP version',
                'status'     => version_compare(PHP_VERSION, '7.0.0', '>='),
                'message'    => 'PHP 7.0.0 or higher is required.',
                'note'       => 'PHP 7.0 or 7.1 recommeded for good performance',
                'short_name' => 'php',
                'key'        => 'php_version',
                'type'       => 'component',
            ],
            [
                'name'       => 'Mysqli Extension',
                'status'     => function_exists('mysqli_connect'),
                'message'    => 'Mysqli Extension is required.',
                'short_name' => 'mysqli',
                'key'        => 'mysqli',
                'type'       => 'component',
            ],
            [
                'name'       => 'OpenSSL Extension',
                'status'     => extension_loaded('openssl'),
                'message'    => 'OpenSSL PHP Extension is required.',
                'short_name' => 'openssl',
                'key'        => 'openssl',
                'type'       => 'component',
            ],
            [
                'name'       => 'Mbstring PHP Extension',
                'status'     => extension_loaded('mbstring'),
                'message'    => 'Mbstring PHP Extension is required.',
                'short_name' => 'mbstring',
                'key'        => 'mbstring',
                'type'       => 'component',
            ],
            [
                'name'       => 'PDO PHP extension',
                'status'     => extension_loaded('pdo'),
                'message'    => 'PDO PHP extension is required.',
                'short_name' => 'pdo',
                'key'        => 'pdo',
                'type'       => 'component',
            ],
            [
                'name'       => 'Tokenizer PHP Extension',
                'status'     => extension_loaded('tokenizer'),
                'message'    => 'Tokenizer PHP Extension is required.',
                'short_name' => 'tokenizer',
                'key'        => 'tokenizer',
                'type'       => 'component',
            ],
            [
                'name'       => 'PHP Zip Archive',
                'status'     => class_exists('ZipArchive', false),
                'message'    => 'PHP Zip Archive is required.',
                'short_name' => 'zip archive',
                'key'        => 'zip_archive',
                'type'       => 'component',
            ],
            [
                'name'       => 'IMAP Extension',
                'status'     => extension_loaded('imap'),
                'message'    => 'PHP IMAP Extension is required.',
                'short_name' => 'imap',
                'key'        => 'imap',
                'type'       => 'component',
            ],
            [
                'name'       => 'PHP GD Library',
                'status'     => extension_loaded('gd') && function_exists('gd_info'),
                'message'    => 'PHP GD Library is required.',
                'short_name' => 'gd library',
                'key'        => 'gd_lib',
                'type'       => 'component',
            ],
            [
                'name'       => 'PHP Fileinfo extension',
                'status'     => extension_loaded('fileinfo'),
                'message'    => 'PHP Fileinfo extension is required.',
                'short_name' => 'fileinfo',
                'key'        => 'fileinfo',
                'type'       => 'component',
            ],
            [
                'name'       => 'PHP CURL extension',
                'status'     => extension_loaded('curl'),
                'message'    => 'PHP CURL extension is required.',
                'short_name' => 'curl',
                'key'        => 'curl',
                'type'       => 'component',
            ],
            [
                'name'       => 'PHP XML extension',
                'status'     => extension_loaded('xml'),
                'message'    => 'PHP XML extension is required.',
                'short_name' => 'xml',
                'key'        => 'xml',
                'type'       => 'component',
            ],
            [
                'name'       => 'proc_close()',
                'status'     => self::getFuncStatus('proc_close'),
                'message'    => 'proc_close() must be enabled.',
                'short_name' => 'proc_close()',
                'key'        => 'proc_close',
                'type'       => 'component',
            ],
            [
                'name'       => 'escapeshellarg()',
                'status'     => self::getFuncStatus('escapeshellarg'),
                'message'    => 'escapeshellarg() must be enabled.',
                'short_name' => 'escapeshellarg()',
                'key'        => 'escapeshellarg',
                'type'       => 'component',
            ],
            [
                'name'       => base_path('storage/app'),
                'status'     => self::getDirPerms('storage/app'),
                'value'      => self::getDirPermsVal('storage/app'),
                'message'    => 'The directory [/storage/app] must be writable by the web server.',
                'short_name' => 'storage/app',
                'key'        => 'storage_app',
                'type'       => 'directory',
            ],
            [
                'name'       => base_path('storage/framework'),
                'status'     => self::getDirPerms('storage/framework'),
                'value'      => self::getDirPermsVal('storage/framework'),
                'message'    => 'The directory [/storage/framework] must be writable by the web server.',
                'short_name' => 'storage/framework',
                'key'        => 'storage_framework',
                'type'       => 'directory',
            ],
            [
                'name'       => base_path('storage/logs'),
                'status'     => self::getDirPerms('storage/logs'),
                'value'      => self::getDirPermsVal('storage/logs'),
                'message'    => 'The directory [/storage/logs] must be writable by the web server.',
                'short_name' => 'storage/logs',
                'key'        => 'storage_logs',
                'type'       => 'directory',
            ],
            [
                'name'       => base_path('bootstrap/cache'),
                'status'     => self::getDirPerms('bootstrap/cache'),
                'value'      => self::getDirPermsVal('bootstrap/cache'),
                'message'    => 'The directory [/bootstrap/cache] must be writable by the web server.',
                'short_name' => 'bootstrap/cache',
                'key'        => 'bootstrap_cache',
                'type'       => 'directory',
            ],
            [
                'name'       => base_path('public/uploads'),
                'status'     => self::getDirPerms('public/uploads'),
                'value'      => self::getDirPermsVal('public/uploads'),
                'message'    => 'The directory [/public/uploads] must be writable by the web server.',
                'short_name' => 'public/uploads',
                'key'        => 'public',
                'type'       => 'directory',
            ],
        ];

        return collect($requirements);
    }

    /**
     * Get system requirement status.
     *
     * @return bool
     */
    public static function getReqStatus()
    {
        return ! self::getRequirements()->where('status', false)->count();
    }

    /**
     * Get minimum initial system requirements.
     *
     * @param bool $quick
     *
     * @return array|bool
     */
    public static function initialReq($quick = false)
    {
        $requirements = self::getRequirements()->whereIn('key', self::$minReq);
        $status = ! $requirements->where('status', false)->count();

        if ($quick || $status) {
            return $status ? compact('status') : $status;
        }

        $view   = $requirements->where('type', 'directory')->pluck('value')->min() >= 500;
        $errors = $requirements->where('status', false);

        return compact('status', 'errors', 'view');
    }

    /**
     * Get directory's permission status.
     *
     * @param string $path
     * @param string $permission readable|writable|executable
     *
     * @return bool
     */
    public static function getDirPerms($path, $permission = 'writable')
    {
        $is_permissible = 'is_' . $permission;
        $path = base_path('/' . $path);

        return file_exists($path) && is_dir($path) && $is_permissible($path);
    }

    /**
     * Get directory's numeric mode permissions.
     *
     * @param $path
     *
     * @return integer
     */
    public static function getDirPermsVal($path)
    {
        $path = base_path('/' . $path);

        return (int) substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Check function enabled or disabled.
     *
     * @param string $function_name
     *
     * @return bool
     */
    public static function getFuncStatus($function_name)
    {
        $disable_functions = explode(',', ini_get('disable_functions'));

        return ! in_array($function_name, $disable_functions);
    }
}
