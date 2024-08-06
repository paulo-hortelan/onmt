<?php

namespace PauloHortelan\Onmt;

use Illuminate\Support\ServiceProvider;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

// use Spatie\LaravelPackageTools\Package;
// use Spatie\LaravelPackageTools\PackageServiceProvider;

class OnmtServiceProvider extends ServiceProvider
{
    // public function boot()
    // {
    //     $this->registerFacades();
    // }

    public function register()
    {
        $this->app->bind("NokiaService", function ($app) {
            return new NokiaService();
        });

        $this->app->bind("FiberhomeService", function ($app) {
            return new FiberhomeService();
        });

        $this->app->bind("ZTEService", function ($app) {
            return new ZTEService();
        });
    }

    // protected function registerFacades()
    // {

    // }
}
