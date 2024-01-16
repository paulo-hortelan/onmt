<?php

namespace PauloHortelan\OltMonitoring;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Filesystem\Filesystem;

class OltMonitoringServiceProvider extends PackageServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        (new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers'));
        (new Filesystem)->copyDirectory(__DIR__.'/../Http/Controllers', app_path('Http/Controllers'));     

        (new Filesystem)->ensureDirectoryExists(app_path('routes'));
        (new Filesystem)->copyDirectory(__DIR__.'/../routes', app_path('routes'));         
    }

    public function register()
    {
        parent::register();

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('OltMonitor', 'PauloHortelan\OltMonitoring\Facades');
        $loader->alias('Zte600', 'PauloHortelan\OltMonitoring\Facades');
        $loader->alias('Zte600', 'PauloHortelan\OltMonitoring\Facades');
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('olt-monitoring')
            ->hasConfigFile()
            ->hasRoute('api')
            ->hasMigration('2023_01_15_100000_create_olt_table');
    }
}