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

class FileHelper
{
    /**
     * Delete old unnecessary files from the storage directory.
     *
     * @param string $directory
     * @param int    $days
     *
     * @return bool
     */
    public static function cleanOlderFiles($directory, $days = 1)
    {
        $path = storage_path($directory);

        // Delete those files that are modified before 1 day or given $days.
        if (file_exists($path)) {
            $files        = \Storage::disk('base')->files($directory);
            $now          = time();
            $before_limit = 60 * 60 * 24 * $days;

            foreach ($files as $file) {
                $last_modified_time = \Storage::disk('base')->lastModified($file);
                $file_age = $now - $last_modified_time;

                if ($file_age >= $before_limit) {
                    \Storage::disk('base')->delete($file);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Delete a file from the public directory.
     *
     * @param string $file_path
     *
     * @return void
     */
    public static function cleanPublicUploads($file_path)
    {
        if (isset($file_path) && strpos($file_path, 'uploads/') !== false) {
            $file_public_path = public_path($file_path);

            if (file_exists($file_public_path)) {
                unlink($file_public_path);
            }
        }
    }

    /**
     * Generate a unique uploaded file name.
     *
     * @param string $original_name
     *
     * @return string
     */
    public static function generateUploadedFilename($original_name)
    {
        return time() . '_' . str_random(10) . '_' . $original_name;
    }

    /**
     * Get uploaded file original name.
     *
     * @param string $uploaded_filename
     *
     * @return string
     */
    public static function uploadedFilenameOriginal($uploaded_filename)
    {
        $sublen            = strpos($uploaded_filename, '_') + 12;
        $original_filename = substr($uploaded_filename, $sublen);
        $ext               = pathinfo($original_filename, PATHINFO_EXTENSION);

        return empty($ext) ? $uploaded_filename : $original_filename;
    }

    /**
     * Get the file icon by extension.
     *
     * @param string $extension
     *
     * @return string
     */
    public static function getFileIcon($extension)
    {
        $extension = strtolower($extension);

        switch ($extension) {
            case 'webp':
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'gif':
                return "<i class='icon image fa fa-file-image-o'></i>";
            case 'zip':
            case 'rar':
            case 'iso':
            case 'tar':
            case 'tgz':
            case 'apk':
            case 'dmg':
            case '7z':
                return "<i class='icon zip fa fa-file-zip-o'></i>";
            case 'docx':
            case 'doc':
                return "<i class='icon word fa fa-file-word-o'></i>";
            case 'xlsx':
            case 'xls':
            case 'csv':
            case 'ods':
                return "<i class='icon excel fa fa-file-excel-o'></i>";
            case 'pptx':
            case 'pptm':
            case 'ppt':
                return "<i class='icon powerpoint fa fa-file-powerpoint-o'></i>";
            case 'pdf':
                return "<i class='icon pdf fa fa-file-pdf-o'></i>";
            case 'wav':
            case 'wma':
            case 'mpc':
            case 'msv':
                return "<i class='icon audio fa fa-file-audio-o'></i>";
            case 'mp3':
            case 'm4a':
            case 'm4b':
            case 'm4p':
                return "<i class='icon audio fa fa-music'></i>";
            case 'mov':
            case 'mp4':
            case 'avi':
            case 'flv':
            case 'wmv':
            case 'swf':
            case 'mkv':
            case 'mpg':
                return "<i class='icon video fa fa-file-video-o'></i>";
            case 'txt':
                return "<i class='icon text fa fa-file-text-o'></i>";
            case 'html':
            case 'php':
                return "<i class='icon code fa fa-file-code-o'></i>";
            case 'laccdb':
            case 'accdb':
            case 'sql':
            case 'dbs':
            case '4mp':
            case 'ade':
            case 'adp':
            case 'apx':
            case 'wdb':
            case 'db':
                return "<i class='icon code fa fa-database'></i>";
            default:
                return "<i class='icon file fa fa-file-o'></i>";
        }
    }

    /**
     * Upload file in the public directory.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $upload_directory
     * @param string|null                                         $clean_file
     * @param bool                                                $save_path
     *
     * @return array
     */
    public static function filePublicUploads($file, $upload_directory, $clean_file = null, $save_path = true)
    {
        $public_path = public_path($upload_directory);
        $file_name   = time() . '_' . $file->getClientOriginalName();

        if (! file_exists($public_path)) {
            mkdir($public_path, 0777, true);
        }

        $file->move($public_path, $file_name);

        if (! is_null($clean_file)) {
            self::cleanPublicUploads($clean_file);
        }

        return $save_path ? $upload_directory . $file_name : $file_name;
    }
}
