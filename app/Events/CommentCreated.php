<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Канал, на який транслюється подія (наприклад, загальний канал для коментарів).
     */
    public function broadcastOn()
    {
        return new Channel('comments');
    }

    /**
     * Назва події на фронті (можна не визначати, тоді буде 'CommentCreated').
     */
    public function broadcastAs()
    {
        return 'comment.created';
    }
}
