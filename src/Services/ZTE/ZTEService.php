<?php

namespace PauloHortelan\Onmt\Services\ZTE;

use Exception;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\ZTE\Models\C300;
use PauloHortelan\Onmt\Services\ZTE\Models\C600;

class ZTEService
{
    use Validations;

    private Telnet $connection;

    private string $model = 'C300';

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

        $this->connection = Telnet::getInstance($ipServer, 23, $this->connTimeout, $this->streamTimeout, $username, $password, 'ZTE-'.$this->model);
        $this->connection->stripPromptFromBuffer(true);
        $this->connection->exec('terminal length 0');

        return $this;
    }

    public function disconnect(): void
    {
        if (empty($this->connection)) {
            throw new Exception('No connection established.');
        }

        $this->connection->destroy();
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

    public function ontsInterface(array $serials = []): ?array
    {
        if (! empty($serials)) {
            $this->serials = $serials;
        }

        if (empty($this->serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if ($this->model === 'C300') {
            return (new C300($this->connection))->ontsInterface($this->serials);
        }

        if ($this->model === 'C600') {
            return (new C600($this->connection))->ontsInterface($this->serials);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }

    public function ontsOpticalPowerBySerial(array $serials = []): ?array
    {
        $ontsOpticalPower = [];

        if (! empty($serials)) {
            $this->serials = $serials;
        }

        if (empty($this->serials)) {
            throw new Exception('Serial(s) not found.');
        }

        foreach ($this->serials as $serial) {
            $interfaceResponse = $this->ontsInterface([$serial])[0];

            if ($interfaceResponse['success']) {
                $interface = $interfaceResponse['result']['interface'];
                $ontsOpticalPower[] = $this->ontsOpticalPower([$interface])[0];
            } else {
                $ontsOpticalPower[] = $interfaceResponse;
            }
        }

        return $ontsOpticalPower;
    }

    public function ontsOpticalPower(array $interfaces = []): ?array
    {
        if (! empty($interfaces)) {
            $this->interfaces = $interfaces;
        }

        if (empty($this->interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if ($this->model === 'C300') {
            return (new C300($this->connection))->ontsOpticalPower($this->interfaces);
        }

        if ($this->model === 'C600') {
            return (new C600($this->connection))->ontsOpticalPower($this->interfaces);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }
}
