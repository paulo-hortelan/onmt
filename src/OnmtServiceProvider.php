<?php

namespace PauloHortelan\Onmt;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OnmtServiceProvider extends PackageServiceProvider
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
            __DIR__.'/../routes/onmt.php' => base_path('routes/onmt.php'),
        ], 'onmt-routes');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations/onmt'),
        ], 'onmt-migrations');      

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations/onmt'),
        ], 'onmt-models');        

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'PauloHortelan\\Onmt\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/onmt.php');
        });
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('onmt.prefix'),
            'middleware' => config('onmt.middleware'),
        ];
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('onmt')
            ->hasConfigFile('onmt')
            ->hasMigration('create_olts_table')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }
}
