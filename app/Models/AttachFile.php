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

class AttachFile extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attach_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'format', 'size', 'location', 'linked_id', 'linked_type'];

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
     * Parent module list array.
     *
     * @var array
     */
    protected static $linkedTypes = [
        'project', 'milestone', 'task', 'issue', 'event', 'note_info', 'staff', 'chat_sender',
    ];

    /**
     * Valid image formats.
     *
     * @var array
     */
    protected static $imageFormats = ['webp', 'jpeg', 'jpg', 'png', 'gif'];

    /**
     * Attach file validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        $related_module = morph_to_model($data['linked_type'])::find((int) $data['linked_id']);

        // If posted data has a related module and the auth user doesn't have permission to attach a file
        // or view the related module then consider the related module for the auth user is invalid.
        if (isset($related_module)) {
            if (method_exists($related_module, 'authCanDo') && ! $related_module->authCanDo('attachment_create')) {
                $data['linked_id'] = 0;
            } elseif (method_exists($related_module, 'getAuthCanViewAttribute') && ! $related_module->auth_can_view) {
                $data['linked_id'] = 0;
            }
        }

        $rules = [
            'linked_id'      => "required|exists:{$data['linked_type']}s,id,deleted_at,NULL",
            'linked_type'    => 'required|in:' . implode(',', self::$linkedTypes),
            'uploaded_files' => 'required|array|max:10',
        ];

        return validator($data, $rules);
    }

    /**
     * Link validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function linkValidate($data)
    {
        $related_module = morph_to_model($data['linked_type'])::find((int) $data['linked_id']);

        // If posted data has a related module and the auth user doesn't have permission to add link
        // or view the related module then consider the related module for the auth user is invalid.
        if (isset($related_module)) {
            if (method_exists($related_module, 'authCanDo') && ! $related_module->authCanDo('attachment_create')) {
                $data['linked_id'] = 0;
            } elseif (method_exists($related_module, 'getAuthCanViewAttribute') && ! $related_module->auth_can_view) {
                $data['linked_id'] = 0;
            }
        }

        $rules = [
            'url'         => 'required|valid_domain',
            'linked_id'   => "required|exists:{$data['linked_type']}s,id,deleted_at,NULL",
            'linked_type' => 'required|in:' . implode(',', self::$linkedTypes),
        ];

        return validator($data, $rules);
    }

    /**
     * Upload file validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function uploadValidate($data)
    {
        return validator($data, [
            'file'   => 'required|file',
            'linked' => 'required|in:' . implode(',', self::$linkedTypes),
        ]);
    }

    /**
     * Avatar image validation.
     *
     * @param array $data
     *
     * @return Illuminate\Validation\Validator
     */
    public static function avatarValidate($data)
    {
        $error_messages = ['mimetypes' => ' The image must be a file of type: jpeg, png, gif, webp. '];
        $rules = [
            'photo'       => 'required|image|mimetypes:image/webp,image/jpeg,image/png,image/jpg,image/gif|max:3072',
            'x'           => 'required|integer',
            'y'           => 'required|integer',
            'width'       => 'required|integer',
            'height'      => 'required|integer',
            'linked_type' => 'required|in:staff',
        ];

        return validator($data, $rules, $error_messages);
    }

    /**
     * Remove image validation.
     *
     * @param array $data
     *
     * @return Illuminate\Validation\Validator
     */
    public static function removeValidate($data)
    {
        return validator($data, [
            'uploaded_files' => 'required',
            'linked' => 'required|in:' . implode(',', self::$linkedTypes),
        ]);
    }

    /**
     * Get the parent module list.
     *
     * @return array
     */
    public static function linkedTypes()
    {
        return self::$linkedTypes;
    }

    /**
     * Get the upload directory.
     *
     * @param string $linked_type
     *
     * @return array
     */
    public static function directoryRule($linked_type)
    {
        // All related module files upload in the storage app/attachments directory.
        if (! is_null($linked_type) && in_array($linked_type, self::$linkedTypes)) {
            return ['location' => 'app.attachments', 'public' => 0];
        }

        return ['location' => 'uploads', 'public' => 1];
    }

    /**
     * Get files data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        return [
            'thead'        => ['NAME', 'UPLOADED BY', 'DATE MODIFIED', 'SIZE'],
            'json_columns' => \DataTable::jsonColumn(['name', 'uploaded_by', 'updated_at', 'size', 'action']),
            'checkbox'     => false,
        ];
    }

    /**
     * Get files table data.
     *
     * @param \App\Models\AttachFile   $files
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($files, $request)
    {
        return \DataTable::of($files)->editColumn('name', function ($file) {
            return $file->name_html;
        })->addColumn('uploaded_by', function ($file) {
            return $file->updatedBy()->linked->profile_html;
        })->editColumn('updated_at', function ($file) {
            return $file->readableDateHtml('updated_at');
        })->editColumn('size', function ($file) {
            return $file->size_html;
        })->addColumn('action', function ($file) {
            return $file->getActionHtml('File', 'admin.file.destroy', null);
        })->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get file display name HTML.
     *
     * @return string
     */
    public function getNameHtmlAttribute()
    {
        $tooltip = strlen($this->name) > 70 ? "data-toggle='tooltip' data-placement='top' title='{$this->name}'" : null;

        return "<a target='_blank' $tooltip class='{$this->filelink_css}' data-valid='{$this->is_exist}'
                   href='{$this->href}'>{$this->file_icon}" . str_limit($this->name, 70) .
               '</a>';
    }

    /**
     * Get the file history link.
     *
     * @param \App\Models\Revision $history
     *
     * @return string
     */
    public function historyLink($history)
    {
        $outcome = $this->name_html;

        // If history root is the related module of the attached file, then return the attached file name link.
        if ($history->root == $this->linked_type && $history->root_id == $this->linked_id) {
            return $outcome;
        }

        $outcome .= "<br><a href='{$this->linked->getShowRouteAttribute([$this->linked_id, 'files'])}'
                            class='like-txt' target='_blank'>
                            <span class='icon {$this->linked->icon}' data-toggle='tooltip' data-placement='top'
                                  title='" . ucfirst($this->linked->identifier) . "'>
                            </span> " . $this->linked->historyVal('name', $history->created_at)['closest'] .
                        '</a>';

        return $outcome;
    }

    /**
     * Get file thumb HTML.
     *
     * @return string
     */
    public function getThumbHtmlAttribute()
    {
        $html = "<div class='file-thumb'>";

        // If the attached file is an image then show the image with a download link,
        // else show the file with the proper icon and link.
        if ($this->is_image) {
            $html .= "<div class='img-thumb'>";
            $html .= "<a target='_blank'  class='{$this->filelink_css} {$this->tooltip_css}' href='{$this->href}'
                         data-valid='{$this->is_exist}'><img src='{$this->image_thumb_src}' title='{$this->name}'
                         data-toggle='tooltip' data-placement='top'>
                      </a>";
            $html .= '</div>';
        } else {
            $tooltip = strlen($this->name) > 9 ? "data-toggle='tooltip' data-placement='top' title='{$this->name}'" : '';
            $html .= "<div class='filelink-thumb'>";
            $html .= "<a target='_blank' $tooltip class='{$this->filelink_css} {$this->tooltip_css}'
                        data-valid='{$this->is_exist}' href='{$this->href}'>" .
                        $this->file_icon . '<br>' . str_limit($this->name, 9) .
                     '</a>';
            $html .= '</div>';
        }

        $html .= "<div class='filethumb-bottom'>";
        $html .= "<a target='_blank' class='{$this->filelink_css}' data-valid='{$this->is_exist}'
                     href='{$this->href}'><i class='fa fa-eye'></i>
                 </a>";
        $html .= "<a class='download' data-valid='{$this->is_exist}' href='" .
                     route('admin.file.show', [$this->id, $this->name, 'download']) . "'><i class='fa fa-download'></i>
                 </a>";
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Get chat message attach file thumb HTML.
     *
     * @param string|null $tooltip
     *
     * @return string
     */
    public function getChatThumbHtmlAttribute($tooltip = null)
    {
        // If the chat message attached file is an image, then show the image with a download link
        // else show the attached file with the proper icon and download link.
        if ($this->is_image) {
            $html = "<a target='_blank' $tooltip class='{$this->filelink_css}' data-valid='{$this->is_exist}'
                        href='{$this->href}'><img src='{$this->href}'>
                    </a>";
        } else {
            $html = "<a target='_blank' $tooltip class='{$this->filelink_css}' data-valid='{$this->is_exist}'
                        href='{$this->href}'>" . $this->file_icon . str_limit($this->name, 45) .
                    '</a>';
        }

        return $html;
    }

    /**
     * Get the file download link.
     *
     * @return string
     */
    public function getDownloadLinkAttribute()
    {
        return "<a class='download' data-valid='{$this->is_exist}' href='" .
                    route('admin.file.show', [$this->id, $this->name, 'download']) . "'><i class='fa fa-download'></i>
                </a>";
    }

    /**
     * Get image file thumb src attribute value.
     *
     * @return string
     */
    public function getImageThumbSrcAttribute()
    {
        if ($this->is_image && $this->is_exist) {
            return (string) \Image::make($this->full_path)->resize(100, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode('data-url');
        }

        return null;
    }

    /**
     * Get file link href attribute value.
     *
     * @return string
     */
    public function getHrefAttribute()
    {
        return $this->is_link ? domain_to_url($this->location) : route('admin.file.show', [$this->id, $this->name]);
    }

    /**
     * Get the file icon.
     *
     * @return string
     */
    public function getFileIconAttribute()
    {
        return $this->is_link ? "<i class='icon fa fa-link'></i>" : \FileHelper::getFileIcon($this->format);
    }

    /**
     * Get link status.
     *
     * @return bool
     */
    public function getIsLinkAttribute()
    {
        return is_null($this->format) && is_null($this->size);
    }

    /**
     * Get image status.
     *
     * @return bool
     */
    public function getIsImageAttribute()
    {
        return ! is_null($this->format) && in_array($this->format, self::$imageFormats);
    }

    /**
     * Get file size HTML.
     *
     * @return string
     */
    public function getSizeHtmlAttribute()
    {
        return is_null($this->size) ? "<span class='shadow normal'>-</span>" : readable_filesize($this->size);
    }

    /**
     * Get the file path.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        if (! $this->is_link) {
            $directory = self::directoryRule($this->linked_type);

            return str_replace('.', '/', $directory['location']) . '/' . $this->location;
        }

        return $this->location;
    }

    /**
     * Get the full path of the file.
     *
     * @return string
     */
    public function getFullPathAttribute()
    {
        if ($this->is_link) {
            return $this->location;
        }

        return $this->public ? public_path($this->path) : storage_path($this->path);
    }

    /**
     * Get file exists status.
     *
     * @return bool
     */
    public function getIsExistAttribute()
    {
        if ($this->is_link) {
            return filter_var($this->location, FILTER_VALIDATE_URL) ? 1 : 0;
        }

        return file_exists($this->full_path) ? 1 : 0;
    }

    /**
     * Get public directory according to file linked type.
     *
     * @return string
     */
    public function getPublicAttribute()
    {
        return self::directoryRule($this->linked_type)['public'];
    }

    /**
     * Get the link CSS class.
     *
     * @return string
     */
    public function getFilelinkCssAttribute()
    {
        return $this->is_link ? null : 'filelink';
    }

    /**
     * Get tooltip CSS class.
     *
     * @return string
     */
    public function getTooltipCssAttribute()
    {
        return strlen($this->name) > 30  ? 'tooltip-lg' : 'tooltip-md';
    }

    /**
     * Get file type name.
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        return $this->is_link ? 'link' : 'file';
    }

    /**
     * Get to know the auth user can delete the file.
     *
     * @return bool
     */
    public function getAuthCanDeleteAttribute()
    {
        // Not Deletable if the auth user doesn't have global file delete permission.
        // Deletable if the auth user is the creator.
        // A project file can be deletable if the auth user has this project file delete permission.
        // Module file can be deletable if the auth user has edit permission of this related module.
        // Not Deletable if none of the above conditions is satisfied.
        if (! permit('attachment.delete')) {
            return false;
        } elseif ($this->created_by == auth_staff()->id) {
            return true;
        } elseif (method_exists($this->linked, 'authCanDo')) {
            return $this->linked->authCanDo('attachment_delete');
        } elseif (method_exists($this->linked, 'getAuthCanEditAttribute')) {
            return $this->linked->auth_can_edit;
        } else {
            return false;
        }
    }

    /**
     * Get the specified resource all actions HTML.
     *
     * @param string      $item
     * @param string      $delete_route
     * @param string|null $edit_route
     * @param array       $action_permission
     * @param bool        $common_modal
     *
     * @return string
     */
    public function getActionHtml(
        $item,
        $delete_route,
        $edit_route = null,
        $action_permission = [],
        $common_modal = false
    ) {
        $view     = "<div class='inline-action'><a target='_blank' href='{$this->href}' class='{$this->filelink_css}'
                          data-valid='{$this->is_exist}'><i class='fa fa-eye'></i></a>
                    </div>";
        $download = null;
        $delete   = null;

        if (! $this->is_link) {
            $download = "<li>
                            <a href='" . route('admin.file.show', [$this->id, $this->name, 'download']) . "'
                               class='download' data-valid='{$this->is_exist}'><i class='fa fa-download'></i> Download
                            </a>
                        </li>";
        }

        if ($this->auth_can_delete) {
            $delete = '<li>' .
                        \Form::open(['route' => ['admin.file.destroy', $this->id], 'method' => 'delete']) .
                            \Form::hidden('id', $this->id) .
                            "<button type='submit' class='delete' data-item='{$this->type_name}'>
                                <i class='mdi mdi-delete'></i> Delete
                            </button>" .
                        \Form::close() .
                      '</li>';
        }

        $dropdown_menu          = $download . $delete;
        $complete_dropdown_menu = "<ul class='dropdown-menu'>{$dropdown_menu}</ul>";
        $open                   = "<div class='action-box'>";
        $dropdown               = "<div class='dropdown'>
                                    <a class='dropdown-toggle' data-toggle='dropdown'>
                                        <i class='fa fa-ellipsis-v'></i>
                                    </a>";
        $close                  = '</div></div>';
        $action                 = $open . $view . $dropdown . $complete_dropdown_menu . $close;

        return $action;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A polymorphic, inverse one-to-many relationship with different Modules
     * (Project|Milestone|Task|Issue|Event|NoteInfo|Staff|ChatSender).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linked()
    {
        return $this->morphTo()->withTrashed();
    }
}
