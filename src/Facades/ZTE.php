<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

/**
 * @see \PauloHortelan\Onmt\Services\ZTEService
 */
class ZTE extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ZTEService';
    }
}
