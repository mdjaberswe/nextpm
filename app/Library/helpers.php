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

/**
 * Value can not be null or empty.
 *
 * @param mixed $val
 *
 * @return bool
 */
function not_null_empty($val)
{
    return ! is_null($val) && ! empty($val);
}

/**
 * Value either can be a null or empty string.
 *
 * @param null|string $val
 *
 * @return bool
 */
function null_or_empty($val)
{
    return is_null($val) || empty($val);
}

/**
 * Fill up space.
 *
 * @param string $str
 *
 * @return string
 */
function fill_up_space($str)
{
    return str_replace(' ', '&nbsp;', $str);
}

/**
 * User permission checker.
 *
 * @param string $permission
 *
 * @return bool
 */
function permit($permission)
{
    return auth()->user()->can($permission);
}

/**
 * Get country names and codes list.
 *
 * @return array
 */
function countries_list()
{
    return collect(array_values(countries()))->sortBy('name')->pluck('name', 'iso_3166_1_alpha2')->toArray();
}

/**
 * A long string of country codes joins by commas.
 *
 * @return string
 */
function valid_country_code()
{
    return implode(',', array_keys(countries_list()));
}

/**
 * Get country name from code.
 *
 * @param string $country_code
 *
 * @return string
 */
function country_code_to_name($country_code)
{
    if (array_key_exists($country_code, countries_list())) {
        return countries_list()[$country_code];
    }

    return null;
}

/**
 * Convert native emoji shortcodes into HTML entities.
 *
 * @param string $str
 *
 * @return string
 */
function emoji($str)
{
    return \LitEmoji\LitEmoji::encodeHtml($str);
}

/**
 * Push a complete array of elements in an array.
 *
 * @param array $array_container
 * @param array $new_array
 *
 * @return array
 */
function push_flatten($array_container, $new_array)
{
    array_push($array_container, $new_array);

    return array_flatten($array_container);
}

/**
 * Get data from a model with the help of morph name and ids array.
 *
 * @param string $morph
 * @param array  $ids
 * @param array  $not_ids
 *
 * @return \Illuminate\Database\Eloquent\Collection|static[]
 */
function get_wherein($morph, $ids, $not_ids = [0])
{
    $model = morph_to_model($morph);

    return $model::orderBy('id')->whereIn('id', $ids)->whereNotIn('id', $not_ids)->get();
}

/**
 * Merge collection arrays data.
 *
 * @param array $collection_array
 *
 * @return \Illuminate\Support\Collection
 */
function collection_merge($collection_array = [])
{
    $outcome = collect();

    if (count($collection_array)) {
        foreach ($collection_array as $collection) {
            $outcome = $outcome->merge($collection);
        }
    }

    return $outcome;
}

/**
 * Get Moment.js compatible timestamp.
 *
 * @param datetime $date
 *
 * @return int
 */
function moment_timestamp($date)
{
    return strtotime($date) * 1000;
}

/**
 * Forget multiple sessions.
 *
 * @param string $session_keys
 *
 * @return bool
 */
function session_forget($session_keys)
{
    $session_keys = explode('|', $session_keys);

    foreach ($session_keys as $key) {
        session()->forget($key);
    }

    return true;
}

/**
 * Create a file in the storage directory.
 *
 * @param string $file_path
 *
 * @return bool
 */
function create_storage_file($file_path)
{
    $file_path = storage_path($file_path);
    $file      = fopen($file_path, 'w') or die('Unable to open file!');
    fwrite($file, '');
    fclose($file);

    return true;
}

/**
 * Delete the file if it is found.
 *
 * @param string $file_path
 *
 * @return bool
 */
function unlink_if_exists($file_path)
{
    if (file_exists($file_path)) {
        unlink($file_path);

        return true;
    }

    return false;
}

/**
 * Get to know if a there is a route matching a certain URL.
 *
 * @param string $url
 *
 * @return bool
 */
function url_has_route($url)
{
    $status  = true;
    $routes  = \Route::getRoutes();
    $request = \Request::create($url);

    try {
        $routes->match($request);
        // route exists
    }
    catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
        $status = false;
        // route doesn't exist
    }

    return $status;
}

