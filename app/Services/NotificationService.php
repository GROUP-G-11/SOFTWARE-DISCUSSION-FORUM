<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/**
 * Central dispatcher for the Notification Module (SDD 5.10).
 *
 * Persists every notification for durability, then pushes it live to
 * connected clients over a broadcasting channel (WebSocket broker in the
 * SDD). Offline users simply see the persisted, unread row next time they
 * sync or poll GET /api/notifications.
 */
class NotificationService
{
    public function send(User $user, string $type, string $message, ?string $relatedType = null, ?int $relatedId = null): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->user_id,
            'type' => $type,
            'message' => $message,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'is_read' => false,
        ]);

        // Push to the user's private channel for online clients. Offline
        // clients pick this up later via the Sync module (SDD 5.4) or by
        // polling the notifications index endpoint.
        broadcast(new \App\Events\NotificationBroadcast($notification))->toOthers();

        return $notification;
    }

    public function sendToMany(iterable $users, string $type, string $message, ?string $relatedType = null, ?int $relatedId = null): void
    {
        foreach ($users as $user) {
            $this->send($user, $type, $message, $relatedType, $relatedId);
        }
    }
}
