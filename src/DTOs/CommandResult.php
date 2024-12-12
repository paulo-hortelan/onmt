<?php

namespace PauloHortelan\Onmt\DTOs;

class CommandResult
{
    public bool $success;

    public string $command;

    public ?string $errorInfo;

    public array $result;

    public function __construct(bool $success, string $command, ?string $errorInfo, array $result = [])
    {
        $this->success = $success;
        $this->command = $command;
        $this->errorInfo = $errorInfo;
        $this->result = $result;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'command' => $this->command,
            'errorInfo' => $this->errorInfo,
            'result' => $this->result,
        ];
    }
}
