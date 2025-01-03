<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class SwitchportBindConfig
{
    public string $switchName;

    public ?int $veip;

    public ?int $iphost;

    public function __construct(
        string $switchName,
        ?int $veip = null,
        ?int $iphost = null,
    ) {
        $this->switchName = $switchName;
        $this->veip = $veip;
        $this->iphost = $iphost;
    }

    public function buildCommand(): string
    {
        $command = "switchport-bind {$this->switchName}";

        if (isset($this->veip)) {
            $command .= " veip {$this->veip}";
        }

        if (isset($this->iphost)) {
            $command .= " iphost {$this->iphost}";
        }

        return $command;
    }
}
