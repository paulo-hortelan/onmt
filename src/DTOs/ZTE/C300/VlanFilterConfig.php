<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class VlanFilterConfig
{
    public int $iphost;

    public ?int $priority;

    public ?int $vlan;

    public function __construct(
        int $iphost,
        ?int $priority,
        ?int $vlan,
    ) {
        $this->iphost = $iphost;
        $this->priority = $priority;
        $this->vlan = $vlan;
    }

    public function buildCommand(): string
    {
        $command = "vlan-filter iphost {$this->iphost}";

        if (isset($this->priority)) {
            $command .= " pri {$this->priority}";
        }

        if (isset($this->vlan)) {
            $command .= " vlan {$this->vlan}";
        }

        return $command;
    }
}