/**
 * Get to know a URL is valid app URL or not.
 *
 * @param string $url
 * @param string $alternate_url
 *
 * @return string
 */
function valid_app_url($url, $alternate_url)
{
    $status   = true;
    $base_url = url('/');

    if (str_contains($url, $base_url)) {
        $url_param = str_replace($base_url . '/', '', $url);
        $status    = url_has_route($url_param);
    } else {
        $status = false;
    }

    return $status ? $url : $alternate_url;
}

/**
 * The readable text format of the access field value.
 *
 * @param string $db_access
 *
 * @return string|null
 */
function readable_access($db_access)
{
    return \Dropdown::readableAccess($db_access);
}

/**
 * Get page layout status.
 *
 * @return array
 */
function get_layout_status()
{
    $outcome            = [];
    $outcome['logo']    = '';
    $outcome['nav']     = '';
    $outcome['top_nav'] = '';
    $outcome['main']    = '';

    // If the auth user compresses side nav, then add the following classes according to HTML tags.
    if (session()->has('is_compress') && session('is_compress') == true) {
        $outcome['logo']    = 'compress';
        $outcome['nav']     = 'compress';
        $outcome['top_nav'] = 'expand';
        $outcome['main']    = 'expand';
    }

    return $outcome;
}

/**
 * Check the active menu by the current route.
 *
 * @param array|string $identifier
 *
 * @return string|null
 */
function active_menu($identifier)
{
    $current_route = \Route::getCurrentRoute()->getName();
    $outcome       = null;

    // If $identifier is an array format, then the "except" element should not equal to current route
    // else formatted string identifier convert to an array and
    // the menu will be active if one of the array elements satisfied the condition.
    if (is_array($identifier)) {
        $except = $identifier['except'];
        $common = $identifier['common'];

        if ($except !== $current_route) {
            if (strpos($current_route, $common) !== false) {
                $outcome = 'active';
            }
        }
    } else {
        $identifier_array = explode('|', $identifier);

        foreach ($identifier_array as $single_identifier) {
            if (strpos($current_route, $single_identifier) !== false) {
                $outcome = 'active';
            }
        }
    }

    return $outcome;
}

/**
 * Active menu arrow position.
 *
 * @param string $identifier
 *
 * @return string|null
 */
function active_menu_arrow($identifier)
{
    $current_route = \Route::getCurrentRoute()->getName();
    $outcome       = null;

    // Add "down" CSS class for indicating all dropdown child elements of the active menu.
    if (strpos($current_route, $identifier) !== false) {
        $outcome = 'down';
    }

    return $outcome;
}

/**
 * Menu dropdown child tree show or hide.
 *
 * @param string $identifier
 * @param string $nav_status
 *
 * @return string|null
 */
function active_tree($identifier, $nav_status)
{
    if ($nav_status == 'compress') {
        return null;
    }

    $current_route = \Route::getCurrentRoute()->getName();
    $outcome       = null;

    // Show all dropdown child elements of the active menu.
    if (strpos($current_route, $identifier) !== false) {
        $outcome = "style='display: block;'";
    }

    return $outcome;
}

/**
 * Render breadcrumb HTML.
 *
 * @param string $str_breadcrumb
 *
 * @return string
 */
function breadcrumb($str_breadcrumb)
{
    return \HtmlElement::renderBreadcrumb($str_breadcrumb);
}

/**
 * Ajax quick response for not delaying execution.
 *
 * @param array $response
 *
 * @return void
 */
function flush_response($response = [])
{
    ignore_user_abort(true);
    set_time_limit(0);
    ob_start();
    echo json_encode($response);
    header('Connection: close');
    header('Content-Length: '.ob_get_length());
    ob_end_flush();

    //  if an output buffer is active
    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
}

/**
 * URL or Domain Validation.
 *
 * @param string $url
 *
 * @return bool
 */
