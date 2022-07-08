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

use Notification;
use App\Models\Note;
use App\Models\NoteInfo;
use App\Models\Revision;
use App\Models\AttachFile;
use App\Jobs\SaveUploadedFile;
use App\Jobs\CleanRemovedFile;
use App\Notifications\CrudNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminNoteController extends AdminBaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setUploadDirectoryLocation('note_info');

        // Check user permission by middleware.
        $this->middleware('admin:note.view', ['only' => ['getData']]);
        $this->middleware('admin:note.create', ['only' => ['store']]);
        $this->middleware('admin:note.edit', ['only' => ['edit', 'update', 'pin']]);
        $this->middleware('admin:note.delete', ['only' => ['destroy']]);
    }

    /**
     * JSON format listing of the resource.
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

        // If related type module is valid and load validation passes.
        if (isset($request->type) && $request->type == $type && in_array($request->type, NoteInfo::types())) {
            $validation = NoteInfo::loadValidate($data);

            if ($validation->passes()) {
                $model  = morph_to_model($request->type);
                $parent = $model::find($request->typeid);
                $html   = $parent->getNotesHtmlAttribute($request->latestid, true);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // If the related type module is valid and validation passes then save posted data.
        if (isset($request->related_type) && in_array($request->related_type, NoteInfo::types())) {
            $validation = NoteInfo::validate($request->all());

            if ($validation->passes()) {
                $note_info              = new NoteInfo;
                $note_info->linked_id   = $request->related_id;
                $note_info->linked_type = $request->related_type;
                $note_info->description = $request->note;
                $note_info->save();

                $note               = new Note;
                $note->linked_id    = $request->related_id;
                $note->linked_type  = $request->related_type;
                $note->note_info_id = $note_info->id;
                $note->save();

                // If the specified resource has attached files then save uploaded files.
                dispatch(new SaveUploadedFile(
                    $request->uploaded_files,
                    'note_info',
                    $note_info->id,
                    $this->directory,
                    $this->location
                ));

                // Ajax quick response for not delaying execution.
                flush_response([
                    'status' => true,
                    'saveId' => $note->id,
                    'html'   => $note->getNoteHtmlAttribute(true, true),
                ]);

                // Get all hierarchical parents.
                $parent = [];

                if (in_array($note->linked_type, ['task', 'issue', 'event']) && isset($note->linked->linked_type)) {
                    $parent[] = ['type' => $note->linked->linked_type, 'id' => $note->linked->linked_id];

                    if (isset($note->linked->milestone_val)) {
                        $parent[] = ['type' => 'milestone', 'id' => $note->linked->milestone_val];
                    }
                } elseif ($note->linked_type == 'milestone') {
                    $parent[] = ['type' => 'project', 'id' => $note->linked->project_id];
                }

                if ($note->linked_type != 'staff') {
                    $staffs[] = $note->createdBy()->linked->id;

                    if (isset($note->linked->owner)) {
                        $staffs[] = $note->linked->owner->id;

                        if ($note->linked_type == 'project') {
                            $staffs = push_flatten($staffs, $note->linked->members->pluck('id')->toArray());
                        }
                    }

                    $staffs = array_unique($staffs);

                    foreach ($staffs as $staff) {
                        $parent[] = ['type' => 'staff', 'id' => $staff];
                    }
                }

                // If has parents then the specified resource is connected with all parents.
                if (count($parent)) {
                    $child_note_ids = [];

                    foreach ($parent as $parent_note) {
                        $child_note               = new Note;
                        $child_note->linked_id    = $parent_note['id'];
                        $child_note->linked_type  = $parent_note['type'];
                        $child_note->note_info_id = $note_info->id;
                        $child_note->save();

                        $child_note_ids[] = $child_note->id;
                    }

                    // Delete repeated same recorded histories.
                    Revision::where('revisionable_type', 'note')
                            ->whereIn('revisionable_id', $child_note_ids)
                            ->delete();
                }

                // Notify all users associated with this record.
                if (count($note->linked->notifees)) {
                    Notification::send(
                        get_wherein('user', $note->linked->notifees, [auth()->user()->id]),
                        new CrudNotification($note->linked_type . '_note_added', $note->linked_id)
                    );
                }
            } else {
                $messages = $validation->getMessageBag()->toArray();
                $errors = [];

                foreach ($messages as $msg) {
                    $errors[] = $msg;
                }

                return response()->json(['status' => false, 'errors' => $errors]);
            }
        } else {
            return response()->json(['status' => false, 'errors' => []]);
        }
    }

    /**
     * Show the form to edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Note         $note
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Note $note)
    {
        $status = true;
        $html   = null;

        // If the specified resource is valid and the auth user has permission to edit.
        if (isset($note) && isset($request->id) && $note->id == $request->id && $note->auth_can_edit) {
            $html = $note->edit_form;
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Note         $note
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Note $note)
    {
        // If the specified resource is valid and the auth user has permission to edit then follow the next execution.
        if (isset($note) && isset($request->id) && $note->id == $request->id && $note->auth_can_edit) {
            $validation = Note::validate($request->all());

            // Update posted data if validation passes.
            if ($validation->passes()) {
                $note_info = $note->info;
                $note_info->description = $request->note;
                $note_info->save();
                $note_info->attachfiles()->delete();

                $updated_by_count = $note_info->notes()
                                              ->where('linked_type', 'staff')
                                              ->where('linked_id', $note_info->updatedBy()->linked->id)
                                              ->get()
                                              ->count();

                // If the user who updated the specified resource has changed.
                if (! $updated_by_count) {
                    $child_note               = new Note;
                    $child_note->note_info_id = $note_info->id;
                    $child_note->linked_id    = $note_info->updatedBy()->linked->id;
                    $child_note->linked_type  = 'staff';
                    $child_note->save();

                    Revision::where('revisionable_type', 'note')
                            ->where('revisionable_id', $child_note->id)
                            ->delete();
                }

                // If the specified resource has attached files then save uploaded files.
                dispatch(new SaveUploadedFile(
                    $request->uploaded_files,
                    'note_info',
                    $note_info->id,
                    $this->directory,
                    $this->location
                ));

                // If the specified resource has removed file then delete older files.
                dispatch(new CleanRemovedFile(
                    $request->removed_files,
                    $this->directory,
                    $this->location
                ));

                // Locate updated note position.
                if ($note->pin == 1) {
                    $html = $note->getNoteHtmlAttribute(null, null, true);
                    $location = 0;
                } else {
                    $prev_location = Note::wherePin(0)
                                         ->whereLinked_type($note->linked_type)
                                         ->whereLinked_id($note->linked_id)
                                         ->where('id', '>', $note->id)
                                         ->orderBy('id')
                                         ->first();

                    $top      = ! isset($prev_location) ? true : null;
                    $html     = $note->getNoteHtmlAttribute($top);
                    $location = $note->id;
                }

                // Ajax quick response for not delaying execution.
                flush_response(['status' => true, 'html' => $html, 'location' => $location, 'saveId' => $request->id]);

                // Notify all users associated with this record.
                if (count($note->linked->notifees)) {
                    Notification::send(
                        get_wherein('user', $note->linked->notifees, [auth()->user()->id]),
                        new CrudNotification($note->linked_type . '_note_edited', $note->linked_id)
                    );
                }
            } else {
                $messages = $validation->getMessageBag()->toArray();
                $errors = [];

                foreach ($messages as $msg) {
                    $errors[] = $msg;
                }

                return response()->json(['status' => false, 'errors' => $errors]);
            }
        } else {
            return response()->json(['status' => false, 'errors' => []]);
        }
    }

    /**
     * Pin at the top of the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Note         $note
     *
     * @return \Illuminate\Http\Response
     */
    public function pin(Request $request, Note $note)
    {
        $pin_html       = null;
        $pin_location   = null;
        $unpin_html     = null;
        $unpin_location = null;
        $prev_location  = null;
        $count          = null;
        $pin            = (bool) $request->pin;

        // If request an invalid pin and the auth user does not have permission to edit then return false.
        if (! is_bool($pin) || ! $note->auth_can_edit) {
            return response()->json(['status' => false]);
        } else {
            // If the request for to be pinned note else unpin the note.
            if ($pin) {
                $old_pin = Note::whereLinked_type($note->linked_type)
                               ->whereLinked_id($note->linked_id)
                               ->wherePin(1)
                               ->first();

                Note::whereLinked_type($note->linked_type)->whereLinked_id($note->linked_id)->update(['pin' => 0]);

                $unpin = isset($old_pin) ? Note::find($old_pin->id) : null;
                $note->pin = 1;
                $note->update();

                $pin_html = $note->getNoteHtmlAttribute(null, null, true);
                $pin_location = $note->id;
            } else {
                $note->pin = 0;
                $note->update();
                $unpin_location = $note->id;
                $unpin = $note;
            }

            $count = Note::whereLinked_type($note->linked_type)
                         ->whereLinked_id($note->linked_id)
                         ->wherePin(0)
                         ->count();

            // If unpin then locate note new position.
            if (isset($unpin)) {
                $prev_location = Note::wherePin(0)
                                     ->whereLinked_type($unpin->linked_type)
                                     ->whereLinked_id($unpin->linked_id)
                                     ->where('id', '>', $unpin->id)
                                     ->orderBy('id')
                                     ->first();

                $unpin_top_status = ! isset($prev_location) ? true : null;
                $unpin_html       = $unpin->getNoteHtmlAttribute($unpin_top_status);
                $prev_location    = isset($prev_location) ? $prev_location->id : 0;
            }

            // Ajax quick response for not delaying execution.
            flush_response([
                'status'            => true,
                'pinHtml'           => $pin_html,
                'unpinHtml'         => $unpin_html,
                'pinLocation'       => $pin_location,
                'prevLocation'      => $prev_location,
                'unpinLocation'     => $unpin_location,
                'timelineInfoCount' => $count,
            ]);

            // Notify all users associated with this record.
            if (count($note->linked->notifees)) {
                Notification::send(
                    get_wherein('user', $note->linked->notifees, [auth()->user()->id]),
                    new CrudNotification($note->linked_type . '_note_pin', $note->linked_id, ['pin' => $pin])
                );
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Note         $note
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Note $note)
    {
        $status = true;
        $count  = null;

        // Valid specified resource and the auth user has to delete permission checker.
        if ($note->id != $request->note_id || ! $note->auth_can_delete) {
            $status = false;
        }

        if ($status == true) {
            $linked_type = $note->linked_type;
            $linked_id   = $note->linked_id;

            // Delete all attach files related to the specified resource.
            foreach ($note->attachfiles as $file) {
                if ($file->public) {
                    \File::delete($file->path);
                } else {
                    \Storage::disk('base')->delete($file->path);
                }
            }

            // Record delete history.
            Revision::secureHistory(
                'note',
                $note->info->notes->pluck('id')->toArray(),
                'deleted_at',
                null,
                date('Y-m-d H:i:s')
            );

            $note->info->attachfiles()->delete();
            $note->info->notes()->delete();
            $note->info()->delete();

            $count = Note::whereLinked_type($linked_type)->whereLinked_id($linked_id)->wherePin(0)->count();
        }

        return response()->json([
            'status'            => $status,
            'timelineInfoId'    => $request->note_id,
            'timelineInfoCount' => $count,
        ]);
    }
}
