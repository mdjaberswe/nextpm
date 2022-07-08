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

trait PosionableTrait
{
    /**
     * Datatable hide columns.
     *
     * @return array
     */
    public static function hideColumns()
    {
        // If the auth user does not have permission to edit,
        // then hide the sequence/draggable column to edit row position.
        return permit(self::getPermission() . '.edit')
               ? parent::hideColumns() : push_flatten(parent::hideColumns(), 'sequence');
    }

    /**
     * Reset all resources position.
     *
     * @return bool
     */
    public static function resetPosition()
    {
        $min_position = self::min('position');

        if (isset($min_position)) {
            $min_position_id = self::wherePosition($min_position)->first()->id;
            $top             = self::find($min_position_id);
            $rest            = self::where('id', '!=', $min_position_id)->orderBy('position')->get();
            $trashed_records = self::onlyTrashed()->orderBy('position')->get();
            $start_with      = 1;
            $top->position   = $start_with;
            $top->save();

            // Reset all resource items position according to the top position.
            if ($rest->count()) {
                foreach ($rest as $single_rest) {
                    $start_with++;
                    $single_rest->position = $start_with;
                    $single_rest->save();
                }
            }

            // Reset all trashed/deleted resource items position.
            if ($trashed_records->count()) {
                foreach ($trashed_records as $trashed_record) {
                    $start_with++;
                    $trashed_record->position = $start_with;
                    $trashed_record->save();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Get the next value of a picked position value.
     *
     * @param int $picked_position_id
     * @param int $target_position_id
     *
     * @return numeric
     */
    public static function afterPickedPositionVal($picked_position_id, $target_position_id = null)
    {
        $picked_position_val  = self::find($picked_position_id)->position;
        $picked_next_position = self::where('position', '>', $picked_position_val)->min('position');
        $addition_val         = isset($picked_next_position) ? ($picked_next_position - $picked_position_val)/2 : 1;
        $position_val         = $picked_position_val + $addition_val;

        // When form position field value remains unchanged.
        if (isset($target_position_id) && isset($picked_next_position)) {
            $picked_next_position_id = self::wherePosition($picked_next_position)->first()->id;

            if ($picked_next_position_id == $target_position_id) {
                $position_val = $picked_next_position;
            }
        }

        return $position_val;
    }

    /**
     * Downgrade top specified resource position.
     *
     * @param int|null $target_position_id
     *
     * @return array
     */
    public static function downgradeTop($target_position_id = null)
    {
        $outcome      = ['status' => false, 'empty_top_position' => 1];
        $top_position = self::min('position');

        if (isset($top_position)) {
            $top_position_id = self::wherePosition($top_position)->first()->id;

            if ($top_position_id !== $target_position_id) {
                $next_to_top_position = self::where('position', '>', $top_position)->min('position');
                $downgrade            = isset($next_to_top_position) ? ($next_to_top_position - $top_position)/2 : 1;
                $downgrade_position   = $top_position + $downgrade;
                $top                  = self::find($top_position_id);
                $top->position        = $downgrade_position;
                $top->save();
            }

            $outcome = ['status' => true, 'empty_top_position' => $top_position];
        }

        return $outcome;
    }

    /**
     * Get top position which is currently empty.
     *
     * @param int|null $target_position_id
     *
     * @return numeric
     */
    public static function getEmptyTopPosition($target_position_id = null)
    {
        return self::downgradeTop($target_position_id)['empty_top_position'];
    }

    /**
     * Get resources bottom position.
     *
     * @param int|null $target_position_id
     *
     * @return numeric
     */
    public static function getBottomPosition($target_position_id = null)
    {
        $empty_bottom_position = 1;
        $last_position = self::max('position');

        if (isset($last_position)) {
            $empty_bottom_position = $last_position + 1;

            if (isset($target_position_id)) {
                $last_position_id = self::wherePosition($last_position)->first()->id;

                if ($last_position_id == $target_position_id) {
                    $empty_bottom_position = $last_position;
                }
            }
        }

        return $empty_bottom_position;
    }

    /**
     * Get the position value where the picked specified resource will replace.
     *
     * @param int      $picked_position_id
     * @param int|null $target_position_id
     *
     * @return numeric
     */
    public static function getTargetPositionVal($picked_position_id, $target_position_id = null)
    {
        switch ($picked_position_id) {
            case 0: // Place at top.
                $position_val = self::getEmptyTopPosition($target_position_id);
                break;
            case -1: // Place at bottom.
                $position_val = self::getBottomPosition($target_position_id);
                break;
            default: // Place after picked item position.
                $position_val = self::afterPickedPositionVal($picked_position_id, $target_position_id);
        }

        return $position_val;
    }

    /**
     * Get picked specified resource closest desc kanban position.
     *
     * @param int $picked_position_id
     *
     * @return numeric
     */
    public static function getKanbanDescPosition($picked_position_id)
    {
        if ($picked_position_id == 0) {
            return self::getBottomPosition(null);
        }

        $picked = self::find($picked_position_id);
        $picked_position_val  = $picked->position;
        $picked_prev_position = self::where('position', '<', $picked_position_val)->max('position');

        if (isset($picked_prev_position)) {
            $substract_val = ($picked_position_val - $picked_prev_position) / 2;

            return ($picked_position_val - $substract_val);
        }

        if ($picked_position_val >= 2) {
            return floor($picked_position_val - 1);
        }

        $picked_next_position = self::where('position', '>', $picked_position_val)->min('position');
        $addition_val         = isset($picked_next_position) ? ($picked_next_position - $picked_position_val)/2 : 1;
        $update_picked_val    = $picked_position_val + $addition_val;
        $picked->position     = $update_picked_val;
        $picked->update();

        return floor($picked_position_val);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */
    /**
     * Get "AFTER: " added prefix name.
     *
     * @return string
     */
    public function getPositionAfterNameAttribute()
    {
        return 'AFTER : ' . $this->name;
    }

    /**
     * Get prev positioned specified resource id.
     *
     * @return int
     */
    public function getPrevPositionIdAttribute()
    {
        $prev = self::where('position', '<', $this->position)->latest('position')->first();

        return isset($prev) ? $prev->id : 0;
    }

    /**
     * Get specified resource category HTML.
     *
     * @return string
     */
    public function getCategoryHtmlAttribute()
    {
        return "<span class='capitalize'>" . snake_to_space($this->category) . '</span>';
    }

    /**
     * Get data table drog and drop HTML for changing positions.
     *
     * @return string
     */
    public function getDragAndDropAttribute()
    {
        return "<i class='mdi mdi-drag-vertical'></i><input type='hidden' name='positions[]' value='{$this->id}'>";
    }
}
