<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use PauloHortelan\Onmt\Connections\TL1;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Fiberhome\Models\AN551604;

class FiberhomeService
{
    use Assertations, Validations;

    private TL1 $connection;

    private string $model = 'AN551604';

    private string $hostServer;

    private array $interfaces = [];

    private array $serials = [];

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3): mixed
    {
        if (! $this->oltValid($olt)) {
            throw new Exception('OLT brand does not match the service.');
        }

        $this->model = $olt->model;
        $this->hostServer = $olt->host_server;
        $this->connection = TL1::getInstance($olt->host_connection, 3337, $timeout, $streamTimeout, $olt->username, $olt->password, 'Fiberhome-'.$this->model);
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

    public function ont(Ont $ont): mixed
    {
        $this->connect($ont->cto->ceo_splitter->ceo->dio->olt);

        $this->interfaces[] = $ont->interface;
        $this->serials[] = $ont->name;

        return $this;
    }

    public function onts(Collection $onts): mixed
    {
        if ($onts->isEmpty()) {
            throw new Exception('Onts collections is empty.');
        }

        if (! ($onts->first() instanceof Ont)) {
            throw new Exception('The given object model is not an Ont.');
        }

        $this->connect($onts->first()->cto->ceo_splitter->ceo->dio->olt);

        $onts->each(function ($ont) {
            if ($ont instanceof Ont) {
                $this->interfaces[] = $ont->interface;
                $this->serials[] = $ont->name;
            }
        });

        return $this;
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

    public function opticalPower(): float|array|null
    {
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
            return (new AN551604($this->connection, $this->hostServer))->ontOpticalPower($this->interfaces, $this->serials);
        }

        throw new Exception('Model '.$this->model.' is not supported.');
    }
}