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
use App\Library\SystemInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;

class InstallController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('sys.req', ['only' => ['config', 'database', 'import', 'complete']]);
        $this->middleware('import.db', ['only' => ['system', 'config', 'database', 'complete']]);
        $this->middleware('ready', ['only' => ['system', 'config', 'database', 'import']]);
    }

    /**
     * Display system requirement page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function system(Request $request)
    {
        // Page information like title, installation step, and requirement status report.
        $page = [
            'title'         => config('app.item_name') . ' Installation',
            'install_step'  => 'system',
            'multi_section' => true,
        ];

        $requirement = [
            'components'  => SystemInfo::getRequirements()->where('type', 'component'),
            'permissions' => SystemInfo::getRequirements()->where('type', 'directory'),
            'status'      => SystemInfo::getReqStatus(),
        ];

        // Update the installation step.
        session()->forget('install_step');
        $install_step = $this->putInstallationStep(1);

        return view('install.system', compact('page', 'requirement'));
    }

    /**
     * Display the minimum initial system requirement page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function initialRequirement(Request $request)
    {
        $page = [
            'title'         => 'System Requirements',
            'system_info'   => SystemInfo::initialReq(false),
            'multi_section' => true,
        ];

        // Redirect to the home route if the system has no error.
        if ($page['system_info']['status']) {
            return redirect()->route('home');
        }

        // If the directory [/storage/framework] has errors then don't load view just render errors here.
        if (! $page['system_info']['view']) {
            foreach ($page['system_info']['errors'] as $error) {
                echo 'ERROR: ' . $error['message'] . '<br>';
            }

            exit(0);
        }

        return view('install.requirement', compact('page'));
    }

    /**
     * Display the app configuration page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function config(Request $request)
    {
        // Page information like title, installation step, and default app configuration.
        $page = [
            'title'         => config('app.item_name') . ' Installation',
            'install_step'  => 'config',
            'multi_section' => true,
        ];

        $default_data = [
            'app_name'        => config('app.name'),
            'timezone'        => config('app.timezone'),
            'purchase_code'   => null,
            'first_name'      => null,
            'last_name'       => null,
            'email'           => null,
            'mail_driver'     => 'mail',
            'mail_host'       => null,
            'mail_username'   => null,
            'mail_password'   => null,
            'mail_port'       => null,
            'mail_encryption' => null,
        ];

        $form_data        = session()->has('install_config') ? session('install_config') : $default_data;
        $mail_driver_type = in_array($form_data['mail_driver'], ['mail', 'smtp'])
                            ? [$form_data['mail_driver']] : ['smtp', $form_data['mail_driver']];
        $mail_css         = ['smtp' => append_css_class('form-group', 'none', $mail_driver_type, 'smtp', false)];
        $time_zones_list  = ['' => '-None-'] + time_zones_list();
        $follow_step      = $this->installationSteps(1);

        // Redirect if don't follow prev steps.
        if (! $follow_step['status']) {
            return redirect()->route($follow_step['redirect']);
        }

        // Update the installation step.
        $install_step = $this->putInstallationStep(2);

        return view('install.config', compact('page', 'form_data', 'mail_css', 'time_zones_list'));
    }

    /**
     * Post configuration form data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postConfig(Request $request)
    {
        $status         = false;
        $errors         = null;
        $smtp_error_msg = null;
        $license_info   = null;
        $redirect       = null;
        $data           = $request->all();
        $required       = $data['mail_driver'] != 'mail' ? 'required|' : '';

        $rules = [
            'app_name'        => 'required|max:200',
            'timezone'        => 'required|timezone',
            'first_name'      => 'required|max:200',
            'last_name'       => 'required|max:200',
            'email'           => 'required|email',
            'password'        => 'required|min:6|max:60',
            'mail_driver'     => 'required|in:mail,smtp',
            'mail_host'       => $required . 'max:200',
            'mail_username'   => $required . 'max:200',
            'mail_password'   => $required . 'max:200',
            'mail_port'       => $required . 'max:200',
            'mail_encryption' => $required . 'in:tls,ssl',
        ];

        $validation = validator($data, $rules);

        // If validation passes then check license information and system email SMTP server connection.
        if (isset($validation) && $validation->passes()) {
            $install_code = not_null_empty($request->purchase_code) ? $request->purchase_code : 'installation-trial-code';
            $license_info = License::getLicenseInfo($install_code);

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

            // Status true if license info and SMTP info is valid.
            $status = is_array($license_info) && $license_info['status'] && is_null($smtp_error_msg);
        }

        // If validation passes, valid license and SMTP connection then save posted data.
        if ($status) {
            array_forget($data, ['_token']);
            session(['install_config' => $data]);
            session(['license_info' => $license_info]);
            $redirect = route('install.database');

            // Update the installation step.
            $install_step = $this->putInstallationStep(3);
        } else {
            $status = false;
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

            if (! is_null($smtp_error_msg)) {
                $errors['smtp_connection'][] = 'SMTP server connection test failed. <strong>' . str_replace('but got code "", with message ""', '', $smtp_error_msg) . '<strong>';
            }
        }

        return response()->json([
            'status'   => $status,
            'errors'   => $errors,
            'redirect' => $redirect,
            'scroll'   => false,
        ]);
    }

    /**
     * Display database setup form page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function database(Request $request)
    {
        // Page information like title, installation step, and default database setup data.
        $page = [
            'title'         => config('app.item_name') . ' Installation',
            'install_step'  => 'database',
            'multi_section' => true,
        ];

        $default_data = [
            'hostname'      => null,
            'port'          => null,
            'username'      => null,
            'database_name' => null,
        ];

        $form_data    = session()->has('install_database') ? session('install_database') : $default_data;
        $follow_step  = $this->installationSteps(3);

        // Redirect if don't follow prev steps.
        if (! $follow_step['status']) {
            return redirect()->route($follow_step['redirect']);
        }

        return view('install.database', compact('page', 'form_data'));
    }

    /**
     * Post database setup form data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postDatabase(Request $request)
    {
        $status        = true;
        $errors        = null;
        $redirect      = null;
        $realtime_data = [];
        $data          = $request->all();

        $rules = [
            'hostname'      => 'required',
            'port'          => 'required',
            'username'      => 'required',
            'database_name' => 'required',
        ];

        $validation = validator($data, $rules);

        // If validation passes then connect with DB.
        if (isset($validation) && $validation->passes()) {
            try {
                $port = (int) $request->port;
                $connection = new \mysqli(
                    $request->hostname,
                    $request->username,
                    $request->password,
                    $request->database_name,
                    $port
                );
            } catch (\Exception $e) {
                $status = false;
                $errors['mysql_connection'] = ['Can not connect to MySQL server'];
            }

            if ($status) {
                array_forget($data, ['_token']);
                session(['install_database' => $data]);
                $redirect = route('install.import');
                // Update the installation step.
                $install_step = $this->putInstallationStep(4);
            }
        } else {
            $status = false;
            $errors = $validation->getMessageBag()->toArray();
        }

        return response()->json([
            'status'   => $status,
            'errors'   => $errors,
            'realtime' => $realtime_data,
            'redirect' => $redirect,
            'scroll'   => false,
        ]);
    }

    /**
     * Display the database import page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        // Page information like title, installation step, database information, and import progress status.
        $page = [
            'title'           => config('app.item_name') . ' Installation',
            'install_step'    => 'database',
            'database_name'   => session('install_database')['database_name'],
            'is_importing'    => $this->dbStatus('importing'),
            'import_progress' => $this->getImportProgress(),
            'is_ready'        => $this->dbStatus('ready'),
            'multi_section'   => true,
        ];

        $follow_step = $this->installationSteps(4);

        // Redirect if don't follow prev steps.
        if (! $follow_step['status']) {
            return redirect()->route($follow_step['redirect']);
        }

        return view('install.import', compact('page'));
    }

    /**
     * Post database import data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postImport(Request $request)
    {
        // If import status okay then start to import data.
        if (session()->has('install_config') && session()->has('install_database')) {
            create_storage_file('app/importing');

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true]);

            // Update .env file, drop all old data, and import new fresh data.
            $database        = session('install_database');
            $config          = session('install_config');
            $env_data        = ['config' => $config, 'database' => $database];
            $env_status      = $this->writeEnv($env_data);
            $drop_old_tables = $this->dropAllTables($database);
            $import_db       = $this->importDatabase($database);

            create_storage_file('app/imported');
        } else {
            return response()->json(['status' => false, 'error' => 'Something went wrong! Please try again.']);
        }
    }

    /**
     * Get import current status/stage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function importStatus(Request $request)
    {
        if ($this->dbStatus('importing') && session('install_step') == 4) {
            // Update the installation step.
            $install_step = $this->putInstallationStep(5, true);
        }

        // If import data has completed then update progress information to complete.
        if ($this->dbStatus('imported')) {
            unlink(storage_path('app/imported'));
            create_storage_file('app/ready');
            flush_response(['status' => true, 'importProgress' => 100]);
            sleep(5);
            unlink(storage_path('app/importing'));
            $this->primaryDataStore();
        } else {
            // Realtime response according to the import stage.
            $response = [
                'status'         => ! $this->dbStatus('importing') && session('install_step') == 5,
                'importProgress' => $this->getImportProgress(),
            ];

            return response()->json($response);
        }
    }

    /**
     * Display installation complete page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request)
    {
        // Page information like title, installation step, and update status as complete.
        $page = [
            'title'         => config('app.item_name') . ' Installation',
            'install_step'  => 'complete',
            'multi_section' => true,
        ];

        $follow_step = $this->installationSteps(5);

        // Redirect if don't follow prev steps.
        if (! $follow_step['status']) {
            return redirect()->route($follow_step['redirect']);
        }

        if (! db_connection_status()) {
            return redirect()->route('install.database');
        }

        unlink_if_exists(storage_path('app/ready'));

        // When complete installation, create installed file and put license info
        $license_info = session('license_info');
        $license_info['checked_at'] = now()->format('Y-m-d H:i:s');
        $installed = fopen(storage_path('app/installed'), 'w');
        fwrite($installed, json_encode($license_info));
        fclose($installed);

        // Remove all unnecessary installation session values
        session_forget('install_step|install_config|install_database|license_info');

        return view('install.complete', compact('page'));
    }

    /**
     * Follow the previous installation steps.
     *
     * @param int $follow_step
     *
     * @return array
     */
    public function installationSteps($follow_step)
    {
        if (session()->has('install_step')) {
            $install_step = session('install_step');
        } else {
            $install_step = 1;
            session(['install_step' => 1]);
        }

        // If the current step is advanced or equal to the requested following step then return true.
        if ($install_step >= $follow_step) {
            return ['status' => true];
        }

        $outcome['status'] = false;

        // Find out the redirect page.
        switch ($install_step) {
            case 1:
                $outcome['redirect'] = 'install.system';
                break;
            case 2:
                $outcome['redirect'] = 'install.config';
                break;
            case 3:
                $outcome['redirect'] = 'install.database';
                break;
            case 4:
                $outcome['redirect'] = 'install.import';
                break;
            case 5:
                $outcome['redirect'] = 'install.complete';
                break;
            default:
                $outcome['redirect'] = 'install.system';
        }

        return $outcome;
    }

    /**
     * Save the latest installation step in the session.
     *
     * @param int  $this_step
     * @param bool $put
     *
     * @return bool
     */
    public function putInstallationStep($this_step, $put = false)
    {
        if (session()->has('install_step') && session('install_step') == ($this_step - 1)) {
            $put = true;
        }

        if ($put) {
            session()->forget('install_step');
            session(['install_step' => $this_step]);

            return true;
        }

        if (! session()->has('install_step')) {
            session(['install_step' => 1]);

            return true;
        }

        return false;
    }

    /**
     * Write .env file.
     *
     * @param array $env_data
     *
     * @return bool
     */
    public function writeEnv($env_data)
    {
        $file_path     = base_path('.env');
        $file          = fopen($file_path, 'r') or die('Unable to open file!');
        $app_config    = fread($file, filesize($file_path));
        preg_match('/APP_KEY=(.*)\n/', $app_config, $match);
        $app_key       = trim($match[1]);
        fclose($file);
        $database      = $env_data['database'];
        $env_file      = fopen(base_path('.env'), 'w') or die('Unable to open file!');
        $config_string = 'APP_ENV=local
APP_DEBUG=true
APP_KEY=' . $app_key . '

DB_CONNECTION=mysql
DB_HOST=' . $database['hostname'] . '
DB_PORT=' . $database['port'] . '
DB_DATABASE=' . $database['database_name'] . '
DB_USERNAME=' . $database['username'] . '
DB_PASSWORD=' . $database['password'] . '

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

REDIS_HOST=localhost
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=mail
MAIL_HOST=null
MAIL_PORT=null
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
';
        fwrite($env_file, $config_string);
        fclose($env_file);

        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $config = Artisan::call('config:cache');
            $config = Artisan::call('config:clear');
        }

        sleep(1);

        return true;
    }

    /**
     * Import database.
     *
     * @param array $database
     *
     * @return bool
     */
    public function importDatabase($database)
    {
        // Read the initial SQL file.
        $file_path = storage_path('database/initial.sql');
        $file = fopen($file_path, 'r') or die('Unable to open file!');
        $sql = fread($file, filesize($file_path));
        fclose($file);

        // Open and write SQL in import.sql
        $file_path = storage_path('database/import.sql');
        $file = fopen($file_path, 'w') or die('Unable to open file!');
        fwrite($file, $sql);
        fclose($file);

        // Execute SQL command to start importing data.
        $file_path = storage_path('database/import.sql');
        $command   = 'mysql --host=' . $database['hostname'] .
                          ' --user=' . $database['username'] .
                          ' --password=' . $database['password'] .
                          ' --port=' . $database['port'] .
                          ' --database=' . $database['database_name'] .
                          ' < '. $file_path;

        return $this->importSql($database, $file_path);
    }

    /**
     * Execute SQL command line by line and import DB.
     *
     * @param array  $database
     * @param string $file_path
     *
     * @return bool
     */
    public function importSql($database, $file_path)
    {
        // Connect to MySQL.
        $mysqli = new \mysqli(
            $database['hostname'],
            $database['username'],
            $database['password'],
            $database['database_name'],
            $database['port']
        );

        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }

        $templine = '';
        $lines    = file($file_path);
        $scale    = round(count($lines) / 100);

        // Loop through each line to import DB.
        foreach ($lines as $key => $line) {
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }

            $templine .= $line;

            if (substr(trim($line), -1, 1) == ';') {
                $error_msg = "Error performing query '<strong>{$templine}</strong>': {$mysqli->connect_error}<br/>";
                $mysqli->query($templine) or print $error_msg;
                $templine = '';
            }

            if ($key >= $scale && ($key % $scale) == 0) {
                $progress = (int) ($key / $scale);
                $this->setImportProgress(max_value_fixer($progress, 100));
            }
        }

        unlink($file_path);
        sleep(1);

        return true;
    }

    /**
     * Drop all previous tables.
     *
     * @param array $database
     *
     * @return bool
     */
    public function dropAllTables($database)
    {
        $mysqli = new \mysqli(
            $database['hostname'],
            $database['username'],
            $database['password'],
            $database['database_name'],
            $database['port']
        );

        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }

        $mysqli->query('SET foreign_key_checks = 0');

        if ($result = $mysqli->query("SHOW TABLES")) {
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                $mysqli->query('DROP TABLE IF EXISTS '.$row[0]);
            }
        }

        $mysqli->query('SET foreign_key_checks = 1');
        $mysqli->close();

        return true;
    }

    /**
     * Get DB import status.
     *
     * @param string $tracker
     *
     * @return bool
     */
    public function dbStatus($tracker)
    {
        return file_exists(storage_path('app/' . $tracker));
    }

    /**
     * Set the current import stage.
     *
     * @param int $percentage
     *
     * @return void
     */
    public function setImportProgress($percentage)
    {
        if ($this->dbStatus('importing')) {
            $importing = fopen(storage_path('app/importing'), 'w');
            fwrite($importing, $percentage);
            fclose($importing);
        }
    }

    /**
     * Get current import progress.
     *
     * @return int
     */
    public function getImportProgress()
    {
        if ($this->dbStatus('ready')) {
            return 100;
        }

        if ($this->dbStatus('importing')) {
            $progress = file(storage_path('app/importing'));

            return count_if_countable($progress) ? (int) $progress[0] : 0;
        }

        return 0;
    }

    /**
     * Minimal data for primary database setup.
     *
     * @return void
     */
    public function primaryDataStore()
    {
        if (session()->has('install_config')) {
            $config            = session('install_config');
            // Save admin credentials.
            $staff             = new \App\Models\Staff;
            $staff->first_name = $config['first_name'];
            $staff->last_name  = $config['last_name'];
            $staff->save();

            $user              = new \App\Models\User;
            $user->email       = $config['email'];
            $user->password    = bcrypt($config['password']);
            $user->linked_id   = $staff->id;
            $user->linked_type = 'staff';
            $user->save();

            $user->roles()->attach(\App\Models\Role::getAdminRoleId());

            // App configuration saves into DB.
            $data = [
                'app_name'          => $config['app_name'],
                'timezone'          => $config['timezone'],
                'purchase_code'     => array_key_exists('purchase_code', $config) ? encrypt($config['purchase_code']) : null,
                'mail_from_address' => $config['email'],
                'mail_from_name'    => $config['app_name'],
                'mail_driver'       => $config['mail_driver'],
                'mail_host'         => null_if_empty($config['mail_host']),
                'mail_username'     => encrypt_if_has_value($config['mail_username']),
                'mail_password'     => encrypt_if_has_value($config['mail_password']),
                'mail_port'         => null_if_empty($config['mail_port']),
                'mail_encryption'   => null_if_empty($config['mail_encryption']),
            ];

            \App\Models\Setting::mergeSave($data);
            event(new \App\Events\UserCreated($staff, $config));
            \App\Models\Revision::truncate();
            \App\Models\Revision::secureHistory('staff', [1], 'created_at', null, date('Y-m-d H:i:s'), 1);
        }
    }
}
