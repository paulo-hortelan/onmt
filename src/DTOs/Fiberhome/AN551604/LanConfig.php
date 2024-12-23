<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN551604;

class LanConfig
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
        $parameters = [
            'CVLAN' => $this->cVlan ?? null,
            'CCOS' => $this->cCos ?? null,
        ];

        $command = '';

        foreach ($parameters as $key => $value) {
            if (isset($value)) {
                $command .= "$key=$value,";
            }
        }

        $command = rtrim($command, ',');

        return $command;
    }
}
