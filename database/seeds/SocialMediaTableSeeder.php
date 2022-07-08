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
use App\Models\SocialMedia;

class SocialMediaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SocialMedia::truncate();

        $save_date    = date('Y-m-d H:i:s');
        $social_media = [
            [
                'linked_id'   => 1,
                'linked_type' => 'staff',
                'media'       => 'facebook',
                'data'        => json_encode(['link' => 'james.gardner']),
                'created_at'  => $save_date,
                'updated_at'  => $save_date,
            ]
        ];

        SocialMedia::insert($social_media);
    }
}
