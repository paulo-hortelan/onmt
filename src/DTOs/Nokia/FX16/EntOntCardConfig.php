<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class EntOntCardConfig
{
    public int $ontCardHolderSlot;

    public string $planCardType;

    public int $plndNumDataPorts;

    public int $plndNumVoicePorts;

    public function __construct(
        int $ontCardHolderSlot,
        string $planCardType,
        int $plndNumDataPorts,
        int $plndNumVoicePorts
    ) {
        $this->ontCardHolderSlot = $ontCardHolderSlot;
        $this->planCardType = $planCardType;
        $this->plndNumDataPorts = $plndNumDataPorts;
        $this->plndNumVoicePorts = $plndNumVoicePorts;
    }

    public function buildCommand(): string
    {
        $command = '';

        $command .= $this->ontCardHolderSlot.':::';
        $command .= $this->planCardType.','.$this->plndNumDataPorts.','.$this->plndNumVoicePorts;

        $command = rtrim($command, ',');

        return $command;
    }
}
