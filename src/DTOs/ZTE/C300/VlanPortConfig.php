<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class VlanPortConfig
{
    public string $portName;

    public ?string $mode;

    public ?string $tag;

    public ?int $vlan;

    public function __construct(
        string $portName,
        ?string $mode = null,
        ?string $tag = null,
        ?int $vlan = null,
    ) {
        $this->portName = $portName;
        $this->mode = $mode;
        $this->tag = $tag;
        $this->vlan = $vlan;
    }

    public function buildCommand(): string
    {
        $command = "vlan port {$this->portName}";

        if (isset($this->mode)) {
            $command .= " mode {$this->mode} {$this->tag}";

            if ($this->tag === 'vlan') {
                $command .= " {$this->vlan}";
            }
        }

        return $command;
    }
}
