<?php

declare(strict_types=1);

namespace Workbench\App\Notifications;

use Illuminate\Notifications\Notification;

class HorseEscaped extends Notification
{
    public function __construct(protected string $horse) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, string> */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "{$this->horse} has escaped the corral!",
            'url' => '/admin/resources/horses',
        ];
    }
}