function valid_url_or_domain($url)
{
    $url_info  = parse_url(filter_var($url, FILTER_SANITIZE_URL));
    $valid_url = domain_to_url($url);

    if (filter_var($valid_url, FILTER_VALIDATE_URL) !== false) {
        return true;
    } elseif (filter_var(gethostbyname($url), FILTER_VALIDATE_IP)) {
        return true;
    } elseif (array_key_exists('host', $url_info) && filter_var(gethostbyname($url_info['host']), FILTER_VALIDATE_IP)) {
        return true;
    } elseif (array_key_exists('path', $url_info) && filter_var(gethostbyname($url_info['path']), FILTER_VALIDATE_IP)) {
        return true;
    }

    return false;
}

/**
 * Get the URL title.
 *
 * @param string $url
 *
 * @return string
 */
function get_url_title($url)
{
    $contents = @file_get_contents($url) or '';

    if (strlen($contents)) {
        $contents = trim(preg_replace('/\s+/', ' ', $contents));
        preg_match("/\<title\>(.*)\<\/title\>/i", $contents, $title);

        return $title[1];
    }

    return url_to_domain($url);
}

/**
 * Generate a quick URL.
 *
 * @param string $url
 *
 * @return string
 */
function quick_url($url)
{
    $http = substr($url, 0, 4);

    if ($http != 'http') {
        $url = 'http://' . $url;
    }

    return $url;
}

/**
 * Get URL from a domain.
 *
 * @param string $domain
 * @param bool   $force_http
 *
 * @return string
 */
function domain_to_url($domain, $force_http = false)
{
    if (filter_var($domain, FILTER_VALIDATE_URL) !== false) {
        return $domain;
    }

    $url_info = parse_url(filter_var($domain, FILTER_SANITIZE_URL));

    if (! isset($url_info['host'])) {
        $url_info['host'] = $url_info['path'];
    }

    if ($url_info['host'] != '') {
        if (! isset($url_info['scheme'])) {
            $url_info['scheme'] = 'http';
        }

        if ((checkdnsrr($url_info['host'], 'A')
            && in_array($url_info['scheme'], ['http','https'])
            && ip2long($url_info['host']) === false)
            || $force_http == true
        ) {
            $url_info['host'] = preg_replace('/^www\./', '', $url_info['host']);
            $url              = $url_info['scheme'] . '://' . $url_info['host'] . '/';

            return $url;
        }
    }

    return $domain;
}

/**
 * Get a domain from a URL.
 *
 * @param string $url
 *
 * @return string
 */
function url_to_domain($url)
{
    $url = trim($url, '/');

    if (! preg_match('#^http(s)?://#', $url)) {
        $url = 'http://' . $url;
    }

    $url_info = parse_url($url);
    $domain   = preg_replace('/^www\./', '', $url_info['host']);

    return $domain;
}

/**
 * Delete a file from the public or storage directory.
 *
 * @param string $file_path
 * @param bool   $public
 *
 * @return void
 */
function unlink_file($file_path, $public)
{
    if ($public) {
        \File::delete($file_path);
    } else {
        \Storage::disk('base')->delete($file_path);
    }
}

/**
 * Get authenticated staff typed user.
 *
 * @return \App\Models\Staff|null
 */
function auth_staff()
{
    if (auth()->check() && auth()->user()->linked_type == 'staff' && isset(auth()->user()->linked)) {
        return auth()->user()->linked;
    }

    return null;
}

/**
 * Get Model class name from morph.
 *
 * @param string $morph
 *
 * @return string
 */
function morph_to_model($morph)
{
    $model = str_replace('_', ' ', $morph);
    $model = ucwords($model);
    $model = '\App\Models\ ' . $model;
    $model = str_replace(' ', '', $model);

    return $model;
}

/**
 * Get database status.
 *
 * @return bool
 */
function db_connection_status()
{
    $status = true;

    try {
        \DB::connection()->getPdo();
    } catch (\Exception $e) {
        $status = false;
    }

    return $status;
}

/**
 * Check the internet connection status.
 *
 * @return bool
 */
function has_internet_connection()
{
    $connected = @fsockopen('www.google.com', 80, $errno, $errstr, 30);

    if ($connected) {
        $has_connection = true;
        fclose($connected);
    } else {
        $has_connection = false;
    }

    return $has_connection;
}

