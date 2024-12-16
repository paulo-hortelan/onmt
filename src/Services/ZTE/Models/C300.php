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
     * Returns the ONT's optical power
     */
    public function ontsOpticalPower(array $interfaces): ?array
    {
        $ontsOpticalPower = [];

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
                $error = $e->getMessage();
            }

            if (! $success) {
                $error = 'Interface not found on OLT';
            }

            $ontsOpticalPower[] = [
                'success' => $success,
                'error' => $error ?? null,
                'result' => [
                    'interface' => $interface,
                    'downRxPower' => $downRxPower ?? null,
                    'downTxPower' => $downTxPower ?? null,
                ],
            ];
        }

        return $ontsOpticalPower;
    }

    /**
     * Returns the ONT's interface
     */
    public function ontsInterface(array $serials): ?array
    {
        $ontsInterface = [];

        foreach ($serials as $serial) {
            $success = false;

            try {
                $response = $this->connection->exec("show gpon onu by sn $serial");

                if (preg_match('/gpon-onu.*/m', $response, $match)) {
                    $success = true;
                    $interface = trim($match[0]);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }

            if (! $success) {
                $error = 'Interface not found on OLT';
            }

            $ontsInterface[] = [
                'success' => $success,
                'error' => $error ?? null,
                'result' => [
                    'serial' => $serial,
                    'interface' => $interface ?? null,
                ],
            ];
        }

        return $ontsInterface;
    }
}
