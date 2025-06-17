<?php

namespace App\Providers;

use App\Events\CommentCreated;
use App\Listeners\NotifyAdminOfNewComment;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CommentCreated::class => [
            NotifyAdminOfNewComment::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
