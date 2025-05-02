<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureBridgePort
{
    public string $bridgePort;

    public ?int $maxUnicastMac;

    public ?int $vlanId;

    public ?int $pvid;

    public function __construct(
        string $bridgePort,
        ?int $maxUnicastMac = null,
        ?int $vlanId = null,
        ?int $pvid = null,
    ) {
        $this->bridgePort = $bridgePort;
        $this->maxUnicastMac = $maxUnicastMac;
        $this->vlanId = $vlanId;
        $this->pvid = $pvid;
    }

    public function buildCommand(): string
    {
        $command = '';

        $parameters = [
            'max-unicast-mac' => $this->maxUnicastMac ?? null,
            'vlan-id' => $this->vlanId ?? null,
            'pvid' => $this->pvid ?? null,
        ];

        foreach ($parameters as $key => $value) {
            if (isset($value)) {
                $command .= "$key $value ";
            }
        }

        $command = rtrim($command, ' ');

        return $command;
    }
}
