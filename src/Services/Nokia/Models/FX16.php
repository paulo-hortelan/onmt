<?php

namespace PauloHortelan\OltMonitoring\Services\Nokia\Models;

use PauloHortelan\OltMonitoring\Connections\Telnet;

class FX16
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
        $response = $this->connection->exec("show equipment ont optics $interface detail");

        if (preg_match('/rx-signal-level.*:(.*\s)/m', $response, $match)) {
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
        $formattedSerial = substr_replace($serial, ':', 4, 0);

        $response = $this->connection->exec("show equipment ont index sn:$formattedSerial detail");

        if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
            $interface = trim((string) $match[1]);
        } else {
            throw new \Exception('Ont interface not found.');
        }

        return $interface;
    }
}
