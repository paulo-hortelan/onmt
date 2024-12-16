<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class QosUsQueueConfig
{
    public int $ontSlot;

    public int $ontPort;

    public int $queue;

    public string $usbwProfName;

    public function __construct(int $ontSlot, int $ontPort, int $queue, string $usbwProfName)
    {
        $this->ontSlot = $ontSlot;
        $this->ontPort = $ontPort;
        $this->queue = $queue;
        $this->usbwProfName = $usbwProfName;
    }

    public function buildCommand(): string
    {
        $command = '';

        $command .= "USBWPROFNAME={$this->usbwProfName}";

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

        $command .= "ONTL2UNIQ-$formattedInterface-{$this->ontSlot}-{$this->ontPort}-{$this->queue}";

        $command = rtrim($command, ',');

        return $command;
    }
}
