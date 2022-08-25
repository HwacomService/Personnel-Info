<?php

namespace Hwacom\PersonnelInfo;

use Illuminate\Support\ServiceProvider;

class PersonnelInfoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/HRConnections.php',
            'database.connections'
        );
    }

    private function registerPublishables()
    {
    }
}