/**
 * Set DB table column name value and key as configuration value.
 *
 * @param string $table_name
 *
 * @return void
 */
function table_config_set($table_name)
{
    if (db_connection_status() && \Schema::hasTable($table_name)) {
        $morph = substr($table_name, 0, -1);
        config()->set($morph, morph_to_model($morph)::pluck('value', 'key')->all());
    }
}

/**
 * The view exists checker.
 *
 * @param string      $view
 * @param string|null $prefix
 *
 * @return void
 */
function view_exists($view, $prefix = null)
{
    if (! is_null($prefix) && view()->exists($prefix . '.' . $view)) {
        return ['status' => true, 'content' => $prefix . '.' . $view];
    }

    return ['status' => view()->exists($view), 'content' => $view];
}

/**
 * Override configuration value.
 *
 * @param string     $default
 * @param string     $override
 * @param array|null $can_override
 * @param array      $encrypt_keys
 *
 * @return void
 */
function override_config($default, $override, $can_override = null, $encrypt_keys = [])
{
    // If not null config $default and $override both.
    if (! is_null(config($default)) && ! is_null(config($override))) {
        $config_override = [];

        // If $can_override is an array then only override $can_override array
        // else override all iff any of key format exists.
        if (is_array($can_override)) {
            foreach ($can_override as $default_key => $override_key) {
                $config_override[$default . '.' . $default_key] = config($override . '.' . $override_key);
            }
        } else {
            $default_keys  = array_keys(config($default));
            $override_keys = array_keys(config($override));

            foreach ($default_keys as $default_key) {
                $key_exist       = in_array($default_key, $override_keys);
                $snake_key       = $default . '_' . str_replace('.', '_', $default_key);
                $snake_key_exist = in_array($snake_key, $override_keys);

                if ($snake_key_exist) {
                    $value = config($override . '.' . $snake_key);
                } elseif ($key_exist) {
                    $value = config($override . '.' . $default_key);
                }

                if ($key_exist || $snake_key_exist) {
                    $value = in_array($default_key, $encrypt_keys) ? check_before_decrypt($value) : $value;
                    $config_override[$default . '.' . $default_key] = $value;
                }
            }
        }

        if (count($config_override)) {
            config($config_override);

            // Exceptions
            if ($default == 'app') {
                if (array_key_exists('app.timezone', $config_override)) {
                    date_default_timezone_set(config('app.timezone'));
                }
            } elseif ($default == 'mail') {
                config(['mail.from.address' => config('setting.mail_from_address')]);
                config(['mail.from.name' => config('setting.mail_from_name')]);
            }
        }
    }
}

/**
 * Rename array keys.
 *
 * @param array $array
 * @param array $keys
 *
 * @return array
 */
function rename_array_key($array, $keys)
{
    $json = json_encode($array);

    foreach ($keys as $current_key => $new_key) {
        $json = str_replace('"' . $current_key . '":', '"' . $new_key . '":', $json);
    }

    return json_decode($json, true);
}

/**
 * Map array with rename keys.
 *
 * @param array  $array
 * @param string $valFormat
 * @param array  $keys
 *
 * @return array
 */
function array_map_with_keys($array, $valFormat, $keys = [])
{
    $outcome = [];
    $keys    = count($keys) ? $keys : $array;
    $array   = array_map($valFormat, $array);

    foreach ($array as $pos => $val) {
        $outcome[$keys[$pos]] = $val;
    }

    return $outcome;
}

/**
 * Delete array elements by keys.
 *
 * @param array $array
 * @param array $forget_keys
 *
 * @return array
 */
function array_forget_keys($array, $forget_keys)
{
    foreach ($forget_keys as $forget_key) {
        array_forget($array, $forget_key);
    }

    return $array;
}

/**
 * Array element replaces with a new one.
 *
 * @param array $array
 * @param mixed $target
 * @param mixed $replace
 *
 * @return array
 */
function array_element_replace($array, $target, $replace)
{
    if (is_array($array)) {
        $search = array_search($target, $array);

        if (is_int($search)) {
            $array[$search] = $replace;
        }

        return $array;
    }

    return $array;
}

