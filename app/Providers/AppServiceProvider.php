<?php

namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
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
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // 👇 الكود الجديد: مشاركة عدد الرسائل غير المقروءة مع كل الصفحات
        // View::composer('*', function ($view) {
        //     $unreadCount = 0;
        //     if (Auth::check()) {
        //         $unreadCount = \App\Models\Message::where('receiver_id', Auth::id())
        //             ->where('is_read', false) // نعد فقط غير المقروءة
        //             ->count();
        //     }
        //     $view->with('unreadCount', $unreadCount);
        // });
    }
}
