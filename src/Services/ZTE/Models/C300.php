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
    public function ontOpticalPower(array $interfaces): array|float
    {
        $opticalPower = [];

        foreach ($interfaces as $interface) {
            $response = $this->connection->exec("show pon power attenuation $interface");

            if (preg_match('/down.*Rx:(.*)\(dbm\)/m', $response, $match)) {
                $opticalPower[] = (float) $match[1];
            } else {
                throw new \Exception('Ont optical power not found.');
            }
        }

        if (count($opticalPower) === 1) {
            return $opticalPower[0];
        }

        return $opticalPower;
    }

    /**
     * Returns the ONT interface
     */
    public function ontInterface(array $serials): array|string
    {
        $opticalInterface = [];

        foreach ($serials as $serial) {
            $response = $this->connection->exec("show gpon onu by sn $serial");

            if (preg_match('/gpon-onu.*/m', $response, $match)) {
                $opticalInterface[] = (string) $match[0];
            } else {
                throw new \Exception('Ont interface not found.');
            }
        }

        if (count($opticalInterface) === 1) {
            return $opticalInterface[0];
        }

        return $opticalInterface;
    }
}
