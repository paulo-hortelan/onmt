<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\Services\OnmtService;

/**
 * @see \PauloHortelan\Onmt\OnmtService
 */
class Onmt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OnmtService::class;
    }
}
