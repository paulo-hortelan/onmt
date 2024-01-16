<?php

namespace PauloHortelan\OltMonitoring\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\OltMonitoring\OltMonitoring
 */
class OltMonitor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\OltMonitoring\Services\OltMonitorService::class;
    }
}
