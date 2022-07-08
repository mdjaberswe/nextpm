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

namespace App\Jobs;

use FileHelper;
use App\Models\AttachFile;
use App\Jobs\Job;

class SaveUploadedFile extends Job
{
    protected $uploaded_files;
    protected $linked_type;
    protected $linked_id;
    protected $directory;
    protected $location;

    /**
     * Create a new job instance.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploaded_files
     * @param string                                              $linked_type
     * @param int                                                 $linked_id
     * @param array                                               $directory
     * @param string                                              $location
     *
     * @return void
     */
    public function __construct($uploaded_files, $linked_type, $linked_id, $directory, $location)
    {
        $this->uploaded_files = $uploaded_files;
        $this->linked_type    = $linked_type;
        $this->linked_id      = $linked_id;
        $this->directory      = $directory;
        $this->location       = $location;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Save uploaded file in public/storage directory
        // with details information extension, filesize, related module, etc.
        if (isset($this->uploaded_files) && count($this->uploaded_files)) {
            foreach ($this->uploaded_files as $uploaded_file_name) {
                if (not_null_empty($uploaded_file_name)) {
                    $file_path = $this->location . $uploaded_file_name;
                    $file_path = $this->directory['public'] ? public_path($file_path) : storage_path($file_path);

                    if (file_exists($file_path)) {
                        $path_info = pathinfo($file_path);
                        $file_size = filesize_kb($file_path);
                        $file_name = FileHelper::uploadedFilenameOriginal($uploaded_file_name);

                        if (array_key_exists('extension', $path_info)) {
                            $file              = new AttachFile;
                            $file->name        = $file_name;
                            $file->format      = $path_info['extension'];
                            $file->size        = $file_size;
                            $file->location    = $uploaded_file_name;
                            $file->linked_id   = $this->linked_id;
                            $file->linked_type = $this->linked_type;
                            $file->save();
                        }
                    }
                }
            }
        }
    }
}
