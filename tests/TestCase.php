<?php

namespace PauloHortelan\Onmt\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\TestCase as Orchestra;
use PauloHortelan\Onmt\OnmtServiceProvider;

#[WithEnv('DB_CONNECTION', 'testing')]
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            // Code after application created.
        });

        $this->beforeApplicationDestroyed(function () {
            // Code before application destroyed.
        });

        parent::setUp();

        // Factory::guessFactoryNamesUsing(
        //     fn (string $modelName) => 'PauloHortelan\\Onmt\\Database\\Factories\\'.class_basename($modelName).'Factory'
        // );
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('remember_token');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            OnmtServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('connections.fake', true);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../workbench/database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}
