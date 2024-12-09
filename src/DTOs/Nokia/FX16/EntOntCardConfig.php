<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class EntOntCardConfig
{
    public string $planCardType;

    public int $plndNumDataPorts;

    public int $plndNumVoicePorts;

    public function __construct(
        string $planCardType,
        int $plndNumDataPorts,
        int $plndNumVoicePorts
    ) {
        $this->planCardType = $planCardType;
        $this->plndNumDataPorts = $plndNumDataPorts;
        $this->plndNumVoicePorts = $plndNumVoicePorts;
    }

    public function buildCommand(): string
    {
        $command = '';

        $command .= $this->planCardType.','.$this->plndNumDataPorts.','.$this->plndNumVoicePorts;

        $command = rtrim($command, ',');

        return $command;
    }

    /**
     * Build the ONT identifier
     *
     * @param  string  $interface  rack/shelf/lt_slot/pon_port/ont
     * @param  int  $ontCardHolderSlot  ONT card holder (1.. 14)
     * @return string Identifier command
     */
    public function buildIdentifier(string $interface, int $ontCardHolderSlot): string
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $command = '';

        $command .= "ONTCARD-$formattedInterface-$ontCardHolderSlot";

        $command = rtrim($command, ',');

        return $command;
    }
}
