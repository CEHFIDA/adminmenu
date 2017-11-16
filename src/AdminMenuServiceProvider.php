<?php

namespace Selfreliance\adminmenu;

use Illuminate\Support\ServiceProvider;

class AdminMenuServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes.php';
        include __DIR__.'/library/Menu.php';
        $this->app->make('Selfreliance\Adminmenu\AdminMenuController');
        $this->loadViewsFrom(__DIR__.'/views', 'adminmenu');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'translate-menu');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
