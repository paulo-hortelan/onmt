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
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

class FX16 extends NokiaService
{
    /**
     * Inhibit environment alarms - Telnet
     */
    public static function environmentInhibitAlarms(): ?CommandResult
    {
        $command = 'environment inhibit-alarms';

        try {
            self::$telnetConn->exec($command);

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Executes the given command - Telnet
     */
    public static function executeCommandTelnet(string $command): ?CommandResult
    {
        try {
            self::$tl1Conn->exec($command);

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Executes the given command - TL1
     */
    public static function executeCommandTL1(string $command): ?CommandResult
    {
        try {
            self::$telnetConn->exec($command);

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Get ONT's info by PON interface - Telnet
     */
    public static function showEquipmentOntStatusPon(string $ponInterface): ?CommandResult
    {
        $onts = [];
        $command = "show equipment ont status pon $ponInterface";

        try {
            $commandResponse = self::$telnetConn->exec($command);

            if (! str_contains($commandResponse, 'sernum')) {
                throw new \Exception($commandResponse);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $commandResponse);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/sernum/', $column)) {
                    $numOnts = count($splittedResponse) - $key - 5;

                    if ($numOnts === 0) {
                        return CommandResult::create([
                            'success' => true,
                            'command' => $command,
                            'error' => null,
                            'result' => [],
                        ]);
                    }

                    for ($i = 1; $i <= $numOnts; $i++) {
                        $splitted = preg_split('/\s+/', $splittedResponse[$key + $i + 1]);

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
                }
            }
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'error' => null,
            'result' => $onts,
        ]);
    }

    /**
     * Configure ONT admin state - Telnet
     */
    public static function configureEquipmentOntInterfaceAdminState(string $interface, string $adminState): ?CommandResult
    {
        $adminStates = ['down', 'up'];

        if (! in_array($adminState, $adminStates)) {
            throw new Exception("AdminState must be 'down' or 'up'");
        }

        $command = "configure equipment ont interface $interface admin-state $adminState";

        try {
            self::$telnetConn->exec($command);

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Remove ONT interface - Telnet
     */
    public static function configureEquipmentOntNoInterface(string $interface): ?CommandResult
    {
        $command = "configure equipment ont no interface $interface";

        try {
            self::$telnetConn->exec($command);

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Returns the ONT equipment optics - Telnet
     */
    public static function showEquipmentOntOptics(string $interface): ?CommandResult
    {
        $command = "show equipment ont optics $interface detail";

        try {
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

            $ontDetail = [
                'txSignalLevel' => $txSignalLevel ?? null,
                'ontVoltage' => $ontVoltage ?? null,
                'oltRxSigLevel' => $oltRxSigLevel ?? null,
                'rxSignalLevel' => $rxSignalLevel ?? null,
                'ontTemperature' => $ontTemperature ?? null,
                'laserBiasCurr' => $laserBiasCurr ?? null,
            ];

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => $ontDetail,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Returns the ONT optical interface - Telnet
     */
    public static function showEquipmentOntIndex(string $serial): ?CommandResult
    {
        $formattedSerial = substr_replace($serial, ':', 4, 0);
        $command = "show equipment ont index sn:$formattedSerial detail";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'ont-idx')) {
                throw new \Exception($response);
            }

            if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
                $interface = trim($match[1]);
            }

            $ontInterface = [
                'interface' => $interface ?? null,
            ];

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => $ontInterface,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return $ontsInterface;
    }

    /**
     * Returns the ONT port details - Telnet
     */
    public static function showInterfacePort(string $interface): ?CommandResult
    {
        $command = "show interface port ont:$interface detail";

        try {
            $response = self::$telnetConn->exec($command);

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

            $portDetail[] = [
                'interface' => $interface,
                'oprStatus' => $oprStatus ?? null,
                'adminStatus' => $adminStatus ?? null,
                'lastChgOprStat' => $lastChgOprStat ?? null,
            ];

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => $portDetail,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Returns the ONT's unprovisioned - Telnet
     */
    public static function showPonUnprovisionOnu(): ?CommandResult
    {
        $unregData = [];
        $command = 'show pon unprovision-onu';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'gpon-index')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/gpon-index/', $column)) {
                    $numOnts = count($splittedResponse) - $key - 5;

                    if ($numOnts === 0) {
                        return CommandResult::create([
                            'success' => true,
                            'command' => $command,
                            'error' => null,
                            'result' => [],
                        ]);
                    }

                    for ($i = 1; $i <= $numOnts; $i++) {
                        $splitted = preg_split('/\s+/', $splittedResponse[$key + $i + 1]);

                        $alarmIdx = (int) $splitted[1];
                        $interface = $splitted[2];
                        $serial = $splitted[3];

                        $unregData[] = [
                            'alarmIdx' => $alarmIdx ?? null,
                            'interface' => $interface ?? null,
                            'serial' => $serial ?? null,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'error' => null,
            'result' => $unregData,
        ]);
    }

    /**
     * Inhibit all messages - TL1
     */
    public static function inhMsgAll(): ?CommandResult
    {
        $command = 'INH-MSG-ALL::ALL:::;';

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Provision ONT - TL1
     */
    public static function entOnt(string $interface, EntOntConfig $config): ?CommandResult
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $entOntCommand = $config->buildCommand();
        $command = "ENT-ONT::ONT-$formattedInterface::::$entOntCommand;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Edit provisioned ONT - TL1
     */
    public static function edOnt(string $interface, EdOntConfig $config): ?CommandResult
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $edOntCommand = $config->buildCommand();
        $command = "ED-ONT::ONT-$formattedInterface::::$edOntCommand:IS;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Plans a new ONT card - TL1
     */
    public static function entOntsCard(string $interface, EntOntCardConfig $config): ?CommandResult
    {
        $entOntCardConfigCommand = $config->buildCommand();
        $accessIdentifier = $config->buildIdentifier($interface);
        $command = "ENT-ONTCARD::$accessIdentifier:::$entOntCardConfigCommand::IS;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Creates a logical port on an LT - TL1
     */
    public static function entLogPort(string $interface, EntLogPortConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface);
        $command = "ENT-LOGPORT::$accessIdentifier:::;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Edit ONT VEIP - TL1
     */
    public static function edOntVeip(string $interface, EdOntVeipConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface);
        $command = "ED-ONTVEIP::$accessIdentifier:::::;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Configures upstream queue - TL1
     */
    public static function setQosUsQueue(string $interface, QosUsQueueConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface);
        $buildCommand = $config->buildCommand();
        $command = "SET-QOS-USQUEUE::$accessIdentifier::::$buildCommand;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Bounds a bridge port to the VLAN - TL1
     */
    public static function setVlanPort(string $interface, VlanPortConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
        $buildCommand = $config->buildCommand();
        $command = "SET-VLANPORT::$accessIdentifier:::$buildCommand;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Adds a egress port to the VLAN - TL1
     */
    public static function entVlanEgPort(string $interface, VlanEgPortConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
        $buildCommand = $config->buildCommand();
        $command = "ENT-VLANEGPORT::$accessIdentifier:::$buildCommand;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Provisions a new HGU TR069 short key-value pair - TL1
     */
    public static function entHguTr069Sparam(string $interface, EntHguTr069SparamConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
        $buildCommand = $config->buildCommand();
        $command = "ENT-HGUTR069-SPARAM::$accessIdentifier::::$buildCommand;";

        try {
            $response = self::$tl1Conn->exec($command, false);

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }
}
