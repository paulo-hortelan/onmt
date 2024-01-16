<?php

namespace PauloHortelan\OltMonitoring\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\OltMonitoring\Services\Zte300Service
 */
class Zte300 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\OltMonitoring\Services\Zte300Service::class;
    }
}
