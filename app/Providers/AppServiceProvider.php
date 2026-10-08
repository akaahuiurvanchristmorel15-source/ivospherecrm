<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.topbar', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $service = app(NotificationService::class);
                $view->with([
                    'topbarNotifications' => $service->getNotificationsForUser($user, 6),
                    'topbarUnreadCount' => $service->getUnreadCount($user),
                ]);
            } else {
                $view->with([
                    'topbarNotifications' => collect(),
                    'topbarUnreadCount' => 0,
                ]);
            }
        });
    }
}
