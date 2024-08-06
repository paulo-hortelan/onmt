<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService
 */
class Fiberhome extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService::class;
    }
}
