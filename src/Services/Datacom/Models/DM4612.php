<?php

namespace PauloHortelan\Onmt\Services\Datacom\Models;

use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Services\Concerns\DatacomTrait;
use PauloHortelan\Onmt\Services\Datacom\DatacomService;

class DM4612 extends DatacomService
{
    use DatacomTrait;

    /**
     * Shows all discovered ONUs on the GPON interfaces
     */
    public static function showInterfaceGponDiscoveredOnus(): ?CommandResult
    {
        $command = 'show interface gpon discovered-onus';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'Serial Number') && ! str_contains($response, 'No entries found')) {
                throw new \Exception($response);
            }

            if (str_contains($response, 'No entries found')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                ]);
            }

            $onuInfo = [];

            $lines = explode("\n", $response);
            $lines = array_slice($lines, 4);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if (preg_match('/^\s*(\d+\/\d+\/\d+)\s+([A-Z0-9]+)\s*$/', $line, $matches)) {
                    $onuInfo[] = [
                        'interface' => $matches[1],
                        'serial-number' => $matches[2],
                    ];
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $onuInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Shows detailed information about a specific ONU on a GPON interface
     */
    public static function showInterfaceGponOnu(string $ponInterface, string $ontIndex): ?CommandResult
    {
        $command = "show interface gpon $ponInterface onu $ontIndex";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'Serial Number')) {
                throw new \Exception($response);
            }

            $onuInfo = [];

            $lines = explode("\n", $response);
            foreach ($lines as $line) {
                if (preg_match('/^\s*([^:]+?)\s*:\s*(.+?)\s*$/', $line, $matches)) {
                    $key = str_replace([' ', '-'], ['', ''], trim($matches[1]));
                    $value = trim($matches[2]);

                    if ($key === 'ID') {
                        $value = (int) $value;
                    } elseif (in_array($key, ['RxOpticalPower[dBm]', 'TxOpticalPower[dBm]'])) {
                        $value = (float) $value;
                    }

                    $onuInfo[$key] = $value;
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $onuInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Shows information about ONUs matching a specific serial number
     */
    public static function showInterfaceGponOnuInclude(string $serial): ?CommandResult
    {
        $command = "show interface gpon onu | include $serial";

        try {
            $response = self::$telnetConn->exec($command);

            if (trim($response) === $command) {
                throw new \Exception($response);
            }

            $onuInfo = [];
            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                if (preg_match('/^\s*(\S+)\s+(\d+)\s+(\S+)\s+(\S+)\s+(\S+(?:\s+\S+)*)\s+(\S+(?:\s+\S+)*)\s*$/', $line, $matches)) {
                    $onuInfo[] = [
                        'interface' => $matches[1],
                        'onuId' => (int) $matches[2],
                        'serialNumber' => $matches[3],
                        'operState' => $matches[4],
                        'softwareDownloadState' => trim($matches[5]),
                        'name' => trim($matches[6]),
                    ];
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $onuInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? '',
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Shows all ONUs configured on a specific GPON interface
     */
    public static function showInterfaceGpon(string $ponInterface): ?CommandResult
    {
        $command = "show interface gpon $ponInterface onu | nomore";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'Serial Number') && ! str_contains($response, 'No entries found')) {
                throw new \Exception($response);
            }

            if (str_contains($response, 'No entries found')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                ]);
            }

            $onuInfo = [];

            $lines = explode("\n", $response);
            $lines = array_slice($lines, 3);

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                if (preg_match('/^\s*(\S+)\s+(\d+)\s+(\S+)\s+(\S+)\s+(\S+(?:\s+\S+)*)\s+(\S+(?:\s+\S+)*)\s*$/', $line, $matches)) {
                    $onuInfo[] = [
                        'interface' => $matches[1],
                        'onuId' => (int) $matches[2],
                        'serialNumber' => $matches[3],
                        'operState' => $matches[4],
                        'softwareDownloadState' => trim($matches[5]),
                        'name' => trim($matches[6]),
                    ];
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $onuInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Shows service port configurations
     */
    public static function showRunningConfigServicePort(): ?CommandResult
    {
        $command = 'show running-config service-port | nomore';

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'No entries found')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                ]);
            }

            $servicePortInfo = [];
            $currentServicePort = null;

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line === '!') {
                    continue;
                }

                if (preg_match('/^service-port\s+(\d+)/', $line, $matches)) {
                    $currentServicePort = (int) $matches[1];
                } elseif ($currentServicePort && preg_match('/gpon\s+(\S+)\s+onu\s+(\d+)\s+gem\s+(\d+)(?:\s+match\s+vlan\s+vlan-id\s+(\d+))?(?:\s+action\s+vlan\s+replace\s+vlan-id\s+(\d+))?(?:\s+description\s+(.+))?/', $line, $matches)) {
                    $servicePortInfo[] = [
                        'servicePortId' => $currentServicePort,
                        'ponInterface' => $matches[1],
                        'onuIndex' => (int) $matches[2],
                        'gem' => (int) $matches[3],
                        'matchVlan' => isset($matches[4]) ? (int) $matches[4] : null,
                        'replaceVlan' => isset($matches[5]) ? (int) $matches[5] : null,
                        'description' => isset($matches[6]) ? trim($matches[6]) : null,
                    ];
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $servicePortInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? '',
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Shows service port configurations for a specific GPON interface
     */
    public static function showRunningConfigServicePortSelectGpon(string $ponInterface): ?CommandResult
    {
        $command = "show running-config service-port | select gpon $ponInterface | nomore";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, "gpon $ponInterface") && ! str_contains($response, 'No entries found')) {
                throw new \Exception($response);
            }

            if (str_contains($response, 'No entries found')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                ]);
            }

            $servicePortInfo = [];
            $currentServicePort = null;

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line === '!') {
                    continue;
                }

                if (preg_match('/^service-port\s+(\d+)/', $line, $matches)) {
                    $currentServicePort = (int) $matches[1];
                } elseif ($currentServicePort && preg_match('/gpon\s+(\S+)\s+onu\s+(\d+)\s+gem\s+(\d+)(?:\s+match\s+vlan\s+vlan-id\s+(\d+))?(?:\s+action\s+vlan\s+replace\s+vlan-id\s+(\d+))?(?:\s+description\s+(.+))?/', $line, $matches)) {
                    $servicePortInfo[] = [
                        'servicePortId' => $currentServicePort,
                        'ponInterface' => $matches[1],
                        'onuIndex' => (int) $matches[2],
                        'gem' => (int) $matches[3],
                        'matchVlan' => isset($matches[4]) ? (int) $matches[4] : null,
                        'replaceVlan' => isset($matches[5]) ? (int) $matches[5] : null,
                        'description' => isset($matches[6]) ? trim($matches[6]) : null,
                    ];
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $servicePortInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? '',
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Shows service port configurations for a specific ONU on a GPON interface
     */
    public static function showRunningConfigServicePortSelectGponContextMatch(string $ponInterface, string $ontIndex): ?CommandResult
    {
        $command = "show running-config service-port | select gpon $ponInterface | context-match \"onu $ontIndex\"";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, "gpon $ponInterface onu $ontIndex") && ! str_contains($response, 'No entries found')) {
                throw new \Exception($response);
            }

            if (str_contains($response, 'No entries found')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                ]);
            }

            $servicePortInfo = [];
            $currentServicePort = null;

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if (preg_match('/^service-port\s+(\d+)/', $line, $matches)) {
                    $currentServicePort = (int) $matches[1];
                } elseif ($currentServicePort && preg_match('/gpon\s+(\S+)\s+onu\s+(\d+)\s+gem\s+(\d+)(?:\s+match\s+vlan\s+vlan-id\s+(\d+))?(?:\s+action\s+vlan\s+replace\s+vlan-id\s+(\d+))?(?:\s+description\s+(.+))?/', $line, $matches)) {
                    $servicePortInfo[] = [
                        'servicePortId' => $currentServicePort,
                        'ponInterface' => $matches[1],
                        'onuIndex' => (int) $matches[2],
                        'gem' => (int) $matches[3],
                        'matchVlan' => isset($matches[4]) ? (int) $matches[4] : null,
                        'replaceVlan' => isset($matches[5]) ? (int) $matches[5] : null,
                        'description' => isset($matches[6]) ? trim($matches[6]) : null,
                    ];
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $servicePortInfo,
            ]);
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? '',
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets the name for an ONU
     */
    public static function name(string $name): ?CommandResult
    {
        $response = null;
        $command = "name $name";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command) || str_contains($response, 'invalid value')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets the serial number for an ONU
     */
    public static function serialNumber(string $serial): ?CommandResult
    {
        $response = null;
        $command = "serial-number $serial";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command) || str_contains($response, 'invalid value')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Configures the SNMP profile for an ONU
     */
    public static function snmpProfile(string $profile): ?CommandResult
    {
        $response = null;
        $command = "snmp profile $profile";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command) || str_contains($response, 'invalid value')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Enables real-time SNMP monitoring
     */
    public static function snmpRealTime(): ?CommandResult
    {
        $response = null;
        $command = 'snmp real-time';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command)) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets the line profile for an ONU
     */
    public static function lineProfile(string $profile): ?CommandResult
    {
        $response = null;
        $command = "line-profile $profile";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command)) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Exit to top level - Telnet
     */
    public static function top(): ?CommandResult
    {
        $response = null;
        $command = 'top';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command)) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Exit to default level - Telnet
     */
    public static function end(): ?CommandResult
    {
        $response = null;
        $command = 'end';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command)) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Enters configure terminal mode - Telnet
     */
    public static function config(): ?CommandResult
    {
        $response = null;
        $command = 'config';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'Entering configuration mode terminal')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Enters interface gpon terminal mode - Telnet
     */
    public static function interfaceGpon(string $ponInterface): ?CommandResult
    {
        $response = null;
        $command = "interface gpon $ponInterface";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command)) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Enters/creates onu terminal mode - Telnet
     */
    public static function onu(int $index): ?CommandResult
    {
        $response = null;
        $command = "onu $index";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command)) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Commits the current configuration changes
     */
    public static function commit(): ?CommandResult
    {
        $response = null;
        $command = 'commit';

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'Aborted') || str_contains($response, 'Invalid')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Configures a virtual Ethernet interface point for an ONU
     */
    public static function veip(int $port): ?CommandResult
    {
        $response = null;
        $command = "veip $port";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command) || str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets a service port
     */
    public static function servicePort(int $port, string $ponInterface, int $ontIndex, int $vlan, string $description): ?CommandResult
    {
        $response = null;
        $command = "service-port $port gpon $ponInterface onu $ontIndex gem 1 match vlan vlan-id $vlan action vlan replace vlan-id $vlan description $description";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, $command) || str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Enters/creates ethernet terminal mode - Telnet
     */
    public static function ethernet(int $port): ?CommandResult
    {
        $response = null;
        $command = "ethernet $port";

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets ethernet port negotiation
     */
    public static function negotiation(): ?CommandResult
    {
        $response = null;
        $command = 'negotiation';

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets ethernet port to not shutdown
     */
    public static function noShutdown(): ?CommandResult
    {
        $response = null;
        $command = 'no shutdown';

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Sets ethernet port native VLAN ID
     */
    public static function nativeVlanVlanId(int $vlan): ?CommandResult
    {
        $response = null;
        $command = "native vlan vlan-id $vlan";

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Removes an ONU configuration from a GPON interface
     */
    public static function noOnu(int $index): ?CommandResult
    {
        $response = null;
        $command = "no onu $index";

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }

    /**
     * Removes a service port configuration
     */
    public static function noServicePort(int $port): ?CommandResult
    {
        $response = null;
        $command = "no service-port $port";

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'syntax error')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
            ]);
        } catch (\Exception $e) {
            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }
    }
}
