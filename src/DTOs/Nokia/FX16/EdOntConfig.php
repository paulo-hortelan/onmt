<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class EdOntConfig
{
    public function __construct(
    ) {}

    public function buildCommand(): string
    {
        $command = '';

        $command = rtrim($command, ',');

        return $command;
    }
}
