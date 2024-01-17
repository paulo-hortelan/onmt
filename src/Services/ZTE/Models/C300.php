<?php

namespace PauloHortelan\OltMonitoring\Services\ZTE\Models;

use PauloHortelan\OltMonitoring\Connections\Telnet;

class C300
{
    protected Telnet $connection;

    public function __construct(Telnet $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the ONT optical power
     */
    public function ontOpticalPower(string $interface): float
    {
        $response = $this->connection->exec("show pon power attenuation $interface");

        if (preg_match('/down.*Rx:(.*)\(dbm\)/m', $response, $match)) {
            $opticalPower = (float) $match[1];
        } else {
            throw new \Exception('Ont optical power not found.');
        }

        return $opticalPower;
    }

    /**
     * Returns the ONT interface
     */
    public function ontInterface(string $serial): string
    {
        $response = $this->connection->exec("show gpon onu by sn $serial");

        if (preg_match('/gpon-onu.*/m', $response, $match)) {
            $interface = (string) $match[0];
        } else {
            dump($response);
            throw new \Exception('Ont interface not found.');
        }

        return $interface;
    }
}