/**
 * Get readable date format.
 *
 * @param string $date
 * @param string $format
 *
 * @return string|null
 */
function readable_date($date, $format = 'M j, Y')
{
    if (not_null_empty($date)) {
        return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format($format);
    }

    return null;
}

/**
 * Validate the date with a specific given format.
 *
 * @param string $date
 * @param string $format
 *
 * @return bool
 */
function validateDateFormat($date, $format = 'Y-m-d H:i:s')
{
    $obj_date = \DateTime::createFromFormat($format, $date);

    return $obj_date && $obj_date->format($format) === $date;
}

/**
 * AmPm date format to SQL supported DateTime format.
 *
 * @param string $ampm
 *
 * @return string
 */
function ampm_to_sql_datetime($ampm)
{
    $divider   = strpos($ampm, ' ');
    $date      = substr($ampm, 0, $divider);
    $time      = substr($ampm, $divider + 1);
    $strtotime = strtotime($time);
    $sql_time  = date('G:i:s', $strtotime);

    return $date . ' ' . $sql_time;
}

/**
 * Time short readable format.
 *
 * @param string $diffForHumans
 *
 * @return string
 */
function time_short_form($diffForHumans)
{
    $diffForHumans = str_replace(['seconds', 'second'], 'sec', $diffForHumans);
    $diffForHumans = str_replace(['minutes', 'minute'], 'min', $diffForHumans);
    $diffForHumans = str_replace('hour', 'hr', $diffForHumans);
    $diffForHumans = str_replace('hours', 'hrs', $diffForHumans);

    if (strpos($diffForHumans, 'from now') !== false) {
        $diffForHumans = 'in ' . str_replace('from now', '', $diffForHumans);
    }

    return $diffForHumans;
}

/**
 * Check any one of the array words exists in a sentence or not.
 *
 * @param array  $arr
 * @param string $sentence
 *
 * @return bool
 */
