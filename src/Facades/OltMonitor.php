<?php

namespace PauloHortelan\OltMonitoring\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\OltMonitoring\Services\OltMonitorService;

/**
 * @see \PauloHortelan\OltMonitoring\OltMonitoring
 */
class OltMonitor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OltMonitorService::class;
    }
}
