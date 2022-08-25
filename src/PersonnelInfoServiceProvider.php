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
        $this->publishes([
            __DIR__.'/publishable/config/HRConnections.php' => config_path('database.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/publishable/Repositories/Common/EmployeeRepository.php' => app_path('Repositories/Common/EmployeeRepository.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/publishable/config/HRConnections.php',
            'database.connections'
        );
    }
}
