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

use App\Jobs\Job;

class CleanRemovedFile extends Job
{
    protected $removed_files;
    protected $directory;
    protected $location;

    /**
     * Create a new job instance.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $removed_files
     * @param array                                               $directory
     * @param string                                              $location
     *
     * @return void
     */
    public function __construct($removed_files, $directory, $location)
    {
        $this->removed_files = $removed_files;
        $this->directory     = $directory;
        $this->location      = $location;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Delete all requested files.
        if (isset($this->removed_files)) {
            $removed_files = is_array($this->removed_files) ? $this->removed_files : [$this->removed_files];

            foreach ($removed_files as $rmv_file) {
                $removed_file_path = $this->location . $rmv_file;
                unlink_file($removed_file_path, $this->directory['public']);
            }
        }
    }
}
