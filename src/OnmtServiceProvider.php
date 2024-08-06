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

    public function boot()
    {
        $this->registerFacades();
    }

    public function register()
    {
    }

    protected function registerFacades()
    {
        $this->app->singleton("Nokia", function ($app) {
            return new NokiaService();
        });

        $this->app->singleton("Fiberhome", function ($app) {
            return new FiberhomeService();
        });

        $this->app->singleton("ZTE", function ($app) {
            return new ZTEService();
        });
    }
}
