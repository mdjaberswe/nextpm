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

use Venturecraft\Revisionable\FieldFormatter;
use Venturecraft\Revisionable\Revision as VenturecraftRevision;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends VenturecraftRevision
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'revisions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['revisionable_type', 'revisionable_id', 'user_id', 'key', 'old_value', 'new_value'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['revisionable_name', 'description'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Don't display field array list.
     *
     * @var array
     */
    protected $revision_hide = ['*.linked_type'];

    /**
     * Display field name.
     *
     * @var array
     */
    protected $revisionable_display = ['attach_file' => 'Attachment', 'staff' => 'User'];

    /**
     * Parent module list array.
     *
     * @var array
     */
    protected static $types = ['project', 'milestone', 'task', 'issue', 'event', 'staff'];

    /**
     * History load validation.
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
            'latestid' => 'required|exists:revisions,id,deleted_at,NULL',
        ]);
    }

    /**
     * Get a valid related module list.
     *
     * @return array
     */
    public static function types()
    {
        return self::$types;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * Group by revisionable type and id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroupByGet($query)
    {
        $query->groupBy('revisionable_type')->groupBy('revisionable_id')->groupBy('created_at')->get();
    }

    /**
     * The query for not to show hidden field history.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByType($query)
    {
        return $query->whereNotIn('revisionable_type', $this->revision_hide);
    }

    /**
     * The query for getting only valid revisionable|module type history.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyValidType($query)
    {
        return $query->whereIn('revisionable_type', self::types());
    }

    /**
     * Get a history record by type, type id, history id, field conditions, and proper order.
     *
     * @param string $type
     * @param int    $type_id
     * @param string $operator
     * @param int    $history_id
     * @param string $key
     * @param bool   $asc_order
     *
     * @return \App\Models\Revision
     */
    public static function getHistoryRow($type, $type_id, $operator, $history_id, $key, $asc_order = true)
    {
        $row = self::where('revisionable_type', $type)
                   ->where('revisionable_id', $type_id)
                   ->where('id', $operator, $history_id)
                   ->where('key', $key);

        return $asc_order ? $row->first() : $row->latest('id')->first();
    }

    /**
     * Record group of histories.
     *
     * @param string   $revisionable_type
     * @param array    $revisionable_ids
     * @param string   $key
     * @param mixed    $old_value
     * @param mixed    $new_value
     * @param int|null $user_id
     *
     * @return void
     */
    public static function secureHistory(
        $revisionable_type,
        $revisionable_ids,
        $key,
        $old_value,
        $new_value,
        $user_id = null
    ) {
        $histories  = [];
        $user_id    = is_null($user_id) ? auth()->user()->id : $user_id;
        $created_at = $updated_at = date('Y-m-d H:i:s');

        foreach ($revisionable_ids as $revisionable_id) {
            $histories[] = compact('revisionable_type', 'revisionable_id', 'user_id', 'key', 'old_value', 'new_value', 'created_at', 'updated_at');
        }

        self::insert($histories);
    }

    /**
     * Record bulk update histories.
     *
     * @param string                                   $revisionable_type
     * @param \Illuminate\Database\Eloquent\Collection $revisionables
     * @param array                                    $new_value
     *
     * @return void
     */
    public static function secureBulkUpdatedHistory($revisionable_type, $revisionables, $new_value)
    {
        foreach ($revisionables as $revisionable) {
            foreach ($new_value as $field => $new_val) {
                $old_val = $revisionable->$field;

                if ($old_val != $new_val) {
                    self::create([
                        'key'               => $field,
                        'old_value'         => $old_val,
                        'new_value'         => $new_val,
                        'user_id'           => auth()->user()->id,
                        'revisionable_type' => $revisionable_type,
                        'revisionable_id'   => $revisionable->id,
                    ]);
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get revisionable display name.
     *
     * @return string
     */
    public function getRevisionableNameAttribute()
    {
        return display_field($this->revisionable_type, $this['revisionable_display']);
    }

    /**
     * Get field display name.
     *
     * @return string
     */
    public function getFieldNameAttribute()
    {
        return display_field($this->key);
    }

    /**
     * Get the key identifier of the history.
     *
     * @return string
     */
    public function getKeyElementAttribute()
    {
        return 'history-' . $this->revisionable_type . ' key-' . str_replace('_', ' key-', $this->key);
    }

    /**
     * Get created at in a short readable format.
     *
     * @return string
     */
    public function getQuickCreatedAttribute()
    {
        return $this->created_at->format('M j, Y');
    }

    /**
     * Get created at in readable AM|PM format.
     *
     * @return string
     */
    public function getCreatedAmpmAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get history timeline HTML.
     *
     * @return sntring
     */
    public function getTimelineHtmlAttribute()
    {
        $global_hide = '*.' . $this->key;
        $type_hide   = $this->revisionable_type . '.' . $this->key;

        if (! in_array($global_hide, $this['revision_hide']) && ! in_array($type_hide, $this['revision_hide'])) {
            return "<div class='timeline-info {$this->key_element}' data-id='{$this->id}'>
                        <div class='timeline-icon'>
                            {$this->timeline_html_tags['icon']}
                        </div>

                        <div class='timeline-details'>
                            <div class='timeline-title'>
                                {$this->timeline_html_tags['title']}
                            </div>

                            <div class='timeline-record'>
                                {$this->timeline_html_tags['user']}
                            </div>
                        </div>
                    </div>";
        }

        return null;
    }

    /**
     * Get history bottom information tags.
     *
     * @return string
     */
    public function getTimelineHtmlTagsAttribute()
    {
        $outcome = [];
        $outcome['user'] = "<span>by</span>
                            <span class='plain'>{$this->user->linked->name_link}</span>
                            <span data-toggle='tooltip' data-placement='bottom' title='{$this->created_ampm}'>
                                {$this->quick_created}
                            </span>";

        // Get history title according to history type created|updated|deleted.
        if ($this->key == 'created_at') {
            $outcome['icon']  = "<i class='" . module_icon($this->revisionable_type . '_create') . "'
                                    data-toggle='tooltip' data-placement='bottom' title='{$this->created_type}'>
                                </i>";
            $outcome['title'] = "<p>{$this->created_title}</p>";
        } elseif ($this->key == 'deleted_at') {
            $outcome['icon']  = "<i class='mdi mdi-delete-forever'
                                    data-toggle='tooltip'
                                    data-placement='bottom'
                                    title='Deleted'>
                                </i>";
            $outcome['title'] = "<p>{$this->deleted_title}</p>";
        } else {
            $outcome['icon']  = "<i class='" . module_icon($this->key, 'fa fa-pencil') . "'
                                    data-toggle='tooltip'
                                    data-placement='bottom'
                                    title='Updated'>
                                </i>";
            $outcome['title'] = "<p>{$this->updated_title}</p>";
        }

        return $outcome;
    }

    /**
     * Get creation display type.
     *
     * @return string
     */
    public function getCreatedTypeAttribute()
    {
        if ($this->is_last && $this->is_root) {
            return 'Created';
        }

        return 'Added';
    }

    /**
     * Get created type history title.
     *
     * @return string
     */
    public function getCreatedTitleAttribute()
    {
        $title = $this->revisionable_name . ' ' . $this->created_type;

        if ($this->created_type == 'Added'
            || ($this->created_type == 'Created'
            && $this->revisionable_type == 'staff'
            && $this->user_id != $this->revisionable_id)
        ) {
            $title .= '<br>' . $this->revisionable->historyLink($this);
        }

        return $title;
    }

    /**
     * Get an updated type history title.
     *
     * @return string
     */
    public function getUpdatedTitleAttribute()
    {
        $title = $this->full_field_name . ' was updated';

        if ($this->has_long_value) {
            $title .= "<br>From: {$this->getVal('oldValue')}<br>To: <strong>{$this->getVal('newValue')}</strong>";
        } else {
            $title .= " from <strong>{$this->getVal('oldValue')}</strong> to <strong>{$this->getVal()}</strong>";
        }

        if (! $this->is_root && method_exists($this->revisionable, 'updatedHistoryInfo')) {
            $title .= $this->revisionable->updatedHistoryInfo($this);
        }

        return $title;
    }

    /**
     * Get field name with prefix.
     *
     * @return string
     */
    public function getFullFieldNameAttribute()
    {
        if (! is_null($this->field_prefix) && ! (strpos($this->fieldName(), $this->field_prefix) !== false)) {
            return $this->field_prefix . ' ' . $this->fieldName();
        }

        return $this->fieldName();
    }

    /**
     * Get field prefix.
     *
     * @return string|null
     */
    public function getFieldPrefixAttribute()
    {
        if ($this->revisionable_type == $this->root) {
            return null;
        }

        if (in_array($this->revisionable_type, ['note_info', 'attach_file'])
            && $this->revisionable->linked_type != $this->root
        ) {
            return ucfirst($this->revisionable->linked_type);
        } elseif ($this->revisionable_type == 'note'
            && $this->revisionable->info->linked_type != $this->root
        ) {
            return ucfirst($this->revisionable->info->linked_type);
        } else {
            if ($this->revisionable_type != 'note_info') {
                return ucfirst($this->revisionable_type);
            }
        }

        return null;
    }

    /**
     * Get to know about long value status.
     *
     * @return bool
     */
    public function getHasLongValueAttribute()
    {
        $old_value = strip_tags($this->oldValue());
        $new_value = strip_tags($this->newValue());

        if (strlen($old_value) > 25 || strlen($new_value) > 25) {
            return true;
        }

        return false;
    }

    /**
     * Get deleted type history title.
     *
     * @return string
     */
    public function getDeletedTitleAttribute()
    {
        return $this->revisionable_name . ' Deleted <br>' . $this->revisionable->historyLink($this);
    }

    /**
     * Get to know the history is last or not.
     *
     * @return bool
     */
    public function getIsLastAttribute()
    {
        return ($this->id == $this->revisionable->last_history_id);
    }

    /**
     * Get to know the history root status.
     *
     * @return bool
     */
    public function getIsRootAttribute()
    {
        $is_root = ($this->revisionable_type == $this->root) && ($this->revisionable_id == $this->root_id);

        if ($this->revisionable_type == 'user' && $this->root == 'staff') {
            $is_root = ($this->revisionable->linked_id == $this->root_id);
        }

        return $is_root;
    }

    /**
     * Get the history value.
     *
     * @param string $value
     * @param int    $limit
     *
     * @return mixed
     */
    public function getVal($value = 'newValue', $limit = 25)
    {
        $value = $this->$value();
        $limit = $this->has_long_value ? 70 : $limit;

        if ($value != strip_tags($value)) {
            return $value;
        }

        return str_limit($value, $limit);
    }

    /**
     * Get new value.
     *
     * @return mixed
     */
    public function newValue()
    {
        if ($this->key == 'linked_id') {
            return $this->getLinkedName();
        }

        return parent::newValue();
    }

    /**
     * Get old value.
     *
     * @return mixed
     */
    public function oldValue()
    {
        if ($this->key == 'linked_id') {
            return $this->getLinkedName('old_value');
        }

        return parent::oldValue();
    }

    /**
     * Get a related module name.
     *
     * @param string $history_type
     *
     * @return string
     */
    public function getLinkedName($history_type = 'new_value')
    {
        if (not_null_empty($this->linked_type[$history_type]) && not_null_empty($this->$history_type)) {
            $linked = morph_to_model($this->linked_type[$history_type])::withTrashed()
                                                                       ->where('id', $this->$history_type)
                                                                       ->first();

            if (isset($linked)) {
                return $linked->name;
            }
        }

        return 'None';
    }

    /**
     * Get related module type name.
     *
     * @return array
     */
    public function getLinkedTypeAttribute()
    {
        $outcome = ['old_value' => null, 'new_value' => null];

        if ($this->key == 'linked_id') {
            $linked_type_history = self::where('revisionable_type', $this->revisionable_type)
                                       ->where('revisionable_id', $this->revisionable_id)
                                       ->where('user_id', $this->user_id)
                                       ->where('created_at', $this->created_at)
                                       ->where('key', 'linked_type')
                                       ->first();

            if (isset($linked_type_history)) {
                $outcome = [
                    'old_value' => $linked_type_history->old_value,
                    'new_value' => $linked_type_history->new_value,
                ];
            } else {
                $linked_type = $this->revisionable->historyVal('linked_type', $this->created_at)['closest'];
                $outcome     = ['old_value' => $linked_type, 'new_value' => $linked_type];
            }
        }

        return $outcome;
    }

    /**
     * Format the value according to the $revisionFormattedFields array.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function format($key, $value)
    {
        $related_model = morph_to_model($this->revisionable_type);
        $related_model = new $related_model;
        $revisionFormattedFields = $related_model->getRevisionFormattedFields();

        if (isset($revisionFormattedFields[$key])) {
            return FieldFormatter::format($key, $value, $revisionFormattedFields);
        } else {
            return not_null_empty($value) ? $value : 'Blank Value';
        }
    }

    /**
     * Format field names and allow overrides for field names.
     *
     * @param string $key
     *
     * @return string
     */
    protected function formatFieldName($key)
    {
        $related_model = morph_to_model($this->revisionable_type);
        $related_model = new $related_model;
        $revisionFormattedFieldNames = $related_model->getRevisionFormattedFieldNames();

        if (isset($revisionFormattedFieldNames[$key])) {
            return $revisionFormattedFieldNames[$key];
        }

        return display_field($key);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    /**
     * An inverse one-to-many relationship with User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * A polymorphic, inverse one-to-many relationship with different Modules.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function revisionable()
    {
        return $this->morphTo()->withTrashed();
    }
}
