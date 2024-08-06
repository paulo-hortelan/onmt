<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\Onmt\Services\NokiaService
 */
class Nokia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'NokiaService';
    }
}
