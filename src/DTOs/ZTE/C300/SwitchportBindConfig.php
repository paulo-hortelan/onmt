<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class SwitchportBindConfig
{
    public string $switch;

    public ?int $veip;

    public function __construct(
        string $switch,
        ?int $veip = null,
    ) {
        $this->switch = $switch;
        $this->veip = $veip;
    }

    public function buildCommand(): string
    {
        $command = "switchport-bind {$this->switch}";

        if (isset($this->veip)) {
            $command .= " veip {$this->veip}";
        }

        return $command;
    }
}
