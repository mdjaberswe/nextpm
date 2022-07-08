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

use File;
use Image;
use Storage;
use FileHelper;
use Notification;
use App\Models\AttachFile;
use App\Jobs\SaveUploadedFile;
use App\Jobs\CleanRemovedFile;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminFileController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:attachment.view', ['only' => ['fileData', 'show']]);
        $this->middleware('admin:attachment.create', ['only' => ['store', 'linkStore']]);
        $this->middleware('admin:attachment.delete', ['only' => ['destroy']]);
    }

    /**
     * JSON format listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $linked_type
     * @param int                      $linked_id
     *
     * @return \Illuminate\Http\Response
     */
    public function fileData(Request $request, $linked_type, $linked_id)
    {
        // Valid related module check then gets module file resource data.
        if (in_array($linked_type, AttachFile::linkedTypes())) {
            $linked = morph_to_model($linked_type)::withTrashed()->find($linked_id);

            if (isset($linked)) {
                $files = $linked->attachfiles()->latest('id')->get();

                return AttachFile::getTableData($files, $request);
            }
        }
    }

    /**
     * Upload a resource file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $status     = false;
        $file_name  = null;
        $data       = $request->all();
        $validation = AttachFile::uploadValidate($data);

        // If validation passes then upload the posted file.
        if ($validation->passes()) {
            $status = true;
            $directory = AttachFile::directoryRule($request->linked);
            $upload_directory = str_replace('.', '/', $directory['location']);
            $upload_path = $directory['public'] ? public_path($upload_directory) : storage_path($upload_directory);

            if (! file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $file = $request->file('file');
            $file_name = FileHelper::generateUploadedFilename($file->getClientOriginalName());
            $file->move($upload_path, $file_name);
        }

        return response()->json(['status' => $status, 'fileName' => $file_name]);
    }

    /**
     * Upload a profile avatar.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAvatar(Request $request)
    {
        $validation = AttachFile::avatarValidate($request->all());

        // If validation passes then upload the posted avatar.
        if ($validation->passes()) {
            $image_type       = $request->linked_type . $request->linked_id;
            $folder           = $request->linked_type . 's';
            $folder_path      = storage_path('app/' . $folder . '/');
            $upload_directory = 'app/temp/';
            $upload_path      = storage_path($upload_directory);

            // Check directories and permissions.
            if (! file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            if (! file_exists($folder_path)) {
                mkdir($folder_path, 0777, true);
            }

            $file      = $request->file('photo');
            $filename  = FileHelper::generateUploadedFilename($file->getClientOriginalName());
            $save_path = storage_path($upload_directory . $filename);

            Image::make($file)
                 ->crop($request->width, $request->height, $request->x, $request->y)
                 ->fit(200, 200)
                 ->save($save_path);

            // If the avatar has a related module then upload the proper directory and save the file path into DB.
            if (! empty($request->linked_id)) {
                $model  = morph_to_model($request->linked_type);
                $linked = $model::find($request->linked_id);

                if (isset($linked)) {
                    if (! is_null($linked->image)) {
                        \Storage::disk('base')->delete($linked->image_path);
                    }

                    $image_path = storage_path('app/' . $folder . '/' . $filename);
                    \File::move($save_path, $image_path);
                    $save_path = $image_path;
                    $linked->image = $filename;
                    $linked->update();
                }
            }

            $image = (string) Image::make($save_path)->encode('data-url');

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'         => true,
                'modalImage'     => $image,
                'fileName'       => $filename,
                'modalImageType' => $image_type,
            ]);

            // Delete all unnecessary old files from the temp directory.
            FileHelper::cleanOlderFiles($upload_directory);
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Store a newly created resource file in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = AttachFile::validate($request->all());

        // If validation passes then save posted data.
        if ($validation->passes()) {
            $directory = AttachFile::directoryRule($request->linked_type);
            $location  = str_replace('.', '/', $directory['location']) . '/';

            dispatch(new SaveUploadedFile(
                $request->uploaded_files,
                $request->linked_type,
                $request->linked_id,
                $directory,
                $location
            ));

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true]);

            $module = morph_to_model($request->linked_type)::find($request->linked_id);

            // Notify all users associated with this record.
            if (count($module->notifees) && count($request->uploaded_files)) {
                Notification::send(
                    get_wherein('user', $module->notifees, [auth()->user()->id]),
                    new CrudNotification(
                        $request->linked_type . '_file_added',
                        $request->linked_id,
                        ['file_count' => count($request->uploaded_files)]
                    )
                );
            }
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Store a newly created resource link in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function linkStore(Request $request)
    {
        $validation = AttachFile::linkValidate($request->all());

        // If link validation passes.
        if ($validation->passes()) {
            $url  = domain_to_url($request->url);
            $name = get_url_title($url);

            // Save posted the link with a related module.
            $file = new AttachFile;
            $file->name        = $name;
            $file->location    = $request->url;
            $file->linked_id   = $request->linked_id;
            $file->linked_type = $request->linked_type;
            $file->save();

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true]);

            // Notify all users associated with this link.
            if (count($file->linked->notifees)) {
                Notification::send(
                    get_wherein('user', $file->linked->notifees, [auth()->user()->id]),
                    new CrudNotification(
                        $request->linked_type . '_file_added',
                        $request->linked_id,
                        ['file_count' => false]
                    )
                );
            }
        } else {
            return response()->json(['status' => false, 'errors' => $validation->getMessageBag()->toArray()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AttachFile   $file
     * @param string                   $filename
     * @param string|null              $download
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, AttachFile $file, $filename, $download = null)
    {
        $status = $file->name == $filename && file_exists($file->full_path);

        if ($request->ajax()) {
            return response()->json(['status' => $status]);
        }

        // If file not found then abort.
        if (! $status) {
            abort(404);
        }

        $file_content = File::get($file->full_path);
        $file_type    = File::mimeType($file->full_path);

        if (! is_null($download) && $download == 'download') {
            return response()->download($file->full_path, $filename, ["Content-Type: $file_type"]);
        }

        $response = response()->make($file_content, 200);
        $response->header('Content-Type', $file_type);

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {
        $status     = false;
        $data       = $request->all();
        $validation = AttachFile::removeValidate($data);

        // Remove request validation check.
        if ($validation->passes()) {
            $status    = true;
            $directory = AttachFile::directoryRule($request->linked);
            $location  = str_replace('.', '/', $directory['location']) . '/';
            dispatch(new CleanRemovedFile($request->uploaded_files, $directory, $location));
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Remove the specified resource file from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AttachFile   $file
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, AttachFile $file)
    {
        // If the specified resource is valid and the auth user has permission to delete then follow the next execution.
        if ($file->id == $request->id && $file->auth_can_delete) {
            if (! $file->is_link && $file->is_exist) {
                if ($file->public) {
                    File::delete($file->path);
                } else {
                    Storage::disk('base')->delete($file->path);
                }
            }

            $module       = $file->linked;
            $module_name  = $file->linked_type;
            $file_or_link = ! $file->is_link ? 'file' : 'link';
            $file->delete();

            // Ajax quick response for not delaying execution.
            flush_response(['status' => true]);

            // Notify all users associated with this record.
            if (count($module->notifees)) {
                Notification::send(
                    get_wherein('user', $module->notifees, [auth()->user()->id]),
                    new CrudNotification(
                        $module_name . '_file_removed',
                        $module->id,
                        ['file_type' => $file_or_link]
                    )
                );
            }
        } else {
            return response()->json(['status' => false]);
        }
    }
}