function strpos_array($arr, $sentence)
{
    $arr = ! is_array($arr) ? array($arr) : $arr;

    foreach ($arr as $word) {
        if (strpos($sentence, $word) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Get readable timezone list array.
 *
 * @return array
 */
function time_zones_list()
{
    $time_zones      = timezone_identifiers_list();
    $time_zones_list = [];

    // Make a timezone list array where
    // standard timezone name in the key "timezone_val"
    // and city name with time differences of GMT in the key "timezone_display"
    // also sorted the list according to minus plus differences of GMT.
    foreach ($time_zones as $key => $timezone) {
        date_default_timezone_set($timezone);
        $timezone_apart        = explode('/', $timezone);
        $city                  = end($timezone_apart);
        $city                  = str_replace('_', ' ', $city);
        $diff_gmt              = date('P', time());
        $time_zones_list[$key] = [
            'timezone_val'     => $timezone,
            'timezone_display' => '(GMT'. $diff_gmt .') '. trim($city),
        ];
    }

    date_default_timezone_set(config('app.timezone'));
    $time_zones_list  = collect($time_zones_list);

    $minus_time_zones = $time_zones_list->filter(function ($value, $key) {
        return (strpos($value['timezone_display'], 'GMT-') !== false);
    })->sortByDesc('timezone_display');

    $plus_time_zones = $time_zones_list->filter(function ($value, $key) {
        return (strpos($value['timezone_display'], 'GMT+') !== false);
    })->sortBy('timezone_display');

    $time_zones_list = $minus_time_zones->merge($plus_time_zones);

    return $time_zones_list->pluck('timezone_display', 'timezone_val')->toArray();
}

/**
 * Check object has the property or not.
 *
 * @param object $object
 * @param string $property
 *
 * @return mixed
 */
function non_property_checker($object = null, $property = null)
{
    if (is_null($object) || ! is_object($object) || is_null($property)) {
        return null;
    }

    return $object->$property;
}

/**
 * Get date after/before given days.
 *
 * @param int    $days
 * @param string $from_date
 * @param bool   $sign
 *
 * @return string
 */
function get_date_from($days, $from_date = null, $sign = true)
{
    if ($from_date == null) {
        $from_date = date('Y-m-d');
    }

    $time = strtotime($from_date);
    $sign = $sign == true ? '+' : '-';
    $date = strtotime($sign . $days . ' days', $time);

    return date('Y-m-d', $date);
}

/**
 * Get the non-negative number.
 *
 * @param numeric $num
 *
 * @return numeric
 */
function min_zero($num)
{
    if ($num < 0) {
        return 0;
    }

    return $num;
}

/**
 * Set a max value.
 *
 * @param numeric $num
 * @param numeric $max
 *
 * @return numeric
 */
function max_value_fixer($num, $max)
{
    if ($num > $max) {
        return $max;
    }

    return $num;
}

/**
 * Get file size in Kb.
 *
 * @param string $file_path
 *
 * @return numeric
 */
function filesize_kb($file_path)
{
    $file_size_bytes = filesize($file_path);
    $file_size_kb    = number_format(($file_size_bytes / 1024), 2, '.', '') + 0;

    return $file_size_kb;
}

/**
 * Readable file size.
 *
 * @param string $kilobytes
 *
 * @return string
 */
function readable_filesize($kilobytes)
{
    $bytes = $kilobytes * 1024;

    if ($bytes < 1048576) {
        return $kilobytes . ' KB';
    }

    if ($bytes >= 1073741824) {
        $outcome = number_format($bytes / 1073741824, 2) . ' GB';
    } else {
        $outcome = number_format($bytes / 1048576, 2) . ' MB';
    }

    return $outcome;
}

/**
 * Get null if the value is empty.
 *
 * @param mixed $val
 *
 * @return mixed|null
 */
function null_if_empty($val = null)
{
    if (isset($val) && $val !== '') {
        return $val;
    }

    return null;
}

/**
 * Replace the array element with null if the element is empty.
 *
 * @param array $arr
 *
 * @return array
 */
function replace_null_if_empty($arr)
{
    foreach ($arr as $key => $val) {
        $arr[$key] = (isset($val) && $val !== '') ? $arr[$key] : null;
    }

    return $arr;
}

/**
 * Make trim and get lower and snake case string.
 *
 * @param string $str
 *
 * @return string
 */
function trim_lower_snake($str)
{
    $outcome = trim($str);
    $outcome = strtolower($outcome);
    $outcome = str_replace(' ', '_', $outcome);

    return $outcome;
}

/**
 * Replace underscore by space.
 *
 * @param string $str
 *
 * @return string
 */
function snake_to_space($str)
{
    $outcome = trim($str);
    $outcome = str_replace('_', ' ', $outcome);

    return $outcome;
}

/**
 * Snake case to upper case and underscore by spaces.
 *
 * @param str  $snake
 * @param bool $only_first
 *
 * @return str
 */
function snake_to_ucwords($snake, $only_first = false)
{
    $words = str_replace('_', ' ', trim($snake));

    return $only_first ? ucfirst($words) : ucwords($words);
}

/**
 * Get readable field's name.
 *
 * @param string $field_name
 * @param array  $suggestion
 *
 * @return string
 */
function display_field($field_name, $suggestion = null)
{
    if (! is_null($suggestion) && is_array($suggestion) && array_key_exists($field_name, $suggestion)) {
        return $suggestion[$field_name];
    }

    $display = str_replace('_id', '', $field_name);
    $display = snake_to_ucwords($display);

    return $display;
}

/**
 * Place proper singular countable nouns (a/an).
 *
 * @param string $word
 *
 * @return string
 */
function vowel_checker($word)
{
    $vowels    = ['a', 'e', 'i', 'o', 'u'];
    $firstword = substr($word, 0, 1);
    $firstword = strtolower($firstword);
    $outcome   = 'a ' . $word;

    if (in_array($firstword, $vowels)) {
        $outcome = 'an ' . $word;
    }

    return $outcome;
}

/**
 * Valid mime types rules maker.
 *
 * @param string $types
 *
 * @return string
 */
function mime_rule($types)
{
    $rules  = [];
    $types  = explode('|', $types);
    $source = public_path('files/mime.types.json');

    if (file_exists($source)) {
        $source = json_decode(\File::get($source), true);

        foreach ($types as $type) {
            if (array_key_exists($type, $source) && ! in_array($source[$type], $rules)) {
                $rules[] = $source[$type];
            }
        }
    }

    return count($rules) ? 'mimetypes:' . implode(',', $rules) : '';
}

/**
 * Get a module icon.
 *
 * @param string $module
 * @param string $alternative
 *
 * @return string
 */
function module_icon($module, $alternative = null)
{
    $default_icon = 'fa fa-cube';

    $icon_list = [
        'user'            => 'fa fa-user',
        'staff'           => 'fa fa-user',
        'project'         => 'mdi mdi-library-books',
        'task'            => 'mdi mdi-clipboard-check',
        'issue'           => 'fa fa-bug',
        'milestone'       => 'fa fa-map-signs',
        'event'           => 'mdi mdi-calendar-star',
        'note'            => 'fa fa-file-text',
        'note_info'       => 'fa fa-file-text',
        'attach'          => 'fa fa-paperclip',
        'task_owner'      => 'mdi mdi-account-edit',
        'issue_owner'     => 'mdi mdi-account-edit',
        'event_owner'     => 'mdi mdi-account-edit',
        'milestone_owner' => 'mdi mdi-account-edit',
        'project_owner'   => 'mdi mdi-account-edit',
        'linked_id'       => 'mdi mdi-transfer-right',
        'project_id'      => 'mdi mdi-transfer-right',
    ];

    if (array_key_exists($module, $icon_list)) {
        return $icon_list[$module];
    }

    if (! is_null($alternative)) {
        return $alternative;
    }

    $module = explode('_', $module);

    if (array_key_exists($module[0], $icon_list)) {
        return $icon_list[$module[0]];
    }

    if (count($module) > 1 && array_key_exists($module[1], $icon_list)) {
        return $icon_list[$module[1]];
    }

    return $default_icon;
}

/**
 * Get module color.
 *
 * @param string $module
 *
 * @return string
 */
function module_color($module)
{
    $default_color = '#ffabd7';

    $color_list = [
        'project'   => '#289bf0',
        'task'      => '#32b42d',
        'issue'     => '#e64b3c',
        'milestone' => '#32bee6',
        'event'     => '#6496aa',
    ];

    if (array_key_exists($module, $color_list)) {
        return $color_list[$module];
    }

    return $default_color;
}

/**
 * Get a readable significant period list.
 *
 * @return array
 */
function time_period_list()
{
    return \Dropdown::getTimePeriodList();
}

/**
 * Get start and end date of a period.
 *
 * @param string|null $time_period_key
 * @param string|null $start_date
 * @param string|null $end_date
 *
 * @return array
 */
function time_period_dates($time_period_key = null, $start_date = null, $end_date = null)
{
    if (! is_null($start_date) && ! is_null($end_date)) {
        return ['start_date' => $start_date, 'end_date' => $end_date];
    }

    switch ($time_period_key) {
        case 'yesterday':
            $date = date('Y-m-d', strtotime('-1 days'));
            $date = date('Y-m-d H:i:s', strtotime($date));

            return ['start_date' => $date, 'end_date' => $date];
        case 'today':
            $date = date('Y-m-d');
            $date = date('Y-m-d H:i:s', strtotime($date));

            return ['start_date' => $date, 'end_date' => $date];
        case 'tommorrow':
            $date = date('Y-m-d', strtotime('+1 days'));
            $date = date('Y-m-d H:i:s', strtotime($date));

            return ['start_date' => $date, 'end_date' => $date];
        case 'last_month':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of previous month'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of previous month'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'current_month':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of this month'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of this month'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'next_month':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of next month'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of next month'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'last_7_days':
            $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));

            return ['start_date' => $start_date, 'end_date' => date('Y-m-d H:i:s')];
        case 'last_30_days':
            $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));

            return ['start_date' => $start_date, 'end_date' => date('Y-m-d H:i:s')];
        case 'last_60_days':
            $start_date = date('Y-m-d H:i:s', strtotime('-60 days'));

            return ['start_date' => $start_date, 'end_date' => date('Y-m-d H:i:s')];
        case 'last_90_days':
            $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));

            return ['start_date' => $start_date, 'end_date' => date('Y-m-d H:i:s')];
        case 'last_120_days':
            $start_date = date('Y-m-d H:i:s', strtotime('-120 days'));

            return ['start_date' => $start_date, 'end_date' => date('Y-m-d H:i:s')];
        case 'last_6_months':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of this month - 5 months'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of this month'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'last_12_months':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of this month - 11 months'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of this month'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'next_7_days':
            $end_date = date('Y-m-d H:i:s', strtotime('+7 days'));

            return ['start_date' => date('Y-m-d H:i:s'), 'end_date' => $end_date];
        case 'next_30_days':
            $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));

            return ['start_date' => date('Y-m-d H:i:s'), 'end_date' => $end_date];
        case 'next_60_days':
            $end_date = date('Y-m-d H:i:s', strtotime('+60 days'));

            return ['start_date' => date('Y-m-d H:i:s'), 'end_date' => $end_date];
        case 'next_90_days':
            $end_date = date('Y-m-d H:i:s', strtotime('+90 days'));

            return ['start_date' => date('Y-m-d H:i:s'), 'end_date' => $end_date];
        case 'next_120_days':
            $end_date = date('Y-m-d H:i:s', strtotime('+120 days'));

            return ['start_date' => date('Y-m-d H:i:s'), 'end_date' => $end_date];
        case 'next_6_months':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of next month'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of next month + 5 months'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'next_12_months':
            $start_date = date('Y-m-d H:i:s', strtotime('first day of next month'));
            $end_date   = date('Y-m-d H:i:s', strtotime('last day of next month + 11 months'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        case 'any':
            $start_date = date('Y-m-d H:i:s', strtotime('-15 years'));
            $end_date   = date('Y-m-d H:i:s', strtotime('+15 years'));

            return ['start_date' => $start_date, 'end_date' => $end_date];
        default:
            return ['start_date' => date('Y-m-d H:i:s', strtotime('-30 days')), 'end_date' => date('Y-m-d H:i:s')];
    }
}

