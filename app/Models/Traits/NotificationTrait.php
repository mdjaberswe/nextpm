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

namespace App\Models\Traits;

use App\Models\FilterView;
use Venturecraft\Revisionable\FieldFormatter;

trait NotificationTrait
{
    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get the case name.
     *
     * @return string
     */
    public function getCaseAttribute()
    {
        return $this->data['case'];
    }

    /**
     * Get the notification description.
     *
     * @return string|null
     */
    public function getDescriptionAttribute()
    {
        if (! is_null($this->data['info'])
            && is_array($this->data['info'])
            && array_key_exists('description', $this->data['info'])
        ) {
            return $this->data['info']['description'];
        }

        return null;
    }

    /**
     * Add|Remove "Follower" notification associated with the related module.
     *
     * @return string
     */
    public function getFollowerNotificationAttribute()
    {
        $title = null;

        if (strrpos($this->case, 'follower_added') !== false) {
            $title = $this->data['info']['name'] . ' is following the ' . $this->related_type;
        } elseif (strrpos($this->case, 'follower_removed') !== false) {
            $title = $this->data['info']['name'] . ' unfollow the ' . $this->related_type;
        }

        return $title;
    }

    /**
     * Add|Remove "Allowed User" notification associated with the related module.
     *
     * @return string
     */
    public function getAllowedUserNotificationAttribute()
    {
        $title = null;

        if (strrpos($this->case, 'observer_added') !== false) {
            $title = 'Allowed <b>' . $this->data['info']['count'] . '</b> new ' .
                     str_plural('user', $this->data['info']['count']) . ' in a ' . $this->related_type;
        } elseif (strrpos($this->case, 'observer_removed') !== false) {
            $title = 'Removed <b>' . $this->data['info']['count'] . '</b> ' .
                     str_plural('user', $this->data['info']['count']) . ' from a ' . $this->related_type;
        }

        return $title;
    }


    /**
     * "Add|Removed member" notification associated with the related project.
     *
     * @return string
     */
    public function getMemberNotificationAttribute()
    {
        $title = null;

        if ($this->case == 'project_member_added') {
            // Countable members notification else the notification mentioned added member name
            if (is_int($this->data['info']['new_member'])) {
                $title = 'Added <b>' . $this->data['info']['new_member'] .
                         '</b> new ' . str_plural('member', $this->data['info']['new_member']) . ' in a project';
            } elseif ($this->data['info']['new_member'] == true) {
                $title = 'Added <b>' . $this->notifiable->linked->name . '</b> in a project';
            }
        } elseif ($this->case == 'project_member_removed') {
            $title = 'Removed a member from project';
        }

        return $title;
    }

    /**
     * "Post|Update|Pin note" notification associated with the related module.
     *
     * @return string
     */
    public function getNoteNotificationAttribute()
    {
        $title = null;

        if (strrpos($this->case, 'note_added') !== false) {
            $title = 'Posted a comment on ' . $this->related_type;
        } elseif (strrpos($this->case, 'note_edited') !== false) {
            $title = 'Updated a comment in ' . $this->related_type;
        } elseif (strrpos($this->case, 'note_pin') !== false) {
            $pin_status = $this->data['info']['pin'] ? 'Pinned' : 'Unpinned';
            $title      = $pin_status . ' a comment in ' . $this->related_type;
        }

        return $title;
    }

    /**
     * "Add|Removed file" notification associated with the related module.
     *
     * @return string
     */
    public function getFileNotificationAttribute()
    {
        $title = null;

        if (strrpos($this->case, 'file_added') !== false) {
            if (is_int($this->data['info']['file_count'])) {
                $title = 'Added <b>' . $this->data['info']['file_count'] . '</b> ' .
                         str_plural('file', $this->data['info']['file_count']) . ' in a ' . $this->related_type;
            } elseif ($this->data['info']['file_count'] == false) {
                $title = 'Added a link in ' . $this->related_type;
            }
        } elseif (strrpos($this->case, 'file_removed') !== false) {
            $title = 'Deleted a ' . $this->data['info']['file_type'] . ' from ' . $this->related_type;
        }

        return $title;
    }

    /**
     * Get the related module name of the specified notification.
     *
     * @return string
     */
    public function getRelatedTypeAttribute()
    {
        return $this->data['module'];
    }

