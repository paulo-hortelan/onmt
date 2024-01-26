<?php

namespace PauloHortelan\Onmt\Services\Nokia\Models;

use PauloHortelan\Onmt\Connections\Telnet;

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
    public function ontOpticalPower(array $interfaces): array|float|null
    {
        $opticalPower = [];

        foreach ($interfaces as $interface) {
            $response = $this->connection->exec("show equipment ont optics $interface detail");

            if (preg_match('/rx-signal-level.*:(.*\s)/m', $response, $match)) {
                $opticalPower[] = (float) $match[1];
            } else {
                $opticalPower[] = null;
            }
        }

        if (count($opticalPower) === 1) {
            return $opticalPower[0];
        }

        return $opticalPower;
    }

    /**
     * Returns the ONT optical interface
     */
    public function ontOpticalInterface(array $serials): array|string|null
    {
        $opticalInterface = [];

        foreach ($serials as $serial) {
            $formattedSerial = substr_replace($serial, ':', 4, 0);

            $response = $this->connection->exec("show equipment ont index sn:$formattedSerial detail");

            if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
                $opticalInterface[] = trim((string) $match[1]);
            } else {
                $opticalInterface[] = null;
            }
        }

        if (count($opticalInterface) === 1) {
            return $opticalInterface[0];
        }

        return $opticalInterface;
    }
}