/**
 * Readable period display text.
 *
 * @param string      $timeperiod
 * @param string|null $start
 * @param string|null $end
 *
 * @return string
 */
function timeperiod_display($timeperiod, $start = null, $end = null)
{
    if ($timeperiod == 'between') {
        return readable_date($start) . ' to ' . readable_date($end);
    }

    return strtolower(time_period_list()[$timeperiod]);
}

/**
 * Render tag attribute according to default|expected value
 *
 * @param mixed  $value
 * @param mixed  $expected_value
 * @param string $attribute_name
 * @param string $attribute_value
 *
 * @return string
 */
function tag_attr($value, $expected_value, $attribute_name, $attribute_value = null)
{
    // Attributes Example: selected="selected" | data-load="true"
    $attribute_value = is_null($attribute_value) ? $attribute_name : $attribute_value;

    if ($value == $expected_value) {
        return $attribute_name . "='" . $attribute_value . "'";
    }

    return null;
}

/**
 * Append proper CSS class according to the parent field value.
 *
 * @param string $class
 * @param string $append_class
 * @param array  $types_array
 * @param string $type
 * @param bool   $filter_type
 *
 * @return string
 */
function append_css_class($class, $append_class, $types_array, $type, $filter_type = true)
{
    $outcome = $class . ' ' . $append_class;

    if ($filter_type == true && in_array($type, $types_array)) {
        return $outcome;
    } elseif ($filter_type == false && ! in_array($type, $types_array)) {
        return $outcome;
    }

    return $class;
}

/**
 * Check before encrypting a value.
 *
 * @param mixed $val
 *
 * @return string|null
 */
function encrypt_if_has_value($val = null)
{
    if (isset($val) and $val !== '') {
        return encrypt($val);
    }

    return null;
}

/**
 * Check before decrypting a value.
 *
 * @param mixed $val
 *
 * @return string|null
 */
function check_before_decrypt($val = null)
{
    if (isset($val) and $val !== '') {
        return decrypt($val);
    }

    return null;
}
