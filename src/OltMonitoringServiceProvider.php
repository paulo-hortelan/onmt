<?php

namespace PauloHortelan\OltMonitoring;

use PauloHortelan\OltMonitoring\Commands\OltMonitoringCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OltMonitoringServiceProvider extends PackageServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
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
            ->hasMigration('2023_01_15_100000_create_olt_table')
            ->hasCommand(OltMonitoringCommand::class);
    }
}
