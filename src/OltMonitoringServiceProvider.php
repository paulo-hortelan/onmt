<?php

namespace PauloHortelan\OltMonitoring;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Route;
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
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerRoutes();

        $this->publishes([
            __DIR__.'/../config/olt-monitoring.php' => config_path('olt-monitoring.php')
        ], 'olt-monitoring-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'olt-monitoring-migrations');

        // copy(__DIR__.'/../routes/olt-monitoring.php', base_path('routes/olt-monitoring.php'));

        Factory::guessFactoryNamesUsing(function (string $modelName) { // @phpstan-ignore-line
            return 'PauloHortelan\\OltMonitoring\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/olt-monitoring.php');
        });
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('olt-monitoring.prefix'),
            'middleware' => config('olt-monitoring.middleware'),
        ];
    }

    public function register()
    {
        parent::register();

        $loader = AliasLoader::getInstance();
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
            ->hasConfigFile('olt-monitoring')
            ->hasMigration('2023_01_15_100000_create_olt_table');
    }
}
