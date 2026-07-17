<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Pushes a persisted Notification to the recipient's private channel
 * (SDD 5.10 step 4: "the WebSocket Event Broker immediately pushes the
 * notification payload"). Offline recipients pick it up later via the
 * Sync module or GET /api/notifications.
 */
class NotificationBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->notification->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notification->notification_id,
            'type' => $this->notification->type,
            'message' => $this->notification->message,
            'created_at' => $this->notification->created_at,
        ];
    }
}
