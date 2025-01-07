<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\Onmt\Services\ZTE\ZTEService
 *
 * @method static mixed connectTelnet()
 */
class ZTE extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\ZTE\ZTEService::class;
    }
}
