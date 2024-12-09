<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class VlanPortConfig
{
    public int $maxNUcMacAdr;

    public int $cmitMaxNumMacAddr;

    public function __construct(int $maxNUcMacAdr, int $cmitMaxNumMacAddr)
    {
        $this->maxNUcMacAdr = $maxNUcMacAdr;
        $this->cmitMaxNumMacAddr = $cmitMaxNumMacAddr;
    }

    public function buildCommand(): string
    {
        $command = '';

        $command .= "MAXNUCMACADR={$this->maxNUcMacAdr},";
        $command .= "CMITMAXNUMMACADDR={$this->cmitMaxNumMacAddr},";

        $command = rtrim($command, ',');

        return $command;
    }

    /**
     * Build the ONT identifier
     *
     * @param  string  $interface  rack/shelf/lt_slot/pon_port/ont
     * @param  int  $ontSlot  ONT equipment holder (1.. 16)
     * @param  int  $ontPort  service interface (1.. 16)
     * @return string Identifier command
     */
    public function buildIdentifier(string $interface, int $ontSlot, int $ontPort): string
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $command = '';

        $command .= "ONTL2UNI-$formattedInterface-$ontSlot-$ontPort";

        $command = rtrim($command, ',');

        return $command;
    }
}
