<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureInterfacePort
{
    public string $interfacePort;

    public string $adminStatus;

    public function __construct(
        string $interfacePort,
        ?string $adminStatus,
    ) {
        $this->interfacePort = $interfacePort;
        $this->adminStatus = $adminStatus;
    }

    public function buildCommand(): string
    {
        $command = '';

        $command .= "{$this->adminStatus}";

        return $command;
    }
}
