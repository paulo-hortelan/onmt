<?php

namespace PauloHortelan\Onmt\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use PauloHortelan\Onmt\Facades\Fiberhome;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;

class OnmtService
{
    private Olt $olt;

    private mixed $connection;

    private int $timeout;

    private int $streamTimeout;

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3): OnmtService
    {
        $this->olt = $olt;
        $this->timeout = $timeout;
        $this->streamTimeout = $streamTimeout;

        if ($this->olt->brand === 'ZTE') {
            $this->connection = ZTE::connect($this->olt, $this->timeout, $this->streamTimeout);
        } else if ($this->olt->brand === 'Nokia') {
            $this->connection = Nokia::connect($this->olt, $this->timeout, $this->streamTimeout);
        } else if ($this->olt->brand === 'Fiberhome') {
            $this->connection = Fiberhome::connect($this->olt, $this->timeout, $this->streamTimeout);
        }

        return $this;
    }

    public function ont(Ont $ont): mixed
    {
        $this->connect($ont->cto->ceo_splitter->ceo->dio->olt);
        $this->connection->interfaces([$ont->interface]);
        $this->connection->serials([$ont->name]);

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
        $interfaces = [];
        $serials = [];

        $onts->each(function ($ont) use (&$interfaces, &$serials){
            if ($ont instanceof Ont) {
                $interfaces[] = $ont->interface;
                $serials[] = $ont->name;
            }
        });

        $this->connection->interfaces($interfaces);
        $this->connection->serials($serials);

        return $this;
    }    

    public function interface(string $interface): mixed
    {
        $this->connection->interface($interface);

        return $this;
    }

    public function interfaces(array $interfaces): mixed
    {
        $this->connection->interfaces($interfaces);

        return $this;
    }

    public function serial(string $serial): mixed
    {
        $this->connection->serial($serial);

        return $this;
    }

    public function serials(array $serials): mixed
    {
        $this->connection->serials($serials);

        return $this;
    }    

    public function opticalPower(): array|float|null
    {
        return $this->connection->opticalPower();
    }

    // public function ontInterface($serial): string
    // {
    //     $response = $this->connection->exec("show gpon onu by sn $serial");

    //     if (preg_match('/gpon-onu.*/m', $response, $match)) {
    //         $interface = (string) $match[0];
    //     } else {
    //         throw new \Exception('Ont interface not found.');
    //     }

    //     return $interface;
    // }
}
