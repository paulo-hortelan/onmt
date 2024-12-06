<?php

namespace PauloHortelan\Onmt\Services\Nokia\Models;

use Exception;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

class FX16 extends NokiaService
{
    /**
     * Returns the ONT's equipment optics
     */
    public static function showEquipmentOntOptics(): ?array
    {
        $ontsDetail = [];

        var_dump(self::$interfaces);
        foreach (self::$interfaces as $interface) {
            try {
                $command = "show equipment ont optics $interface detail";
                $response = self::$telnetConn->exec($command);

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
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [
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
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
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
                $command = "show equipment ont index sn:$formattedSerial detail";
                $response = self::$telnetConn->exec($command);

                if (! str_contains($response, 'ont-idx')) {
                    throw new \Exception($response);
                }

                if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
                    $interface = trim($match[1]);
                }

                $ontsInterface[] = [
                    'success' => true,
                    'serial' => $serial,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [
                        'interface' => $interface ?? null,
                    ],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsInterface[] = [
                    'success' => false,
                    'serial' => $serial,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
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
     * Returns the ONT's unprovisioned
     */
    public static function showPonUnprovisionOnu(): ?array
    {
        $unregOnts = [];

        try {
            $command = 'show pon unprovision-onu';

            $response = self::$telnetConn->exec($command);

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
                            'command' => $command,
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
                            'command' => $command,
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
                'command' => $command,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }

        return $unregOnts;
    }

    /**
     * Provision ONT's
     */
    public static function entOnts(EntOntConfig $config): ?array
    {
        $provisionedOnts = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $formattedInterface = str_replace('/', '-', $interface);

            try {
                $entOntCommand = $config->buildCommand();

                $command = "ENT-ONT::ONT-$formattedInterface::::$entOntCommand;";
                self::$tl1Conn->exec($command);

                $provisionedOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $provisionedOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }

        }

        return $provisionedOnts;
    }

    /**
     * Edit provisioned ONT's
     */
    public static function edOnts(EdOntConfig $config): ?array
    {
        $editedOnts = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $formattedInterface = str_replace('/', '-', $interface);

            try {
                $edOntCommand = $config->buildCommand();

                $command = "ED-ONT::ONT-$formattedInterface::::$edOntCommand:IS;";
                self::$tl1Conn->exec($command);

                $editedOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $editedOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }

        }

        return $editedOnts;
    }

    /**
     * Plans a new ONT card at ONT's
     */
    public static function entOntsCard(EntOntCardConfig $config): ?array
    {
        $ontsCard = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $formattedInterface = str_replace('/', '-', $interface);

            try {
                $entOntCardConfigCommand = $config->buildCommand();

                $command = "ENT-ONTCARD::ONTCARD-$formattedInterface-$entOntCardConfigCommand::IS;";
                self::$tl1Conn->exec($command);

                $ontsCard[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsCard[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }

        }

        return $ontsCard;
    }

    /**
     * Get ONT's info by pon interfaces
     */
    public static function showEquipmentOntStatusPon(string $ponInterface): ?array
    {
        $response = [];
        $onts = [];

        try {
            $command = "show equipment ont status pon $ponInterface";
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'sernum')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/sernum/', $column)) {
                    $numOnts = count($response) - $key - 5;

                    if ($numOnts === 0) {
                        $response = [
                            'success' => true,
                            'ponInterface' => $ponInterface,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [],
                        ];

                        break;
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
                            'interface' => $interface ?? null,
                            'serial' => $serial ?? null,
                            'adminStatus' => $adminStatus ?? null,
                            'operStatus' => $operStatus ?? null,
                            'oltRxSigLevel' => $oltRxSigLevel ?? null,
                            'ontOltDistance' => $ontOltDistance ?? null,
                        ];
                    }

                    $response = [
                        'success' => true,
                        'ponInterface' => $ponInterface,
                        'command' => $command,
                        'errorInfo' => null,
                        'result' => $onts,
                    ];
                }
            }
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            $response = [
                'success' => false,
                'ponInterface' => $ponInterface,
                'command' => $command,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }

        return $response;
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
