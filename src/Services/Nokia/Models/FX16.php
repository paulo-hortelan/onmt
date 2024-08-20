<?php

namespace PauloHortelan\Onmt\Services\Nokia\Models;

use Exception;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

class FX16 extends NokiaService
{
    /**
     * Returns the ONT's optical details
     */
    public static function showEquipmentOntOptics(): ?array
    {
        $ontsDetail = [];

        foreach (self::$interfaces as $interface) {
            try {
                $response = self::$telnetConn->exec("show equipment ont optics $interface detail");

                if (! str_contains($response, 'tx-signal-level')) {
                    throw new \Exception($response);
                }

                if (preg_match('/tx-signal-level.*:(.*\s)/m', $response, $match)) {
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

                $ontsDetail[] = [
                    'success' => true,
                    'errorInfo' => null,
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
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsDetail[] = [
                    'success' => false,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                    ],
                ];
            }
        }

        return $ontsDetail;
    }

    /**
     * Returns the ONT's optical interfaces
     */
    public static function showEquipmentOntIndex(): ?array
    {
        $ontsInterface = [];

        foreach (self::$serials as $serial) {
            $formattedSerial = substr_replace($serial, ':', 4, 0);

            try {
                $response = self::$telnetConn->exec("show equipment ont index sn:$formattedSerial detail");

                if (! str_contains($response, 'ont-idx')) {
                    throw new \Exception($response);
                }

                if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
                    $interface = trim($match[1]);
                }

                $ontsInterface[] = [
                    'success' => true,
                    'errorInfo' => null,
                    'result' => [
                        'serial' => $serial,
                        'interface' => $interface ?? null,
                    ],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsInterface[] = [
                    'success' => false,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'serial' => $serial,
                    ],
                ];
            }
        }

        return $ontsInterface;
    }

    /**
     * Returns the ONT's port details
     */
    public static function showInterfacePort(): ?array
    {
        $ontsPortDetail = [];

        foreach (self::$interfaces as $interface) {
            try {
                $response = self::$telnetConn->exec("show interface port ont:$interface detail");

                if (! str_contains($response, 'opr-status')) {
                    throw new \Exception($response);
                }

                if (preg_match('/opr-status.*?:(.*?[^\s]+)/m', $response, $match)) {
                    $oprStatus = trim($match[1]);
                }

                if (preg_match('/admin-status.*?:(.*?[^\s]+)/m', $response, $match)) {
                    $adminStatus = trim($match[1]);
                }

                if (preg_match('/last-chg-opr-stat.*?:(.*?[^\s]+)/m', $response, $match)) {
                    $lastChgOprStat = trim($match[1]);
                }

                $ontsPortDetail[] = [
                    'success' => true,
                    'errorInfo' => null,
                    'result' => [
                        'interface' => $interface,
                        'oprStatus' => $oprStatus ?? null,
                        'adminStatus' => $adminStatus ?? null,
                        'lastChgOprStat' => $lastChgOprStat ?? null,
                    ],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsPortDetail[] = [
                    'success' => false,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                    ],
                ];
            }
        }

        return $ontsPortDetail;
    }

