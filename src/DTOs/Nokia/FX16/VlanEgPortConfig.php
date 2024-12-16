<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class VlanEgPortConfig
{
    public int $ontSlot;

    public int $ontPort;

    public int $svLan;

    public int $cvLan;

    public string $portTransMode;

    public function __construct(int $ontSlot, int $ontPort, int $svLan, int $cvLan, string $portTransMode)
    {
        $this->ontSlot = $ontSlot;
        $this->ontPort = $ontPort;
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
     * @return string Identifier command
     */
    public function buildIdentifier(string $interface): string
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $command = '';

        $command .= "ONTL2UNI-$formattedInterface-{$this->ontSlot}-{$this->ontPort}";

        $command = rtrim($command, ',');

        return $command;
    }
}
