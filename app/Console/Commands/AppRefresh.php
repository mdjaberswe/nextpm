<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh and clear the app';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        /*
        php artisan cache:clear
        composer dump-autoload
        php artisan config:clear
        php artisan cache:clear
        */
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $log_file_path = base_path('storage/logs/laravel.log');
        $log_file      = fopen($log_file_path, 'w') or die('Unable to open file!');
        fwrite($log_file, '');
        fclose($log_file);

        $clear_sessions = $this->storageClear('framework/sessions');
        $clear_uploads  = $this->storageClear('app/attachments');
        $clear_uploads  = $this->storageClear('app/public');
        $clear_uploads  = $this->storageClear('app/staffs');
        $clear_uploads  = $this->storageClear('app/temp');
        $clear_uploads  = $this->publicClear('uploads/app');

        \App\Models\AttachFile::truncate();

        // Artisan::call('config:cache');
    }

    /**
     * Remove files from storage directory.
     *
     * @param string $directory Specific directory in storage
     * @param array  $keep      Don't delete and keep those array files
     *
     * @return bool
     */
    public function storageClear($directory, $keep = ['.gitignore', 'installed'])
    {
        $path = storage_path($directory);

        if (file_exists($path)) {
            $files = \Storage::disk('base')->files($directory);

            foreach ($files as $file) {
                $filename = last(explode('/', $file));

                if (! in_array($filename, $keep)) {
                    \Storage::disk('base')->delete($file);
                }
            }
        }

        return true;
    }

    /**
     * Remove files from public directory.
     *
     * @param string $directory Specific directory in public
     * @param array  $keep      Don't delete and keep those array files
     *
     * @return bool
     */
    public function publicClear($directory, $keep = ['.gitignore'])
    {
        $path = public_path($directory);

        if (file_exists($path)) {
            $files = \File::files($path);

            foreach ($files as $file) {
                $filename = last(explode('/', $file));

                if (! in_array($filename, $keep)) {
                    \File::delete($file);
                }
            }
        }

        return true;
    }
}
