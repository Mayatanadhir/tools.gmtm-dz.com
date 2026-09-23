<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemActivityAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  array<string, mixed>  $extra  Optional additional data (e.g. user_id, email)
     */
    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $type,
        private readonly string $causer = 'System',
        private readonly array $extra = []
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get the unified structured payload for the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'causer' => $this->causer,
            'extra' => $this->extra,
        ];
    }
}
