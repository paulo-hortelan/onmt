<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN551604;

class LanServiceConfig
{
    public int $cVlan;

    public int $cCos;

    public function __construct(
        int $cVlan,
        int $cCos,
    ) {
        $this->cVlan = $cVlan;
        $this->cCos = $cCos;
    }

    public function buildCommand(): string
    {
        $command = '';

        if (! empty($this->cVlan)) {
            $command .= 'CVLAN='.$this->cVlan.',';
        }
        if (! empty($this->cCos)) {
            $command .= 'CCOS='.$this->cCos.',';
        }

        $command = rtrim($command, ',');

        return $command;
    }
}
