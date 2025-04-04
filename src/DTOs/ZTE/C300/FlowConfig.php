<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class FlowConfig
{
    public int $flowId;

    public ?int $priority;

    public ?int $vlan;

    public function __construct(
        int $flowId,
        ?int $priority,
        ?int $vlan,
    ) {
        $this->flowId = $flowId;
        $this->priority = $priority;
        $this->vlan = $vlan;
    }

    public function buildCommand(): string
    {
        $command = "flow {$this->flowId}";

        if (isset($this->priority)) {
            $command .= " pri {$this->priority}";
        }

        if (isset($this->vlan)) {
            $command .= " vlan {$this->vlan}";
        }

        return $command;
    }
}
