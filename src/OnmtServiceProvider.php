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
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('onmt')
            ->hasConfigFile();

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
}
