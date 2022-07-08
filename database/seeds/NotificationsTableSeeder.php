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

use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DatabaseNotification::truncate();

        $save_date     = date('Y-m-d H:i:s');
        $notifications = [
            ['id' => '6789a7a5-472a-463f-9ea5-7d7371c0db85', 'type' => 'App\Notifications\CrudNotification', 'notifiable_id' => 1, 'notifiable_type' => 'user', 'data' => json_encode(['case' => 'task_created', 'module' => 'task', 'module_id' => 1, 'info' => null]), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['id' => 'b9933724-5ee9-4440-b78e-0a7f320e1e8d', 'type' => 'App\Notifications\CrudNotification', 'notifiable_id' => 1, 'notifiable_type' => 'user', 'data' => json_encode(['case' => 'issue_created', 'module' => 'issue', 'module_id' => 1, 'info' => null]), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['id' => 'f4789b30-02f7-4a26-b7d5-bb049ceabd71', 'type' => 'App\Notifications\CrudNotification', 'notifiable_id' => 1, 'notifiable_type' => 'user', 'data' => json_encode(['case' => 'project_created', 'module' => 'project', 'module_id' => 1, 'info' => null]), 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        DatabaseNotification::insert($notifications);
    }
}
