<?php

namespace PauloHortelan\OltMonitoring\Services;

use PauloHortelan\OltMonitoring\Facades\ZTE;
use PauloHortelan\OltMonitoring\Models\Olt;

class OltMonitorService
{
    private Olt $olt;

    private mixed $connection;

    private int $timeout;

    private int $streamTimeout;

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3): OltMonitorService
    {
        $this->olt = $olt;
        $this->timeout = $timeout;
        $this->streamTimeout = $streamTimeout;

        if ($this->olt->brand === 'ZTE') {
            $this->connection = ZTE::connect($this->olt, $this->timeout, $this->streamTimeout);
            // if ($this->olt->model === 'C300') {
            //     if (method_exists(C300::class, 'connect')) {
            //         $this->connection = C300::connect($this->olt, $this->timeout, $this->streamTimeout);
            //     }
            // } elseif ($this->olt->model === 'C600') {
            //     if (method_exists(ZTEC600::class, 'connect')) {
            //         $this->connection = ZTEC600::connect($this->olt, $this->timeout, $this->streamTimeout);
            //     }
            // }
        }

        return $this;
    }

    public function ontOpticalPower(string $interface): float
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
