<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\Onmt\Services\Nokia\NokiaService
 */
class Nokia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\Nokia\NokiaService::class;
    }
}
