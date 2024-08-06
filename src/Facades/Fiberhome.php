<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;

/**
 * @see \PauloHortelan\Onmt\Services\FiberhomeService
 */
class Fiberhome extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FiberhomeService';
    }
}
