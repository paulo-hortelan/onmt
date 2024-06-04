<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

/**
 * @see \PauloHortelan\Onmt\Services\NokiaService
 */
class Nokia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NokiaService::class;
    }
}
