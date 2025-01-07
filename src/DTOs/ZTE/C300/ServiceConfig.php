<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class ServiceConfig
{
    public string|int $serviceName;

    public int $gemportId;

    public ?int $veip;

    public ?int $vlan;

    public function __construct(
        string|int $serviceName,
        int $gemportId,
        ?int $veip = null,
        ?int $vlan = null,
    ) {
        $this->serviceName = $serviceName;
        $this->gemportId = $gemportId;
        $this->veip = $veip;
        $this->vlan = $vlan;
    }

    public function buildCommand(): string
    {
        $command = "service {$this->serviceName} gemport {$this->gemportId}";

        if (isset($this->veip)) {
            $command .= " veip {$this->veip}";
        }

        if (isset($this->vlan)) {
            $command .= " vlan {$this->vlan}";
        }

        return $command;
    }
}
