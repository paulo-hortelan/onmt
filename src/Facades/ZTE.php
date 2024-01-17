<?php

namespace PauloHortelan\OltMonitoring\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\OltMonitoring\Services\ZTE\ZTEService;

/**
 * @see \PauloHortelan\OltMonitoring\Services\ZTEService
 */
class ZTE extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ZTEService::class;
    }
}
