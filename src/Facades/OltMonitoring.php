<?php

namespace PauloHortelan\OltMonitoring\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\OltMonitoring\OltMonitoring
 */
class OltMonitoring extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\OltMonitoring\OltMonitoring::class;
    }
}
