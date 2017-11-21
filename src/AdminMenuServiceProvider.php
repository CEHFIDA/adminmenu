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
        $this->app->make('Selfreliance\Adminmenu\AdminMenuController');
        $this->loadViewsFrom(__DIR__.'/views', 'adminmenu');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('menu', function(){
            return new \Selfreliance\Adminmenu\Menu();
        });
    }
}
