<?php

namespace PauloHortelan\Onmt;

use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OnmtServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('onmt')
            ->hasConfigFile()
            ->hasMigration('create_command_result_batches_table')
            ->hasMigration('create_command_results_table');

        $this->app->bind(FiberhomeService::class, function () {
            return new FiberhomeService();
        });

        $this->app->bind(NokiaService::class, function () {
            return new NokiaService();
        });

        $this->app->bind(ZTEService::class, function () {
            return new ZTEService();
        });
    }

    public function boot(): void
    {
        parent::boot();
    }
}