    /**
     * Get related module id of the specified notification.
     *
     * @return int
     */
    public function getRelatedIdAttribute()
    {
        if (is_array($this->data['module_id'])) {
            return $this->data['module_id'][0];
        }

        return $this->data['module_id'];
    }

    /**
     * Get a related module of the specified notification.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRelatedAttribute()
    {
        return morph_to_model($this->related_type)::withTrashed()->find($this->related_id);
    }

    /**
     * Get additional information if the specified notification has a related module.
     *
     * @return string
     */
    public function getAdditionalInfoAttribute()
    {
        $outcome = null;

        if ($this->related
            && ! is_array($this->data['module_id'])
            && ! in_array($this->data['module'], ['import'])
        ) {
            $outcome = '<p>' . snake_to_ucwords($this->related_type) .
                       ': ' . non_property_checker($this->related, 'name') . '</p>';

            if ($this->related_type == 'milestone') {
                $outcome .= $this->related_additional_info;
            }
        } elseif ($this->data['module'] == 'import') {
            $outcome = "<p class='m3-0'>" .
                          'Added: ' . $this->data['info']['count']['created'] . " <span class='color-shadow fog'>|</span> " .
                          'Updated: ' . $this->data['info']['count']['updated'] . " <span class='color-shadow fog'>|</span> " .
                          'Skipped: ' . $this->data['info']['count']['skipped'] .
                       '</p>';
        }

        return $outcome;
    }

    /**
     * Get related module additional information of the specified notification.
     *
     * @return string
     */
    public function getRelatedAdditionalInfoAttribute()
    {
        $outcome = null;

        if (! is_null($this->related->linked)) {
            $outcome = '<p>' . snake_to_ucwords($this->related->linked_type) .
                       ': ' . non_property_checker($this->related->linked, 'name') . '</p>';
        }

        return $outcome;
    }

    /**
     * Get related module link 'href' attribute value of the specified notification.
     *
     * @return string|null
     */
    public function getLinkAttribute()
    {
        if ($this->related
            && ! is_array($this->data['module_id'])
            && ! in_array($this->data['module'], ['import'])
        ) {
            return $this->related->getShowRouteAttribute($this->link_param);
        }

        return null;
    }

    /**
     * Get related module link param of the specified notification.
     *
     * @return array|int
     */
    public function getLinkParamAttribute()
    {
        // Show page link route with additional parameter notes, files.
        if (strrpos($this->case, 'note') !== false) {
            return [$this->related_id, 'notes'];
        } elseif (strrpos($this->case, 'file') !== false) {
            return [$this->related_id, 'files'];
        }

        return $this->related_id;
    }

    /**
     * Get related module field list that value needs to be formatted to display.
     *
     * @return array
     */
    public function getFormattedFieldArrayAttribute()
    {
        return $this->related->getRevisionFormattedFields();
    }

    /**
     * Get related module field names list that needs to be formatted to display.
     *
     * @return array
     */
    public function getFormattedFieldNameArrayAttribute()
    {
        return $this->related->getRevisionFormattedFieldNames();
    }

    /**
     * Get a displayable field name.
     *
     * @param string      $key
     * @param string|null $alternative
     *
     * @return string
     */
    public function getDisplayField($key, $alternative)
    {
        if (isset($this->formatted_field_name_array[$key])) {
            return $this->formatted_field_name_array[$key];
        }

        return is_null($alternative) ? display_field($key) : $alternative;
    }

