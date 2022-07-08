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

use App\Models\Revision;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminHistoryController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * JSON format listing of the history resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $type
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request, $type)
    {
        $status = false;
        $errors = [];
        $html   = null;
        $data   = $request->all();

        // If valid history type and validation passes then get all histories.
        if (isset($request->type) && $request->type == $type && in_array($request->type, Revision::types())) {
            $validation = Revision::loadValidate($data);

            if ($validation->passes()) {
                $module = morph_to_model($request->type)::find($request->typeid);
                $html   = $module->getAllHistoriesHtmlAttribute($request->latestid, true);
                $status = true;
            } else {
                $messages = $validation->getMessageBag()->toArray();

                foreach ($messages as $msg) {
                    $errors[] = $msg;
                }
            }
        }

        return response()->json(['status' => $status, 'errors' => $errors, 'html' => $html]);
    }
}
