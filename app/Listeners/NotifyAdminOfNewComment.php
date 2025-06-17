<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfNewComment
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        Log::info('[🔔] Новий коментар створено: ' . $event->comment->user_name);

        // Або надіслати повідомлення адміну
        // Notification::route('mail', 'admin@example.com')->notify(new NewCommentNotification($event->comment));
    }
}
