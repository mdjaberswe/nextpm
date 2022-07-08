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

namespace App\Library;

class ChartData
{
    protected $progress_step;
    protected $progress_field;

    /**
     * Create a new chart instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->progress_step  = $this->setProgressStep();
        $this->progress_field = $this->setProgressField();
    }

    /**
     * Set progress steps array.
     *
     * @return array
     */
    public function setProgressStep()
    {
        return [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
    }

    /**
     * Get progress steps array.
     *
     * @return array
     */
    public static function getProgressStep()
    {
        return with(new static)->progress_step;
    }

    /**
     * Set progress field.
     *
     * @return string
     */
    public function setProgressField()
    {
        return 'completion_percentage';
    }

    /**
     * Get progress field name.
     *
     * @return string
     */
    public static function getProgressField()
    {
        return with(new static)->progress_field;
    }

    /**
     * Chart colors array list.
     *
     * @var array
     */
    protected static $colorlist = [
        [255, 135, 135, 1],
        [255, 171, 215, 1],
        [165, 146, 247, 1],
        [69, 176, 247, 1],
        [35, 236, 255, 1],
        [81, 253, 199, 1],
        [124, 237, 138, 1],
        [198, 237, 124, 1],
        [255, 152, 126, 1],
        [255, 183, 116, 1],
        [255, 255, 137, 1],
        [255, 245, 206, 1],
        [255, 115, 0, 1],
        [255, 175, 0, 1],
        [255, 236, 0, 1],
        [213, 243, 11, 1],
        [82, 215, 38, 1],
        [27, 170, 47, 1],
        [45, 203, 117, 1],
        [38, 215, 174, 1],
        [124, 221, 221, 1],
        [95, 183, 212, 1],
        [151, 217, 255, 1],
        [0, 126, 214, 1],
        [131, 153, 235, 1],
        [142, 108, 239, 1],
        [156, 70, 208, 1],
        [199, 88, 208, 1],
        [224, 30, 132, 1]
    ];

    /**
     * Get ideal chart colors array.
     *
     * @return array
     */
    public static function getColor()
    {
        return self::$colorlist;
    }

    /**
     * Generate nth different RGB color.
     *
     * @param int $nth
     *
     * @return string
     */
    public static function rgbaColor($nth)
    {
        $chart_color       = self::getColor();
        $total_chart_color = count($chart_color);

        if ($nth <= ($total_chart_color - 1)) {
            $color = $chart_color[$nth];
        } else {
            $remainder = ($nth + 1) % $total_chart_color;
            $color     = $chart_color[$remainder];
        }

        return "rgba($color[0], $color[1], $color[2], $color[3])";
    }

    /**
     * Get Completion progress chart data.
     *
     * @param array      $source
     * @param string     $start
     * @param string     $end
     * @param array|null $owner
     *
     * @return array
     */
    public static function getProgressChartData($source, $start = null, $end = null, $owner = null)
    {
        $data = [];
        $progress_list = self::getProgressStep();

        if (array_key_exists('identifier', $source)) {
            $model = morph_to_model($source['identifier']);

            foreach ($progress_list as $progress) {
                $progress_step = $model::where(self::getProgressField(), $progress)->whereInOwner($owner);

                if (not_null_empty($start) && not_null_empty($end)) {
                    $progress_step = $progress_step->withinPeriod($start, $end);
                }

                $data[] = $progress_step->get()->count();
            }
        } else {
            $parent = $source['parent'];
            $child  = $source['child'];

            foreach ($progress_list as $progress) {
                $data[] = $parent->$child()->where(self::getProgressField(), $progress)->get()->count();
            }
        }

        $outcome['data'] = $data;
        $outcome['max']  = max($data) + 4;
        $outcome['min']  = min_zero(min($data) - 3);
        $outcome['step'] = floor(($outcome['max'] - $outcome['min']) / 5);

        return $outcome;
    }

    /**
     * Get a proper array format for creating a new pie chart.
     *
     * @param string                                   $item_morph
     * @param string                                   $category_morph
     * @param string                                   $category_id
     * @param string|null                              $category_query
     * @param \Illuminate\Database\Eloquent\Model|null $item_parent
     * @param array                                    $options
     * @param int|null                                 $owner
     * @param string                                   $start
     * @param string                                   $end
     *
     * @return array
     */
    public static function getPieData(
        $item_morph,
        $category_morph = null,
        $category_id = null,
        $category_query = null,
        $item_parent = null,
        $options = [],
        $owner = null,
        $start = null,
        $end = null
    ) {
        $category_names = [];
        $items_count    = [];
        $backgrounds    = [];
        $string_names   = '';
        $string_count   = '';
        $string_bg      = '';
        $items          = $item_morph . 's';
        $item_model     = morph_to_model($item_morph);

        // Get category morph and id from item morph.
        if (is_null($category_morph)) {
            $category_morph = $item_morph . '_status';
            $category_id    = $category_morph . '_id';
        }

        // If $category_morph is an array, then we have static categories array
        // else we need to convert morph to category model and get categories data.
        if (is_array($category_morph)) {
            $categories = $category_morph;
        } else {
            $category_model = morph_to_model($category_morph);

            if (is_null($category_query)) {
                $categories = $category_model::orderBy('id')->get();
            } else {
                $categories = $category_model::$category_query();
            }
        }

        $nth = 0;
        $whereClause = ! is_null($owner) ? 'whereIn' : 'whereNot';
        $whereField  = ! is_null($owner) ? $item_morph . '_owner' : 'id';

        foreach ($categories as $category) {
            // If item parent is null, then get category count by filtering within the period
            // and owner else get category count by item parent "Has Many" relationship with category.
            if (is_null($item_parent)) {
                $wherePeriod    = 'whereNot';
                $period_param_1 = 'id';
                $period_param_2 = null;

                if (method_exists($item_model, 'scopeWithinPeriod') && not_null_empty($start) && not_null_empty($end)) {
                    $wherePeriod    = 'withinPeriod';
                    $period_param_1 = $start;
                    $period_param_2 = $end;
                }

                if (is_object($category)) {
                    $count = $item_model::where($category_id, $category->id)
                                        ->$whereClause($whereField, $owner)
                                        ->$wherePeriod($period_param_1, $period_param_2)
                                        ->get()
                                        ->count();
                } else {
                    $count = $item_model::orderBy('id')
                                        ->$whereClause($whereField, $owner)
                                        ->$wherePeriod($period_param_1, $period_param_2)
                                        ->get()
                                        ->where($category_id, $category)
                                        ->count();
                }
            } else {
                if (is_object($category)) {
                    $count = $item_parent->$items()->where($category_id, $category->id)->get()->count();
                } else {
                    $count = $item_parent->$items()->get()->where($category_id, $category)->count();
                }
            }

            $items_count[]    = $count;
            $category_names[] = is_object($category) ? $category->name : $category;
            $backgrounds[]    = array_key_exists('background', $options)
                                ? $options['backgrounds'][$nth] : self::rgbaColor($nth);
            $nth++;
        }

        $string_names = implode(',', $category_names);
        $string_count = implode(',', $items_count);
        $string_bg    = implode(',', $backgrounds);
        $not_empty    = ! empty(array_filter($items_count));

        return [
            'labels'            => $category_names,
            'values'            => $items_count,
            'backgrounds'       => $backgrounds,
            'string_names'      => $string_names,
            'string_count'      => $string_count,
            'string_background' => $string_bg,
            'not_empty'         => $not_empty,
        ];
    }

    /**
     * Get overall completion data filter by owner.
     *
     * @param string     $morph
     * @param string     $owner_condition
     * @param string     $start
     * @param string     $end
     * @param array|null $owner
     * @param string     $open
     * @param string     $closed
     *
     * @return array
     */
    public static function getOverallCompletionInfo(
        $morph,
        $owner_condition,
        $start,
        $end,
        $owner = null,
        $open = 'Open',
        $closed = 'Closed'
    ) {
        $outcome    = [];
        $model      = morph_to_model($morph);
        $identifier = $model::getIdentifier();
        $display    = ucfirst($identifier);

        $outcome['total_data'] = $model::orderBy('id')
                                       ->conditionalFilterQuery($identifier . '_owner', $owner_condition, $owner)
                                       ->withinPeriod($start, $end)
                                       ->get()
                                       ->count();

        $outcome['open_data'] = $model::orderBy('id')
                                      ->conditionalFilterQuery($identifier . '_owner', $owner_condition, $owner)
                                      ->withinPeriod($start, $end);

        $outcome['open_data'] = $identifier == 'milestone'
                                ? $outcome['open_data']->get()->where('status', 'Open')->count()
                                : $outcome['open_data']->onlyOpen()->get()->count();

        $outcome['closed_data'] = $model::orderBy('id')
                                        ->conditionalFilterQuery($identifier . '_owner', $owner_condition, $owner)
                                        ->withinPeriod($start, $end);

        $outcome['closed_data'] = $identifier == 'milestone'
                                  ? $outcome['closed_data']->get()->where('status', 'Closed')->count()
                                  : $outcome['closed_data']->onlyClosed()->get()->count();

        $outcome['completed_percentage'] = 0;

        $outcome['text'] = [
            'open' => $outcome['open_data'] . ' ' . $open . ' ' . str_plural($display, $outcome['open_data']),
            'closed' => $outcome['closed_data'] . ' ' . $closed . ' ' . str_plural($display, $outcome['closed_data']),
            'percentage' => '',
        ];

        // Get the percentage of closed tasks.
        if ($outcome['total_data'] > 0) {
            $outcome['completed_percentage'] = floor($outcome['closed_data'] / $outcome['total_data'] * 100);
            $outcome['text']['percentage']   = $outcome['completed_percentage'] . '% (' . $outcome['closed_data'] .
                                               ' of ' . $outcome['total_data'] . ' ' .
                                               str_plural($display, $outcome['total_data']) . ' Completed)';
        }

        return $outcome;
    }

    /**
     * Get the activity digest chart data according to filter param.
     *
     * @param string     $start
     * @param string     $end
     * @param array|null $owner
     *
     * @return array
     */
    public static function getActivityDigestChart($start, $end, $owner = null)
    {
        $data                 = [];
        $colors               = ['rgba(255, 212, 65, 1)', 'rgba(90, 163, 213, 1)', 'rgba(156, 203, 119, 1)'];
        $label_names          = ['Projects', 'Tasks', 'Milestones', 'Issues'];
        $group_names          = ['Created', 'Open', 'Closed'];
        $string_group_names   = implode(',', $group_names);
        $string_label_names   = implode(',', $label_names);
        $string_colors        = implode('|', $colors);
        $string_data          = '';

        $data['created'] = [
            'project'   => \App\Models\Project::getAuthViewData()->whereInOwner($owner)->withinCreated($start, $end)->count(),
            'task'      => \App\Models\Task::getAuthViewData()->whereInOwner($owner)->withinCreated($start, $end)->count(),
            'milestone' => \App\Models\Milestone::getAuthViewData()->whereInOwner($owner)->withinCreated($start, $end)->count(),
            'issue'     => \App\Models\Issue::getAuthViewData()->whereInOwner($owner)->withinCreated($start, $end)->count(),
        ];

        $data['open'] = [
            'project'   => \App\Models\Project::getAuthViewData()
                                              ->whereInOwner($owner)
                                              ->withinPeriod($start, $end)
                                              ->onlyOpen()
                                              ->count(),

            'task'      => \App\Models\Task::getAuthViewData()
                                           ->whereInOwner($owner)
                                           ->withinPeriod($start, $end)
                                           ->onlyOpen()
                                           ->count(),

            'milestone' => \App\Models\Milestone::getAuthViewData()
                                                ->whereInOwner($owner)
                                                ->withinPeriod($start, $end)
                                                ->get()
                                                ->filter(function ($milestone) {
                                                    return ($milestone->completion_percentage < 100);
                                                })->count(),

            'issue'     => \App\Models\Issue::getAuthViewData()
                                            ->whereInOwner($owner)
                                            ->withinPeriod($start, $end)
                                            ->onlyOpen()
                                            ->count(),
        ];

        $data['closed'] = [
            'project'   => \App\Models\Project::getAuthViewData()
                                              ->whereInOwner($owner)
                                              ->withinPeriod($start, $end)
                                              ->onlyClosed()
                                              ->count(),

            'task'      => \App\Models\Task::getAuthViewData()
                                           ->whereInOwner($owner)
                                           ->withinPeriod($start, $end)
                                           ->onlyClosed()
                                           ->count(),

            'milestone' => \App\Models\Milestone::getAuthViewData()
                                                ->whereInOwner($owner)
                                                ->withinPeriod($start, $end)
                                                ->get()
                                                ->where('completion_percentage', 100)
                                                ->count(),

            'issue'     => \App\Models\Issue::getAuthViewData()
                                            ->whereInOwner($owner)
                                            ->withinPeriod($start, $end)
                                            ->onlyClosed()
                                            ->count(),
        ];

        $string_data .= implode(',', $data['created']) . '|' .
                        implode(',', $data['open']) . '|' .
                        implode(',', $data['closed']);

        return [
            'labels'        => $label_names,
            'groups'        => $group_names,
            'data'          => $data,
            'colors'        => $colors,
            'string_labels' => $string_label_names,
            'string_groups' => $string_group_names,
            'string_data'   => $string_data,
            'string_colors' => $string_colors,
        ];
    }

    /**
     * Get Task|Issue|Milestone type work activities.
     *
     * @param string     $activity
     * @param array|null $owner
     * @param string     $type
     * @param string     $start
     * @param string     $end
     *
     * @return array
     */
    public static function getWorkActivityData($activity, $owner = null, $type = 'open', $start = null, $end = null)
    {
        $model = morph_to_model($activity);
        $activities = $model::getAuthViewData()->whereInOwner($owner)->withinPeriod($start, $end);

        if ($activities->count()) {
            if (in_array($activity, ['task', 'issue'])) {
                $query = 'only' . ucfirst($type);
                $activities = $activities->$query();

                return $activities->orderBy('due_date')->get()->flatten()->all();
            } elseif ($activity == 'milestone') {
                return $activities->orderBy('end_date')->get()
                                  ->filter(function ($milestone) use ($type) {
                                    if ($type == 'open') {
                                        return ($milestone->completion_percentage < 100);
                                    } elseif ($type == 'closed') {
                                        return ($milestone->completion_percentage == 100);
                                    } else {
                                        return false;
                                    }
                                  })->flatten()->all();
            }
        }

        return [];
    }

    /**
     * Get the user viewable activities according to filter param.
     *
     * @param string     $type
     * @param string     $start
     * @param string     $end
     * @param array|null $owner
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getActivitiesData($type = 'open', $start = null, $end = null, $owner = null)
    {
        $activities = array_merge(
            self::getWorkActivityData('task', $owner, $type, $start, $end),
            self::getWorkActivityData('issue', $owner, $type, $start, $end),
            self::getWorkActivityData('milestone', $owner, $type, $start, $end)
        );

        return count($activities) ? collect($activities)->sortBy('end') : collect();
    }

    /**
     * Get overdue Task|Issue|Milestone according to filter param.
     *
     * @param string     $activity
     * @param array|null $owner
     * @param string     $start
     * @param string     $end
     * @param bool       $today
     * @param bool       $sort_overdue
     *
     * @return array
     */
    public static function getOverdueWorkActivityData(
        $activity,
        $owner = null,
        $start = null,
        $end = null,
        $today = false,
        $sort_overdue = false
    ) {
        $model = morph_to_model($activity);
        $activities = $model::getAuthViewData()->whereInOwner($owner)->withinPeriod($start, $end)->overdue($today);

        if ($activities->count()) {
            if (in_array($activity, ['task', 'issue'])) {
                $activities = $activities->onlyOpen()->get();
            } elseif ($activity == 'milestone') {
                $activities = $activities->get()->filter(function ($milestone) {
                    return ($milestone->completion_percentage < 100);
                });
            }

            if ($sort_overdue) {
                $activities = $activities->sortByDesc('overdue_days');
            }

            return $activities->flatten()->all();
        }

        return [];
    }

    /**
     * Get the overdue activities according to filter param.
     *
     * @param string     $start
     * @param string     $end
     * @param array|null $owner
     * @param bool       $today
     * @param bool       $empty
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getOverdueActivitiesData(
        $start = null,
        $end = null,
        $owner = null,
        $today = false,
        $empty = false
    ) {
        if ($empty == true) {
            return collect();
        }

        $activities = array_merge(
            self::getOverdueWorkActivityData('task', $owner, $start, $end, $today),
            self::getOverdueWorkActivityData('issue', $owner, $start, $end, $today),
            self::getOverdueWorkActivityData('milestone', $owner, $start, $end, $today)
        );

        return count($activities)
               ? collect($activities)->sortBy('created_at')->sortByDesc('overdue_days')
               : collect();
    }
}
