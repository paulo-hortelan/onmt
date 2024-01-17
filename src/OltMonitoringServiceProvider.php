<?php

namespace PauloHortelan\OltMonitoring;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

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

        $this->registerRoutes();

        $this->publishes([
            __DIR__.'/../routes/olt-monitoring.php' => base_path('routes/olt-monitoring.php')
        ], 'olt-monitoring-routes');

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
            ->hasMigration('create_olts_table')
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }
}
