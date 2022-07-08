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

trait DropdownCategoryTrait
{
    /**
     * Form validation.
     *
     * @param array                                    $data
     * @param \Illuminate\Database\Eloquent\Model|null $model_obj
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($data, $model_obj = null)
    {
        $table        = with(new static)->table;
        $position_ids = self::commaSeparatedIds([0, -1]);
        $unique_name  = "unique:{$table},name";
        $required     = 'required';

        // If Edit the specified resource then will have data "id".
        if (isset($data['id'])) {
            $unique_name .= ',' . $data['id'];
            $required     = isset($model_obj) && ! $model_obj->fixed ? $required : '';
        }

        $rules = [
            'name'        => 'required|max:200|' . $unique_name,
            'position'    => 'required|integer|in:' . $position_ids,
            'description' => 'max:65535',
            'category'    => $required . '|in:open,closed',
        ];

        // Completion percentage rule for Task Status.
        if ($table == 'task_status') {
            $rules['completion_percentage'] = 'numeric|min:0|max:100|in:0,10,20,30,40,50,60,70,80,90,100';
        }

        return validator($data, $rules);
    }

    /**
     * Get a resource data table format.
     *
     * @return array
     */
    public static function getTableFormat()
    {
        $table  = with(new static)->table;
        $thead  = ['NAME', 'CATEGORY', 'DESCRIPTION'];
        $column = ['sequence' => ['className' => 'reorder'], 'name', 'category', 'description', 'action'];

        if ($table == 'task_status') {
            array_splice($thead, 2, 0, [['COMPLETION PERCENTAGE', 'data_class' => 'center']]);
            array_splice($column, 3, 0, ['completion_percentage']);
        }

        return [
            'thead'        => $thead,
            'action'       => self::allowAction(),
            'drag_drop'    => permit(self::getPermission() . '.edit'),
            'json_columns' => \DataTable::jsonColumn($column, self::hideColumns()),
        ];
    }

    /**
     * Get resource table data.
     *
     * @param array                    $data
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getTableData($data, $request)
    {
        $table = with(new static)->table;
        $data  = \DataTable::of($data)->addColumn('sequence', function ($model_obj) {
            return $model_obj->drag_and_drop;
        })->editColumn('name', function ($model_obj) {
            return $model_obj->name_html;
        })->editColumn('category', function ($model_obj) {
            return $model_obj->category_html;
        })->addColumn('action', function ($model_obj) use ($table) {
            return $model_obj->getActionHtml('Status', $model_obj->del_route['name'], null, [
                'edit'   => permit("custom_dropdowns.{$table}.edit"),
                'delete' => permit("custom_dropdowns.{$table}.delete") && ! $model_obj->fixed,
            ]);
        });

        if ($table == 'task_status') {
            $data = $data->editColumn('completion_percentage', function ($model_obj) {
                return $model_obj->completion_percentage . '%';
            });
        }

        return $data->make(true);
    }

    /**
     * Get default closed status.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getDefaultClosed()
    {
        return self::onlyClosed()->whereFixed(1)->get()->first();
    }

    /**
     * Get default open status.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getDefaultOpen()
    {
        return self::onlyOpen()->orderBy('position')->get()->first();
    }

    /**
     * Get a specific category records ids.
     *
     * @param string $category
     *
     * @return array
     */
    public static function getCategoryIds($category)
    {
        return self::whereCategory($category)->pluck('id')->toArray();
    }

    /**
     * Get smart order by category Open|Closed and position.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getSmartOrder()
    {
        return self::orderByRaw("FIELD(category, 'open', 'closed')")->orderBy('position')->get();
    }

    /**
     * The query only gets closed status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyClosed($query)
    {
        return $query->where('category', 'closed');
    }

    /**
     * The query only gets open status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyOpen($query)
    {
        return $query->where('category', 'open');
    }

    /**
     * Get the specified resource name HTML.
     *
     * @return string
     */
    public function getNameHtmlAttribute()
    {
        $outcome = $this->name;

        if ($this->category == 'closed' && $this->fixed == 1) {
            $closed_count = self::whereCategory('closed')->count();

            if ($closed_count > 1) {
                $outcome .= " <span class='para-hint-sm'>(default closed)</span>";
            }
        }

        return $outcome;
    }
}
