<?php

namespace PauloHortelan\Onmt\Services\Nokia\Models;

use Exception;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntHguTr069SparamConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\QosUsQueueConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanEgPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanPortConfig;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

class FX16 extends NokiaService
{
    /**
     * Inhibit environment alarms - Telnet
     */
    public static function environmentInhibitAlarms(): ?array
    {
        $command = 'environment inhibit-alarms';

        try {
            self::$telnetConn->exec($command);

            return [
                'success' => true,
                'command' => $command,
                'errorInfo' => null,
                'result' => [],
            ];
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            return [
                'success' => false,
                'command' => $command,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }
    }

    /**
     * Get ONT's info by PON interfaces - Telnet
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
     * Configure ONT's admin state - Telnet
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
                $command = "configure equipment ont interface $interface admin-state $adminState";
                self::$telnetConn->exec($command);

                $configuredOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configuredOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $configuredOnts;
    }

    /**
     * Remove ONT's - Telnet
     */
    public static function configureEquipmentOntNoInterface(): ?array
    {
        $removedOnts = [];

        foreach (self::$interfaces as $interface) {
            try {
                $command = "configure equipment ont no interface $interface";
                self::$telnetConn->exec($command);

                $removedOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $removedOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $removedOnts;
    }

    /**
     * Returns the ONT's equipment optics - Telnet
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
     * Returns the ONT's optical interfaces - Telnet
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
     * Returns the ONT's port details - Telnet
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
     * Returns the ONT's unprovisioned - Telnet
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
     * Inhibit all messages - TL1
     */
    public static function inhMsgAll(): ?array
    {
        try {
            $command = 'INH-MSG-ALL::ALL:::;';
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return [
                'success' => true,
                'command' => $command,
                'errorInfo' => null,
                'result' => [],
            ];
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            return [
                'success' => false,
                'command' => $command,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }
    }

    /**
     * Provision ONT's - TL1
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
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

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
     * Edit provisioned ONT's - TL1
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
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

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
     * Plans a new ONT card - TL1
     */
    public static function entOntsCard(EntOntCardConfig $config): ?array
    {
        $ontsCard = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $entOntCardConfigCommand = $config->buildCommand();
                $accessIdentifier = $config->buildIdentifier($interface, 14);

                $command = "ENT-ONTCARD::$accessIdentifier:::$entOntCardConfigCommand::IS;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

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
     * Creates a logical port on an LT - TL1
     */
    public static function entLogPort(EntLogPortConfig $config): ?array
    {
        $ontsLTLogPort = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $accessIdentifier = $config->buildIdentifier($interface, 14, 1);

                $command = "ENT-LOGPORT::$accessIdentifier:::;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

                $ontsLTLogPort[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsLTLogPort[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }

        }

        return $ontsLTLogPort;
    }

    /**
     * Edit ONT's VEIP - TL1
     */
    public static function edOntVeip(EdOntVeipConfig $config): ?array
    {
        $editedOntsVeip = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $accessIdentifier = $config->buildIdentifier($interface, 14, 1);

                $command = "ED-ONTVEIP::$accessIdentifier:::::;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

                $editedOntsVeip[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $editedOntsVeip[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $editedOntsVeip;
    }

    /**
     * Configures upstream queue - TL1
     */
    public static function setQosUsQueue(QosUsQueueConfig $config): ?array
    {
        $configuredOnts = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $accessIdentifier = $config->buildIdentifier($interface, 14, 1, 0);
                $buildCommand = $config->buildCommand();

                $command = "SET-QOS-USQUEUE::$accessIdentifier::::$buildCommand;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

                $configuredOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configuredOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $configuredOnts;
    }

    /**
     * Bounds a bridge port to the VLAN - TL1
     */
    public static function setVlanPort(VlanPortConfig $config): ?array
    {
        $configuredOnts = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
                $buildCommand = $config->buildCommand();

                $command = "SET-VLANPORT::$accessIdentifier:::$buildCommand;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

                $configuredOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configuredOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $configuredOnts;
    }

    /**
     * Adds a egress port to the VLAN - TL1
     */
    public static function entVlanEgPort(VlanEgPortConfig $config): ?array
    {
        $configuredOnts = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
                $buildCommand = $config->buildCommand();

                $command = "ENT-VLANEGPORT::$accessIdentifier:::$buildCommand;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

                $configuredOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configuredOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $configuredOnts;
    }

    /**
     * Provisions a new HGU TR069 short key-value pair - TL1
     */
    public static function entHguTr069Sparam(EntHguTr069SparamConfig $config): ?array
    {
        $configuredOnts = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];

            try {
                $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
                $buildCommand = $config->buildCommand();

                $command = "ENT-HGUTR069-SPARAM::$accessIdentifier::::$buildCommand;";
                $response = self::$tl1Conn->exec($command, false);

                if (! str_contains($response, 'M  0 COMPLD')) {
                    throw new \Exception($response);
                }

                $configuredOnts[] = [
                    'success' => true,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configuredOnts[] = [
                    'success' => false,
                    'interface' => $interface,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $configuredOnts;
    }
}
