<?php

namespace PauloHortelan\OltMonitoring\Services;

use PauloHortelan\OltMonitoring\Facades\Zte300;
use PauloHortelan\OltMonitoring\Facades\Zte600;
use PauloHortelan\OltMonitoring\Models\Olt;

class OltMonitorService
{
    private Olt $olt;

    private mixed $connection;

    private int $timeout;

    private int $streamTimeout;

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3)
    {
        $this->olt = $olt;
        $this->timeout = $timeout;
        $this->streamTimeout = $streamTimeout;

        if ($this->olt->brand === 'ZTE') {
            if ($this->olt->product_model === 'C300') {
                $this->connection = Zte300::connect($this->olt, $this->timeout, $this->streamTimeout);
            } elseif ($this->olt->product_model === 'C600') {
                $this->connection = Zte600::connect($this->olt, $this->timeout, $this->streamTimeout);
            }
        }

        return $this;
    }

    public function ontOpticalPower($interface): float
    {
        return $this->connection->ontOpticalPower($interface);
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
