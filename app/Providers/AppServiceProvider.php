<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $groups = cache()->remember('user_groups_' . auth()->id(), now()->addMinutes(10), function () {
                    return auth()->user()->groups;
                });

                $view->with('groups', $groups);
            }
        });
    }
}