    /**
     * Returns the ONT's optical details
     */
    public static function showPonUnprovisionOnu(): ?array
    {
        $unregOnts = [];

        try {
            $response = self::$telnetConn->exec('show pon unprovision-onu');

            if (! str_contains($response, 'gpon-index')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/gpon-index/', $column)) {
                    $numOnts = count($response) - $key - 5;

                    if ($numOnts === 0) {
                        $unregOnts[] = [
                            'success' => true,
                            'errorInfo' => null,
                            'result' => [],
                        ];
                    }

                    for ($i = 1; $i <= $numOnts; $i++) {
                        $splitted = preg_split('/\s+/', $response[$key + $i + 1]);

                        $alarmIdx = (int) $splitted[1];
                        $interface = $splitted[2];
                        $serial = $splitted[3];

                        $unregOnts[] = [
                            'success' => true,
                            'errorInfo' => null,
                            'result' => [
                                'alarmIdx' => $alarmIdx ?? null,
                                'interface' => $interface ?? null,
                                'serial' => $serial ?? null,
                            ],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            $unregOnts[] = [
                'success' => false,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }

        return $unregOnts;
    }

    /**
     * Provision ONT's
     */
    public static function entOnt(array $tid, array $ctag, array $ontNblk): ?array
    {
        $provisionedOnt = [];

        $interface = self::$interfaces[0];
        $formattedInterface = str_replace('/', '-', $interface);
        $serial = self::$serials[0];

        $command = self::formatCommandEntOnt($tid, $formattedInterface, $ctag, $ontNblk);
        $command .= ",SERNUM=\"$serial\";";

        var_dump($command);

        // $pppoeUsername = $pppoeUsernames[$i];
        // $swVerPlnd = $swVerPlnds[$i];
        // $opticShist = $opticShists[$i] ?? null;
        // $plndCfgfile1 = $plndCfgfiles1[$i] ?? null;
        // $dlCfgfile1 = $dlCfgfiles1[$i] ?? null;
        // $voidAllowed = $voidAlloweds[$i] ?? null;

        // $command = "ENT-ONT::ONT-$formattedInterface::::DESC1=\"$pppoeUsername\",DESC2=\"$pppoeUsername\",SERNUM=$serial,SWVERPLND=$swVerPlnd;";

        // $optionalParts = array_filter([$opticShist, $plndCfgfile1, $dlCfgfile1, $voidAllowed]);

        // if (!empty($optionalParts)) {
        //     $command .= "," . implode(",", $optionalParts) . ";";
        // } else {
        //     $command .= ";";
        // }

        try {
            self::$tl1Conn->exec($command);

            $provisionedOnt[] = [
                'success' => true,
                'errorInfo' => null,
                'result' => [
                    'interface' => $interface,
                ],
            ];
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            $provisionedOnt[] = [
                'success' => false,
                'errorInfo' => $errorInfo,
                'result' => [
                    'interface' => $interface,
                ],
            ];
        }

        return $provisionedOnt;
    }

    /**
     * Get ONT's info by pon interfaces
     */
    public static function showEquipmentOntStatusPon($ponInterfaces): ?array
    {
        $onts = [];

        foreach ($ponInterfaces as $ponInterface) {
            try {
                $response = self::$telnetConn->exec("show equipment ont status pon $ponInterface");

                if (! str_contains($response, 'sernum')) {
                    throw new \Exception($response);
                }

                $response = preg_split("/\r\n|\n|\r/", $response);

                foreach ($response as $key => $column) {
                    if (preg_match('/sernum/', $column)) {
                        $numOnts = count($response) - $key - 5;

                        if ($numOnts === 0) {
                            $onts[] = [
                                'success' => true,
                                'errorInfo' => null,
                                'result' => [
                                    'ponInterface' => $ponInterface,
                                ],
                            ];
                        }

                        for ($i = 1; $i <= $numOnts; $i++) {
                            $splitted = preg_split('/\s+/', $response[$key + $i + 1]);

                            $ponInterface = $splitted[0];
                            $interface = $splitted[1];
                            $serial = $splitted[2];
                            $adminStatus = $splitted[3];
                            $operStatus = $splitted[4];
                            $oltRxSigLevel = $splitted[5];
                            $ontOltDistance = $splitted[6];

                            $onts[] = [
                                'success' => true,
                                'errorInfo' => null,
                                'result' => [
                                    'ponInterface' => $ponInterface,
                                    'interface' => $interface ?? null,
                                    'serial' => $serial ?? null,
                                    'adminStatus' => $adminStatus ?? null,
                                    'operStatus' => $operStatus ?? null,
                                    'oltRxSigLevel' => $oltRxSigLevel ?? null,
                                    'ontOltDistance' => $ontOltDistance ?? null,
                                ],
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $onts[] = [
                    'success' => false,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'ponInterface' => $ponInterface,
                    ],
                ];
            }
        }

        return $onts;
    }

    /**
     * Configure ONT's admin state
     */
    public static function configureEquipmentOntInterfaceAdminState($adminState): ?array
    {
        $adminStates = ['down', 'up'];

        if (! in_array($adminState, $adminStates)) {
            throw new Exception("AdminState must be 'down' or 'up'");
        }

        $configuredOnts = [];

        foreach (self::$interfaces as $interface) {
            try {
                self::$telnetConn->exec("configure equipment ont interface $interface admin-state $adminState");

                $configuredOnts[] = [
                    'success' => true,
                    'errorInfo' => null,
                    'result' => [
                        'interface' => $interface,
                    ],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configuredOnts[] = [
                    'success' => false,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                    ],
                ];
            }
        }

        return $configuredOnts;
    }

    /**
     * Remove ONT's
     */
    public static function configureEquipmentOntNoInterface(): ?array
    {
        $removedOnts = [];

        foreach (self::$interfaces as $interface) {
            try {
                self::$telnetConn->exec("configure equipment ont no interface $interface");

                $removedOnts[] = [
                    'success' => true,
                    'errorInfo' => null,
                    'result' => [
                        'interface' => $interface,
                    ],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $removedOnts[] = [
                    'success' => false,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                    ],
                ];
            }
        }

        return $removedOnts;
    }
}
