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

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CrudNotification extends Notification
{
    use Queueable;

    private $case;
    private $info;
    private $module;
    private $module_id;

    /**
     * Create a new notification instance.
     *
     * @param string $case
     * @param string $module_id
     * @param string $info
     *
     * @return void
     */
    public function __construct($case, $module_id, $info = null)
    {
        $this->case      = $case;
        $this->info      = $info;
        $this->module    = explode('_', $case)[0];
        $this->module_id = $module_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'case'      => $this->case,
            'info'      => $this->info,
            'module'    => $this->module,
            'module_id' => $this->module_id,
        ];
    }
}
