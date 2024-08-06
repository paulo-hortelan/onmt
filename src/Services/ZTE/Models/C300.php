<?php

namespace PauloHortelan\Onmt\Services\ZTE\Models;

use PauloHortelan\Onmt\Services\Connections\Telnet;

class C300
{
    protected Telnet $connection;

    public function __construct(Telnet $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the ONT optical powers
     */
    public function ontOpticalPowers(array $interfaces): array|float|null
    {
        $opticalPowers = [];

        foreach ($interfaces as $interface) {
            $success = false;

            try {
                $response = $this->connection->exec("show pon power attenuation $interface");

                if (preg_match('/down.*?:(.*?[^(]+)/m', $response, $match)) {
                    $success = true;
                    $downTxPower = (float) $match[1];
                }

                if (preg_match('/down.*:(.*?[^(]+)/m', $response, $match)) {
                    $downRxPower = (float) $match[1];
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            if (!$success) {
                $errorInfo = 'Interface not found on OLT';
            }

            $opticalPowers[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'interface' => $interface,
                    'downRxPower' => $downRxPower ?? null,
                    'downTxPower' => $downTxPower ?? null,
                ],
            ];
        }

        return $opticalPowers;
    }

    /**
     * Returns the ONT optical interfaces
     */
    public function ontOpticalInterfaces(array $serials): ?array
    {
        $opticalInterfaces = [];

        foreach ($serials as $serial) {
            $success = false;

            try {
                $response = $this->connection->exec("show gpon onu by sn $serial");

                if (preg_match('/gpon-onu.*/m', $response, $match)) {
                    $success = true;
                    $interface = trim($match[0]);
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            if (!$success) {
                $errorInfo = 'Interface not found on OLT';
            }

            $opticalInterfaces[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'serial' => $serial,
                    'interface' => $interface ?? null,
                ],
            ];
        }

        return $opticalInterfaces;
    }
}
