<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class ServiceConfig
{
    public string $serviceName;

    public int $gemportId;

    public ?int $vlan;

    public function __construct(
        string $serviceName,
        int $gemportId,
        ?int $vlan = null,
    ) {
        $this->serviceName = $serviceName;
        $this->gemportId = $gemportId;
        $this->vlan = $vlan;
    }

    public function buildCommand(): string
    {
        $command = "service {$this->serviceName} gemport {$this->gemportId}";

        if (isset($this->vlan)) {
            $command .= " vlan {$this->vlan}";
        }

        return $command;
    }
}
