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

class NoteInfo extends BaseModel
{
    use SoftDeletes;
    use HistoryTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'note_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'linked_id', 'linked_type'];

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
    protected $revisionCreationsEnabled = false;

    /**
     * Keep history only field array list.
     *
     * @var array
     */
    protected $keepRevisionOf = ['description'];

    /**
     * Field custom display name.
     *
     * @var array
     */
    protected $revisionFormattedFieldNames = ['description' => 'Note'];

    /**
     * Display custom format of field values.
     *
     * @var array
     */
    protected $revisionFormattedFields = ['description' => 'helper:emoji'];

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected static $types = ['project', 'milestone', 'task', 'issue', 'event', 'staff'];

    /**
     * Note Info validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data)
    {
        $valid_types    = implode(',', self::$types);
        $related_module = morph_to_model($data['related_type'])::find((int) $data['related_id']);

        // If posted data has a related module and the auth user doesn't have permission to create "note"
        // or view the related module then consider the related module for the auth user is invalid.
        if (isset($related_module)) {
            if (method_exists($related_module, 'authCanDo') && ! $related_module->authCanDo('note_create')) {
                $data['related_id'] = 0;
            } elseif (method_exists($related_module, 'getAuthCanViewAttribute') && ! $related_module->auth_can_view) {
                $data['related_id'] = 0;
            }
        }

        $rules = [
            'related_type'   => "required|in:{$valid_types}",
            'related_id'     => "required|exists:{$data['related_type']}s,id,deleted_at,NULL",
            'note'           => 'required|max:65535',
            'uploaded_files' => 'array|max:10',
        ];

        return validator($data, $rules);
    }

    /**
     * Note Info load validation.
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function loadValidate($data)
    {
        return validator($data, [
            'type'     => 'required|in:' . implode(',', self::$types),
            'typeid'   => "required|exists:{$data['type']}s,id,deleted_at,NULL",
            'latestid' => "required|exists:notes,id,linked_type,{$data['type']}," .
                          "linked_id,{$data['typeid']},deleted_at,NULL",
        ]);
    }

    /**
     * Get a valid related parent module list.
     *
     * @return array
     */
    public static function types()
    {
        return self::$types;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get note description HTML.
     *
     * @return string
     */
    public function getDescriptionHtmlAttribute()
    {
        $html = '';
        $at_count = substr_count($this->description, '@');

        // If don't mention any user then return plan note HTML else find mentioned users and render note HTML.
        if ($at_count == 0) {
            if (strlen($this->description) < 220) {
                return emoji($this->description);
            }

            $show_part   = substr($this->description, 0, 220);
            $hidden_part = "<span class='extend none'>" . substr($this->description, 220) . "</span>";
            $show_more   = "<a class='more'><span>...</span> more</a>";
            $html        = $show_part . $hidden_part . $show_more;
        } else {
            $names  = [];
            $at_pos = 0;
            $html   = $this->description;

            for ($i = 0; $i < $at_count; $i++) {
                $at_pos    = strpos($this->description, '@', $at_pos) + 1;
                $space_pos = strpos($this->description, ' ', $at_pos);

                if ($space_pos) {
                    $length = $space_pos - $at_pos;
                    $fname  = substr($this->description, $at_pos, $length);
                } else {
                    $fname = substr($this->description, $at_pos);
                }

                $names[] = $fname;
            }

            $at_whos = Staff::whereIn('first_name', $names)->orWhere(function ($query) use ($names) {
                $query->whereIn('last_name', $names);
            })->get();

            if (strlen($html) > 220) {
                $at_who_names = $at_whos->pluck('name')->toArray();
                $at_who_str   = implode(' ', $at_who_names);
                $at_who_array = explode(' ', $at_who_str);
                $break_point  = null;

                for ($j = 220; $j < strlen($html); $j++) {
                    $word_start_pos = strpos($html, ' ', $j);

                    if ($word_start_pos) {
                        $word_start_pos++;
                        $word_next_pos = strpos($html, ' ', $word_start_pos);

                        if ($word_next_pos) {
                            $word_length = $word_next_pos - $word_start_pos;
                            $word = substr($html, $word_start_pos, $word_length);
                            $word = str_replace('@', '', $word);

                            if (in_array($word, $at_who_array)) {
                                $j = $word_next_pos;
                            } else {
                                $break_point = $word_start_pos;

                                break;
                            }
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }

                if ($break_point != null) {
                    $show_part   = substr($html, 0, $break_point);
                    $hidden_part = "<span class='extend none'>" . substr($html, $break_point) . "</span>";
                    $show_more   = "<a class='more'><span>...</span> more</a>";
                    $html        = $show_part . $hidden_part . $show_more;
                }
            }

            foreach ($at_whos as $at_who) {
                $at_str = '@' . $at_who->name;
                $html   = str_replace($at_str, $at_who->show_link, $html);
            }
        }

        return emoji($html);
    }

    /**
     * Get note display related parent module type.
     *
     * @return string
     */
    public function getDisplayTypeAttribute()
    {
        return display_field($this->linked_type, ['staff' => 'User']);
    }

    /**
     * Get note info history link.
     *
     * @param \App\Model\Revision $history
     *
     * @return string
     */
    public function historyLink($history)
    {
        return "<a href='{$this->linked->getShowRouteAttribute([$this->linked_id, 'notes'])}'
                   class='like-txt' target='_blank'><span class='icon {$this->linked->icon}' data-toggle='tooltip'
                   data-placement='top' title='" . ucfirst($this->linked->identifier) . "'></span> " .
                   $this->linked->historyVal('name', $history->created_at)['closest'] .
               '</a>';
    }

    /**
     * Get updated type history information.
     *
     * @param \App\Model\Revision $history
     *
     * @return string|null
     */
    public function updatedHistoryInfo($history)
    {
        if ($history->root != $this->linked_type && $history->root_id != $this->linked_id) {
            if ($history->key == 'description') {
                return '<br>' . $this->historyLink($history);
            }
        }

        return null;
    }

    /**
     * Get the user who last updated the specified resource.
     *
     * @return \App\Models\User
     */
    public function updatedBy()
    {
        $last = $this->revisionHistory->last();

        if (isset($last)) {
            return $last->userResponsible();
        } elseif ($this->notes->count()) {
            return $this->notes->first()->createdBy();
        }

        return Staff::superAdmin();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * A one-to-many relationship with Note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(Note::class, 'note_info_id');
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

    /**
     * Polymorphic one-to-many relationship with AttachFile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachfiles()
    {
        return $this->morphMany(AttachFile::class, 'linked');
    }
}
