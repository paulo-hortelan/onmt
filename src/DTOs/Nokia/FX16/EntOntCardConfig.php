<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class EntOntCardConfig
{
    public string $planCardType;

    public int $plndNumDataPorts;

    public int $plndNumVoicePorts;

    public int $ontCardHolderSlot;

    /**
     * @param  string  $planCardType  Plan card type
     * @param  int  $plndNumDataPorts  .
     * @param  int  $plndNumVoicePorts  .
     * @param  int  $ontCardHolderSlot  ONT card holder (1.. 14)
     */
    public function __construct(
        string $planCardType,
        int $plndNumDataPorts,
        int $plndNumVoicePorts,
        int $ontCardHolderSlot
    ) {
        $this->planCardType = $planCardType;
        $this->plndNumDataPorts = $plndNumDataPorts;
        $this->plndNumVoicePorts = $plndNumVoicePorts;
        $this->ontCardHolderSlot = $ontCardHolderSlot;
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
     * @return string Identifier command
     */
    public function buildIdentifier(string $interface): string
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $command = '';

        $command .= "ONTCARD-$formattedInterface-{$this->ontCardHolderSlot}";

        $command = rtrim($command, ',');

        return $command;
    }
}
