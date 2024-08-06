<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;

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
