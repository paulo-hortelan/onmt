<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class QosUsQueueConfig
{
    public string $usbwProfName;

    public function __construct(string $usbwProfName)
    {
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
     * @param  int  $ontSlot  ONT equipment holder (1.. 16)
     * @param  int  $ontPort  service interface (1.. 16)
     * @param  int  $queue  Queue identifier (0.. 7)
     * @return string Identifier command
     */
    public function buildIdentifier(string $interface, int $ontSlot, int $ontPort, int $queue): string
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $command = '';

        $command .= "ONTL2UNIQ-$formattedInterface-$ontSlot-$ontPort-$queue";

        $command = rtrim($command, ',');

        return $command;
    }
}
