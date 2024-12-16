<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class VlanPortConfig
{
    public int $ontSlot;

    public int $ontPort;

    public ?int $maxNUcMacAdr;

    public ?int $cmitMaxNumMacAddr;

    public ?int $defaultCvLan;

    public function __construct(int $ontSlot, int $ontPort, ?int $maxNUcMacAdr = null, ?int $cmitMaxNumMacAddr = null, ?int $defaultCvLan = null)
    {
        $this->ontSlot = $ontSlot;
        $this->ontPort = $ontPort;
        $this->maxNUcMacAdr = $maxNUcMacAdr;
        $this->cmitMaxNumMacAddr = $cmitMaxNumMacAddr;
        $this->defaultCvLan = $defaultCvLan;
    }

    public function buildCommand(): string
    {
        $parameters = [
            'MAXNUCMACADR' => $this->maxNUcMacAdr,
            'CMITMAXNUMMACADDR' => $this->cmitMaxNumMacAddr,
            'DEFAULTCVLAN' => $this->defaultCvLan,
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
