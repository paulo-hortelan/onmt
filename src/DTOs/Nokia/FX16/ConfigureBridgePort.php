<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureBridgePort
{
    public string $bridgePort;

    public ?int $maxUnicastMac;

    public ?int $maxCommittedMac;

    public ?int $vlanId;

    public ?string $tag;

    public ?int $pvid;

    public function __construct(
        string $bridgePort,
        ?int $maxUnicastMac = null,
        ?int $maxCommittedMac = null,
        ?int $vlanId = null,
        ?string $tag = null,
        ?int $pvid = null,
    ) {
        $this->bridgePort = $bridgePort;
        $this->maxUnicastMac = $maxUnicastMac;
        $this->maxCommittedMac = $maxCommittedMac;
        $this->vlanId = $vlanId;
        $this->tag = $tag;
        $this->pvid = $pvid;
    }

    public function buildCommand(): string
    {
        $command = '';

        $parameters = [
            'max-unicast-mac' => $this->maxUnicastMac ?? null,
            'max-committed-mac' => $this->maxCommittedMac ?? null,
            'vlan-id' => $this->vlanId ?? null,
            'tag' => $this->tag ?? null,
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
