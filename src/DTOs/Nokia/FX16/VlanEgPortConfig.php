<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class VlanEgPortConfig
{
    public int $svLan;

    public int $cvLan;

    public string $portTransMode;

    public function __construct(int $svLan, int $cvLan, string $portTransMode)
    {
        $this->svLan = $svLan;
        $this->cvLan = $cvLan;
        $this->portTransMode = $portTransMode;
    }

    public function buildCommand(): string
    {
        $command = '';

        $command .= "{$this->svLan},{$this->cvLan}";
        $command .= ":PORTTRANSMODE={$this->portTransMode},";

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
