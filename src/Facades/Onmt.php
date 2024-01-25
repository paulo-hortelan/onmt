<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\Services\OltMonitorService;

/**
 * @see \PauloHortelan\Onmt\Onmt
 */
class OltMonitor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OltMonitorService::class;
    }
}
