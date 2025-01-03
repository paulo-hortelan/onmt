<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class ServicePortConfig
{
    public int $servicePortId;

    public ?int $vport;

    public ?int $userVlan;

    public ?int $vlan;

    public function __construct(
        int $servicePortId,
        ?int $vport = null,
        ?int $userVlan = null,
        ?int $vlan = null,
    ) {
        $this->servicePortId = $servicePortId;
        $this->vport = $vport;
        $this->userVlan = $userVlan;
        $this->vlan = $vlan;
    }

    public function buildCommand(): string
    {
        $command = "service-port {$this->servicePortId}";

        if (isset($this->vport)) {
            $command .= " vport {$this->vport}";
        }

        if (isset($this->userVlan)) {
            $command .= " user-vlan {$this->userVlan}";
        }

        if (isset($this->vlan)) {
            $command .= " vlan {$this->vlan}";
        }

        return $command;
    }
}
