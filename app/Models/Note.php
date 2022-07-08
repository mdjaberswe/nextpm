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

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HistoryTrait;

class Note extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['note_info_id', 'linked_id', 'linked_type', 'pin'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Store creations in the revision history.
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Don't keep history fields array list.
     *
     * @var array
     */
    protected $dontKeepRevisionOf = ['note_info_id', 'linked_id', 'linked_type'];

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFields = ['pin' => 'boolean:Unpin|Pin'];

    /**
     * Field custom display name.
     *
     * @var array
     */
    protected $revisionFormattedFieldNames = ['pin' => 'Note Position'];

    /**
     * Note validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        return validator($data, [
            'id'             => 'required|exists:notes,id,deleted_at,NULL',
            'note'           => 'required|max:65535',
            'uploaded_files' => 'array|max:10',
            'removed_files'  => 'array|max:10',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the auth user permission status to edit the specified resource.
     *
     * @return bool
     */
    public function getAuthCanEditAttribute()
    {
        // Not Editable if the auth user doesn't have global note edit permission.
        // Editable if the auth user is the creator.
        // The project's note can be editable if the auth user has the project note edit permission.
        // Module's note can be editable if the auth user has 'edit' permission of the related module.
        // Not editable if none of the above conditions is satisfied.
        if (! permit('note.edit')) {
            return false;
        } elseif ($this->created_by == auth_staff()->id || $this->info->updated_by == auth_staff()->id) {
            return true;
        } elseif (method_exists($this->linked, 'authCanDo')) {
            return $this->linked->authCanDo('note_edit');
        } elseif (method_exists($this->linked, 'getAuthCanEditAttribute')) {
            return $this->linked->auth_can_edit;
        } else {
            return false;
        }
    }

    /**
     * Get the auth user permission status to delete the specified resource.
     *
     * @return bool
     */
    public function getAuthCanDeleteAttribute()
    {
        // Not Deletable if the auth user doesn't have global note delete permission.
        // Deletable if the auth user is the creator.
        // The project's note can be deletable if the auth user has the project note delete permission.
        // Module's note can be deletable if the auth user has 'delete' permission of the related module.
        // Not editable if none of the above conditions is satisfied.
        if (! permit('note.delete')) {
            return false;
        } elseif ($this->created_by == auth_staff()->id || $this->info->updated_by == auth_staff()->id) {
            return true;
        } elseif (method_exists($this->linked, 'authCanDo')) {
            return $this->linked->authCanDo('note_delete');
        } elseif (method_exists($this->linked, 'getAuthCanEditAttribute')) {
            return $this->linked->auth_can_edit;
        } else {
            return false;
        }
    }

    /**
     * Get note action buttons.
     *
     * @param bool|null $is_pin
     *
     * @return string
     */
    public function getActionBtnsHtml($is_pin = null)
    {
        // Return null if the auth user does not have permission to edit|delete the specified note.
        if (! $this->auth_can_edit && ! $this->auth_can_delete) {
            return null;
        }

        $edit_btn = '';
        $pin_btn  = '';
        $del_btn  = '';

        // If the auth user has permission to edit the specified note.
        if ($this->auth_can_edit) {
            $edit_btn = "<div class='inline-action'>
                            <a data-id='{$this->id}' data-url='" . route('admin.note.edit', $this->id) . "'
                               class='timeline-edit' ><i class='fa fa-pencil'></i>
                            </a>
                        </div>";

            $pin_btn = "<li>
                            <a class='pin-btn' data-pin='1' data-url='" . route('admin.note.pin', $this->id) . "'>
                            <i class='mdi mdi-pin'></i> Pin</a>
                        </li>";

            // If the specified note is pinned.
            if (isset($is_pin)) {
                $pin_btn = "<li>
                                <a class='pin-btn' data-pin='0' data-url='" . route('admin.note.pin', $this->id) . "'>
                                <i class='mdi mdi-pin-off'></i> Unpin</a>
                            </li>";
            }
        }

        // If the auth user has permission to delete the specified note.
        if ($this->auth_can_delete) {
            $del_btn = "<li>" .
                            \Form::open(['route' => ['admin.note.destroy', $this->id], 'method' => 'delete']) .
                                \Form::hidden('note_id', $this->id) .
                                "<button type='submit' class='delete' data-item='note'>
                                    <i class='mdi mdi-delete'></i> Delete
                                </button>" .
                            \Form::close() .
                       '</li>';
        }

        $html = "<div class='action-box'>" .
                    $edit_btn . "
                    <div class='dropdown'>
                        <a class='dropdown-toggle' animation='fadeIn|fadeOut' data-toggle='dropdown'>
                            <i class='fa fa-ellipsis-v'></i>
                        </a>
                        <ul class='dropdown-menu'>" .
                            $pin_btn . $del_btn . '
                        </ul>
                    </div>
                </div>';

        return $html;
    }

    /**
     * Get note HTML.
     *
     * @param bool|null $top
     * @param bool|null $new
     * @param bool|null $is_pin
     *
     * @return string
     */
    public function getNoteHtmlAttribute($top = null, $new = null, $is_pin = null)
    {
        $top      = isset($top) ? 'top' : null;
        $circle   = isset($new) ? "<span class='circle'></span>" : null;
        $pin      = null;
        $pin_mark = null;

        // If the specified note is pinned.
        if (isset($is_pin)) {
            $pin      = 'pin';
            $pin_mark = "<span class='pin-mark rot-45'><i class='mdi mdi-pin'></i></span>";
        }

        $attachfiles_html = "<div class='full attachfile-container'>";

        // Render the specified note attached files.
        foreach ($this->attachfiles as $file) {
            $attachfiles_html .= $file->thumb_html;
        }

        $attachfiles_html .= '</div>';
        $html = "<div class='timeline-info $top $pin box' data-id='{$this->id}'>
                    <div class='timeline-icon'>
                        <i class='mdi mdi-clipboard-text'></i>
                    </div> " .
                    $circle . $pin_mark . "
                    <div class='timeline-details'>
                        <img src='{$this->info->updatedBy()->linked->avatar}'
                             alt='{$this->info->updatedBy()->linked->last_name}' class='img-avt'>
                        <div class='timeline-details-content'>
                            <div class='timeline-title'>
                                <p class='along-action'>{$this->info->description_html}</p>
                                {$this->getActionBtnsHtml($is_pin)}
                            </div>" .

                            $attachfiles_html . "

                            <div class='timeline-record'>
                                <span class='capsule'>
                                    " . ucfirst($this->info->display_type) .
                                    " - <a href='{$this->info->linked->show_route}'>{$this->info->linked->name}</a>
                                </span>
                                <span class='capsule'>
                                    <i class='dot fa fa-circle'></i>
                                    <i class='fa fa-clock-o '></i>
                                    <span data-toggle='tooltip' data-placement='bottom'
                                          title='{$this->info->readableDateAmPm('updated_at')}'>" .
                                          $this->info->readableDate('updated_at') .
                                   "</span>
                                </span>
                                <span class='capsule'>
                                    <i class='dot fa fa-circle'></i>
                                    <span class='type'>by</span>
                                    {$this->info->updatedBy()->linked->name_link}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>";

        $count = self::whereLinked_type($this->linked_type)
                     ->whereLinked_id($this->linked_id)
                     ->wherePin(0)
                     ->count();

        // if the total no of unpinned notes is one and this is the unpinned note then disable end down more notes.
        if ($count == 1 && $this->pin == 0) {
            $html .= "<div class='timeline-info end-down disable'>
                        <i class='load-icon fa fa-circle-o-notch fa-spin'></i>
                        <div class='timeline-icon'><a class='load-timeline'><i class='fa fa-angle-down'></i></a></div>
                     </div>";
        }

        return $html;
    }

    /**
     * Get note edit form.
     *
     * @return string
     */
    public function getEditFormAttribute()
    {
        $attached_files = '';
        $attached_file_inputs = '';

        // Render all attached files of the specified note.
        foreach ($this->attachfiles as $file) {
            $attached_files .=  "<div class='dz-preview dz-file-preview'>
                                    <div class='dz-details' style='min-width: 65%;'>" .
                                        $file->file_icon . "
                                        <div class='dz-size'>
                                            <span>{$file->size_html}</span>
                                        </div>
                                        <div class='dz-filename' data-original='{$file->location}'>
                                            <span data-checked='false'>{$file->name}</span>
                                        </div>
                                    </div>
                                    <a class='dz-remove edit-dz-remove' style='right: auto; margin-left: 10px;'>
                                        Remove file
                                    </a>
                                </div>";

            $attached_file_inputs .= "<input type='hidden' name='uploaded_files[]' value='{$file->location}'>";
        }

        // Render edit form of the specified note.
        $html = "<div class='timeline-form' data-posturl='" . route('admin.note.update', $this->id) . "'>
                    <div class='form-group'>" .
                        \Form::textarea('note', $this->info->description, [
                            'class'       => 'form-control atwho-inputor',
                            'placeholder' => 'Start typing to leave a note...',
                            'at-who'      => \Dropdown::atWhoData(),
                        ]) .
                        \Form::hidden('id', $this->id) . "
                    </div>

                    <div class='form-group bottom'>
                        <div class='full'>
                            <div class='option-icon'>
                                <a class='dropzone-attach rot--90' data-toggle='tooltip'
                                   data-placement='bottom' title='Attach File'><i class='fa fa-paperclip'></i>
                                </a>
                            </div>

                            <div class='form-btn'>
                                <button type='button' class='first btn btn-info update-comment ladda-button' data-style='expand-right'>
                                    <span class='ladda-label' data-status='true'>Save</span>
                                </button>
                                <button class='cancel btn btn-secondary'>Cancel</button>
                            </div>
                        </div>

                        <div class='full'>
                            <div class='col-xs-12 col-sm-12 col-md-12 col-lg-10 modalfree dropzone-container update-dz'>
                                <div class='modalfree-dropzone' data-linked='note_info'
                                    data-url='" . route('admin.file.upload') . "'
                                    data-removeurl='" . route('admin.file.remove') . "'>
                                </div>
                                <div class='dz-preview-container'>{$attached_files}</div>
                                {$attached_file_inputs}
                            </div>
                        </div>
                    </div>
                </div>";

        return $html;
    }

    /**
     * Get attach files of the note.
     *
     * @return \App\Models\AttachFile
     */
    public function getAttachfilesAttribute()
    {
        return $this->info->attachfiles;
    }

    /**
     * Get the history link of the note.
     *
     * @param \App\Models\Revision $history
     *
     * @return string
     */
    public function historyLink($history)
    {
        $outcome = '<strong>' .
                        str_limit(emoji($this->info->historyVal('description', $history->created_at)['closest']), 50) .
                   '</strong>';

        // If history root is the related module of the specified note, then return the description of the note.
        if ($history->root == $this->info->linked_type && $history->root_id == $this->info->linked_id) {
            return $outcome;
        }

        $outcome .= "<br><a href='{$this->info->linked->getShowRouteAttribute([$this->info->linked_id, 'notes'])}'
                            class='like-txt' target='_blank'>
                            <span class='icon {$this->info->linked->icon}' data-toggle='tooltip' data-placement='top'
                            title='" . ucfirst($this->info->linked->identifier) . "'></span>
                            {$this->info->linked->historyVal('name', $history->created_at)['closest']}
                         </a>";

        return $outcome;
    }

    /**
     * Get updated type history information on the note.
     *
     * @param \App\Models\Revision $history
     *
     * @return string|null
     */
    public function updatedHistoryInfo($history)
    {
        return $history->key == 'pin' ? '<br>' . $this->historyLink($history) : null;
    }

    /**
     * Get attached files of NoteInfo related to the note.
     *
     * @return \App\Models\AttachFile
     */
    public function attachfiles()
    {
        return $this->info->attachfiles;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with NoteInfo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function info()
    {
        return $this->belongsTo(NoteInfo::class, 'note_info_id')->withTrashed();
    }

    /**
     * A polymorphic, inverse one-to-many relationship with Project|Milestone|Task|Issue|Event|Staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo();
    }
}