    /**
     * Get readable formatted value to display.
     *
     * @param string $key
     * @param mixed  $value
     * @param string $type
     *
     * @return string
     */
    public function getDisplayValue($key, $value, $type)
    {
        if (isset($this->formatted_field_array[$key])) {
            return FieldFormatter::format($key, $value, $this->formatted_field_array);
        } elseif ($key == 'linked_id') {
            return $this->getLinkedName($value, $type);
        } elseif (not_null_empty($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Get a related module name.
     *
     * @param integer $linked_id
     * @param string  $type
     *
     * @return string
     */
    public function getLinkedName($linked_id, $type)
    {
        if (array_key_exists('linked_type', $this->data['info'])) {
            $linked_module = $this->data['info']['linked_type'];
        } else {
            $linked_module = collect($this->data['info'])->where('key', 'linked_type')->first();
            $linked_module = isset($linked_module) ? $linked_module[$type] : null;
        }

        if (not_null_empty($linked_module) && not_null_empty($linked_id)) {
            $linked = morph_to_model($linked_module)::withTrashed()->where('id', $linked_id)->first();

            if (isset($linked)) {
                return $linked->name;
            }
        }

        return 'None';
    }

    /**
     * Get updated value details info HTML.
     *
     * @return string
     */
    public function getUpdatedValueHtmlAttribute()
    {
        $html = '';

        if (! is_null($this->data['info']) && is_array($this->data['info'])) {
            foreach ($this->data['info'] as $update_info) {
                if (! in_array($update_info['key'], ['created_at', 'linked_type'])) {
                    $html .= $this->getSingleUpdatedValueHtml($update_info);
                }
            }
        }

        return $html;
    }

    /**
     * Get single field updated value HTML.
     *
     * @param array $update_info
     * @param array $hidden
     *
     * @return string
     */
    public function getSingleUpdatedValueHtml($update_info, $hidden = [])
    {
        $old  = in_array('old', $hidden)
                ? null : $this->getDisplayValue($update_info['key'], $update_info['old_value'], 'old_value');
        $new  = in_array('new', $hidden)
                ? null : $this->getDisplayValue($update_info['key'], $update_info['new_value'], 'new_value');
        $html = "<br><span class='list-item'>" . $this->getDisplayField($update_info['key'], $update_info['field']) .
                ": <b class='line-cut'>" . $old . "</b> <b class='color-success'>" . $new . '</b></span>';

        return $html;
    }

    /**
     * Get notification title.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        $title = null;
        $item  = snake_to_space($this->related_type);

        // Get title when notifications case related with module "created", "updated", "deleted",
        // "mass updated", "mass deleted", "follower|allowed user", "project member", "note", "attached file"
        if (strrpos($this->case, 'created') !== false) {
            return 'Created ' . vowel_checker($item);
        } elseif (strrpos($this->case, 'updated') !== false) {
            return 'Updated ' . vowel_checker($item) . $this->updated_value_html;
        } elseif (strrpos($this->case, 'deleted') !== false) {
            return 'Deleted ' . vowel_checker($item);
        } elseif (strrpos($this->case, 'mass_changed') !== false) {
            return 'Updated <b>' . $this->data['info']['count'] . '</b> ' .
                    str_plural($item, $this->data['info']['count']) .
                    $this->getSingleUpdatedValueHtml($this->data['info'], ['old']);
        } elseif (strrpos($this->case, 'mass_removed') !== false) {
            return 'Deleted <b>' . $this->data['info']['count'] . '</b> ' .
                    str_plural($item, $this->data['info']['count']);
        } elseif (strrpos($this->case, 'observer') !== false) {
            return $this->allowed_user_notification;
        } elseif (strrpos($this->case, 'follower') !== false) {
            return $this->follower_notification;
        } elseif (strrpos($this->case, 'project_member') !== false) {
            return $this->member_notification;
        } elseif (strrpos($this->case, 'note') !== false) {
            return $this->note_notification;
        } elseif (strrpos($this->case, 'file') !== false) {
            return $this->file_notification;
        } elseif (strrpos($this->case, 'import') !== false) {
            return $this->data['info']['field'] . ' imported from ' .
                   "<span class='like-link add-multiple' {$this->related->data_btn}>" .
                        $this->data['info']['file_name'] .
                   '</span>';
        } else {
            return $this->description;
        }

        return $title;
    }

    /**
     * Get the user link who is responsible for the specified notification.
     *
     * @return string
     */
    public function getNotificationFromAttribute()
    {
        return is_null($this->createdBy())
               ? $this->notifiable->linked->profile_html
               : $this->createdBy()->linked->profile_html;
    }

    /**
     * Get the user name who is responsible for the specified notification.
     *
     * @return string
     */
    public function getCreatedByNameAttribute()
    {
        return is_null($this->createdByName()) ? $this->notifiable->linked->name : $this->createdByName();
    }

    /**
     * Get the user avatar who is responsible for the specified notification.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return is_null($this->createdByAvatar()) ? $this->notifiable->linked->avatar : $this->createdByAvatar();
    }

    /**
     * Get the specified notification class according to user read|unread the notification.
     *
     * @return string
     */
    public function getCssClassAttribute()
    {
        return is_null($this->read_at) ? 'unread' : 'read';
    }

    /**
     * Get the notification list item HTML.
     *
     * @return string
     */
    public function getListHtmlAttribute()
    {
        $href = $this->link ? "href='$this->link'" : '';

        return "<li data-id='{$this->id}' class='{$this->css_class}' data-time='{$this->created_at->timestamp}'>
                    <a $href class='dropdown-notification'>
                        <img src='{$this->avatar}' alt='User Name'>
                        <p class='time' data-toggle='tooltip' data-placement='left' title='" .
                        $this->getCreatedTimeAmpmAttribute(true) . "'>" .
                        $this->getCreatedShortFormatAttribute(true) . '</p>
                        <h5>' . str_limit($this->created_by_name, 20, '.') . "</h5>
                        <p>{$this->title}</p>" .
                        $this->additional_info . '
                    </a>
                </li>';
    }

    /**
     * The query for getting authenticate user notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthOnly($query)
    {
        return $query->where('notifiable_id', auth()->user()->id)->where('notifiable_type', 'user');
    }

    /**
     * The query for filtering notifications data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterViewData($query)
    {
        // Get current filter and query by its parameter period, owner condition, related module
        $current_filter  = FilterView::getCurrentFilter('notification');
        $dates           = time_period_dates($current_filter->getParamVal('timeperiod'));
        $query           = $query->WithinCreated($dates['start_date'], $dates['end_date']);
        $owner_condition = $current_filter->getParamCondition('owner');
        $related_module  = $current_filter->getParamCondition('related');

        if ($owner_condition == 'equal' || $owner_condition == 'not_equal') {
            $created_by_user = ($owner_condition == 'equal');
            $owner = $current_filter->getParamVal('owner');
            $query = $query->createdByUser($owner, $created_by_user);
        }

        if (! is_null($related_module)) {
            $query = $query->where('data', 'LIKE', '%' . '"module":"' . $related_module . '"' . '%');
            $related_module_id = $current_filter->getParamVal('related');

            if (! is_null($related_module_id)) {
                $query = $query->where('data', 'LIKE', '%' . '"module_id":' . $related_module_id . '%');
            }
        }


        return $query;
    }

    /**
     * Get new notifications.
     *
     * @param \Carbon\Carbon $last_created_at
     * @param bool           $html
     * @param string         $order_type
     *
     * @return \Illuminate\Notifications\DatabaseNotification|array
     */
    public static function getNewNotificationsData($last_created_at = null, $html = true, $order_type = 'asc')
    {
        $html_array        = [];
        $condition         = is_null($last_created_at) ? '!=' : '>';
        $new_notifications = self::authOnly()->where('created_at', $condition, $last_created_at)
                                             ->orderBy('created_at', $order_type)
                                             ->get();

        if (! $html) {
            return $new_notifications;
        }

        if ($new_notifications->count()) {
            foreach ($new_notifications as $notification) {
                $html_array[] = ['id' => $notification->id, 'html' => $notification->list_html];
            }
        }

        return [
            'html' => $html_array,
            'data' => $new_notifications,
        ];
    }

    /**
     * Get notifications data table format.
     *
     * @return array
     */
    public static function getNotificationTableFormat()
    {
        return [
            'checkbox'     => false,
            'action'       => false,
            'thead'        => ['notification from', 'description', 'related to', 'date'],
            'json_columns' => \DataTable::jsonColumn(['notification_from', 'description', 'related_to', 'date'], self::hideColumns()),
        ];
    }

    /**
     * Get notifications table data.
     *
     * @param \Illuminate\Notifications\DatabaseNotification $notifications
     * @param \Illuminate\Http\Request                       $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getNotificationTableData($notifications, $request)
    {
        return \DataTable::of($notifications)->addColumn('notification_from', function ($notification) {
            return $notification->notification_from;
        })->addColumn('description', function ($notification) {
            if ($notification->data['module'] == 'import') {
                return $notification->title . $notification->additional_info;
            }

            return $notification->title;
        })->addColumn('related_to', function ($notification) {
            return non_property_checker($notification->related, 'name_link_icon');
        })->addColumn('date', function ($notification) {
            return $notification->created_at->format('M j, Y g:i A');
        })->make(true);
    }
}
