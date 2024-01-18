<?php

namespace PauloHortelan\OltMonitoring\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\OltMonitoring\Services\Nokia\NokiaService;

/**
 * @see \PauloHortelan\OltMonitoring\Services\ZTEService
 */
class Nokia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NokiaService::class;
    }
}
