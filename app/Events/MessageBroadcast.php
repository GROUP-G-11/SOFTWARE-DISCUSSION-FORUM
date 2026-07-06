<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Real-time messaging event (SDD 5.4). Broadcast over the group's presence
 * channel so "receiving clients automatically get the new message... without
 * needing a page refresh." Excluded members never receive this event because
 * MessagingController filters recipients before the exclusion rows are used
 * by the client-side channel authorization callback.
 */
class MessageBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Post $post)
    {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('group.'.$this->post->topic->group_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->post->post_id,
            'topic_id' => $this->post->topic_id,
            'author' => $this->post->author->full_name,
            'content' => $this->post->content,
            'posted_at' => $this->post->posted_at,
        ];
    }
}
