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
     * Returns the ONT optical details
     */
    public function ontOpticalDetails(array $interfaces): array|float|null
    {
        $opticalDetails = [];

        foreach ($interfaces as $interface) {
            $success = false;

            try {
                $response = $this->connection->exec("show equipment ont optics $interface detail");

                if (preg_match('/tx-signal-level.*:(.*\s)/m', $response, $match)) {
                    $success = true;
                    $txSignalLevel = (float) $match[1];
                }

                if (preg_match('/ont-voltage.*:(.*\s)/m', $response, $match)) {
                    $ontVoltage = (float) $match[1];
                }

                if (preg_match('/olt-rx-sig-level.*:(.*\s)/m', $response, $match)) {
                    $oltRxSigLevel = (float) $match[1];
                }

                if (preg_match('/rx-signal-level.*:(.*\s)/m', $response, $match)) {
                    $rxSignalLevel = (float) $match[1];
                }

                if (preg_match('/ont-temperature.*:(.*\s)/m', $response, $match)) {
                    $ontTemperature = (float) $match[1];
                }

                if (preg_match('/laser-bias-curr.*:(.*\s)/m', $response, $match)) {
                    $laserBiasCurr = (float) $match[1];
                }

                if (! $success) {
                    $errorInfo = $response;
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            $opticalDetails[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'interface' => $interface,
                    'txSignalLevel' => $txSignalLevel ?? null,
                    'ontVoltage' => $ontVoltage ?? null,
                    'oltRxSigLevel' => $oltRxSigLevel ?? null,
                    'rxSignalLevel' => $rxSignalLevel ?? null,
                    'ontTemperature' => $ontTemperature ?? null,
                    'laserBiasCurr' => $laserBiasCurr ?? null,
                ],
            ];
        }

        return $opticalDetails;
    }

    /**
     * Returns the ONT optical interfaces
     */
    public function ontOpticalInterfaces(array $serials): array|string|null
    {
        $opticalInterfaces = [];

        foreach ($serials as $serial) {
            $success = false;
            $formattedSerial = substr_replace($serial, ':', 4, 0);

            try {
                $response = $this->connection->exec("show equipment ont index sn:$formattedSerial detail");

                if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
                    $success = true;
                    $interface = trim($match[1]);
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            if (! $success) {
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

    /**
     * Returns the ONT port details
     */
    public function ontPortDetails(array $interfaces): array|string|null
    {
        $portDetails = [];

        foreach ($interfaces as $interface) {
            $success = false;

            try {
                $response = $this->connection->exec("show interface port ont:$interface detail");

                if (preg_match('/opr-status.*?:(.*?[^\s]+)/m', $response, $match)) {
                    $success = true;
                    $oprStatus = trim($match[1]);
                }

                if (preg_match('/admin-status.*?:(.*?[^\s]+)/m', $response, $match)) {
                    $adminStatus = trim($match[1]);
                }

                if (preg_match('/last-chg-opr-stat.*?:(.*?[^\s]+)/m', $response, $match)) {
                    $lastChgOprStat = trim($match[1]);
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            if (! $success) {
                $errorInfo = 'Interface not found on OLT';
            }

            $portDetails[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'interface' => $interface,
                    'oprStatus' => $oprStatus ?? null,
                    'adminStatus' => $adminStatus ?? null,
                    'lastChgOprStat' => $lastChgOprStat ?? null,
                ],
            ];
        }

        return $portDetails;
    }
}
