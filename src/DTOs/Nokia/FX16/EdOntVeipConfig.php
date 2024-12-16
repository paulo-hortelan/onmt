<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class EdOntVeipConfig
{
    public int $ontSlot;

    public int $ontPort;

    /**
     * @param  string  $interface  rack/shelf/lt_slot/pon_port/ont
     * @param  int  $ontSlot  ONT equipment holder (1.. 16)
     * @param  int  $ontPort  service interface (1.. 16)
     */
    public function __construct(
        int $ontSlot,
        int $ontPort,
    ) {
        $this->ontSlot = $ontSlot;
        $this->ontPort = $ontPort;
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

        $command .= "ONTVEIP-$formattedInterface-{$this->ontSlot}-{$this->ontPort}";

        $command = rtrim($command, ',');

        return $command;
    }
}
