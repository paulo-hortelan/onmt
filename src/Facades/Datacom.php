<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PauloHortelan\Onmt\Services\Datacom\DatacomService
 */
class Datacom extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\Datacom\DatacomService::class;
    }
}
