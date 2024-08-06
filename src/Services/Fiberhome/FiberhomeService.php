<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Fiberhome\Models\AN551604;

class FiberhomeService
{
    use Assertations, Validations;

    private TL1 $connection;

    private string $model = 'AN551604';

    protected int $connTimeout = 4;

    protected int $streamTimeout = 2;

    protected string $ipOlt;

    public array $serials = [];

    public array $interfaces = [];

    public function connect(string $ipOlt, string $username, string $password, ?string $ipServer = null): mixed
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        $this->ipOlt = $ipOlt;
        $this->connection = TL1::getInstance($ipServer, 3337, $this->connTimeout, $this->streamTimeout, $username, $password, 'Fiberhome-'.$this->model);
        $this->connection->stripPromptFromBuffer(true);

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

    /**
     * Gets ONT's optical powers
     *
     * @param  array  $interfaces  Interfaces list like 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serials list like 'CMSZ123456'
     * @return array List with info about each ONT Power, in which 'rxPower' is the most important info
     */
    public function opticalPowers(array $interfaces = [], array $serials = []): ?array
    {
        if (! empty($interfaces)) {
            $this->interfaces = $interfaces;
        }

        if (! empty($serials)) {
            $this->serials = $serials;
        }

        if (empty($this->interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty($this->serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (! $this->assertSameLength($this->interfaces, $this->serials)) {
            throw new Exception('The number of interfaces and serials are not the same.');
        }

        if ($this->model === 'AN551604') {
            return (new AN551604($this->connection, $this->ipOlt))->ontOpticalPowers($this->interfaces, $this->serials);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }

    public function opticalStates(array $interfaces = [], array $serials = []): ?array
    {
        if (! empty($interfaces)) {
            $this->interfaces = $interfaces;
        }

        if (! empty($serials)) {
            $this->serials = $serials;
        }

        if (empty($this->interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty($this->serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (! $this->assertSameLength($this->interfaces, $this->serials)) {
            throw new Exception('The number of interfaces and serials are not the same.');
        }

        if ($this->model === 'AN551604') {
            return (new AN551604($this->connection, $this->ipOlt))->ontOpticalStates($this->interfaces, $this->serials);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }
}
