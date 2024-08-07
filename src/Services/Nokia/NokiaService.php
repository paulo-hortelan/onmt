<?php

namespace PauloHortelan\Onmt\Services\Nokia;

use Exception;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;

class NokiaService
{
    use Validations;

    private Telnet $connection;

    protected string $model = 'FX16';

    protected int $connTimeout = 4;

    protected int $streamTimeout = 2;

    public array $serials = [];

    public array $interfaces = [];

    public function connect(string $ipOlt, string $username, string $password, ?string $ipServer = null): mixed
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        $this->connection = Telnet::getInstance($ipServer, 23, $this->connTimeout, $this->streamTimeout, $username, $password, 'Nokia-'.$this->model);
        $this->connection->stripPromptFromBuffer(true);
        $this->connection->exec('environment inhibit-alarms');

        return $this;
    }

    public function disconnect(): void
    {
        if (empty($this->connection)) {
            throw new Exception('No connection established.');
        }

        $this->connection->destroy();
    }

    public function model(string $model): void
    {
        $this->model = $model;
    }

    public function timeout(int $connTimeout, int $streamTimeout): void
    {
        $this->connTimeout = $connTimeout;
        $this->streamTimeout = $streamTimeout;
    }

    public function interface(string $interface): mixed
    {
        $this->interfaces = [$interface];

        return $this;
    }

    public function interfaces(array $interfaces): mixed
    {
        $this->interfaces = $interfaces;

        return $this;
    }

    public function serial(string $serial): mixed
    {
        $this->serials = [$serial];

        return $this;
    }

    public function serials(array $serials): mixed
    {
        $this->serials = $serials;

        return $this;
    }

    public function opticalDetails(array $interfaces = []): ?array
    {
        if (! empty($interfaces)) {
            $this->interfaces = $interfaces;
        }

        if (empty($this->interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if ($this->model === 'FX16') {
            return (new FX16($this->connection))->ontOpticalDetails($this->interfaces);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }

    public function opticalInterfaces(array $serials = []): ?array
    {
        if (! empty($serials)) {
            $this->serials = $serials;
        }

        if (empty($this->serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if ($this->model === 'FX16') {
            return (new FX16($this->connection))->ontOpticalInterfaces($this->serials);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }

    public function opticalDetailsBySerials(array $serials = []): ?array
    {
        $opticalDetails = [];

        if (! empty($serials)) {
            $this->serials = $serials;
        }

        if (empty($this->serials)) {
            throw new Exception('Serial(s) not found.');
        }

        foreach ($this->serials as $serial) {
            $interfaceResponse = $this->opticalInterfaces([$serial])[0];

            if ($interfaceResponse['success']) {
                $interface = $interfaceResponse['result']['interface'];
                $opticalDetails[] = $this->opticalDetails([$interface])[0];
            } else {
                $opticalDetails[] = $interfaceResponse;
            }
        }

        return $opticalDetails;
    }

    public function portDetails(array $interfaces = []): ?array
    {
        if (! empty($interfaces)) {
            $this->interfaces = $interfaces;
        }

        if (empty($this->interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if ($this->model === 'FX16') {
            return (new FX16($this->connection))->ontPortDetails($this->interfaces);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }
}
