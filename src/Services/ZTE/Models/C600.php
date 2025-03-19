<?php

namespace PauloHortelan\Onmt\Services\ZTE\Models;

use PauloHortelan\Onmt\DTOs\ZTE\C300\GemportConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServiceConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServicePortConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;

class C600 extends C300
{
    /**
     * Get unconfigured ONTs - Telnet
     */
    public static function showGponOnuUncfg(): ?CommandResult
    {
        $command = 'show pon onu uncfg';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'OltIndex')) {
                throw new \Exception($response);
            }

            $onuInfo = [];

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/^gpon_olt-\d+\/\d+\/\d+\s+\S+\s+\S+$/', $line, $matches)) {
                    $parts = preg_split('/\s+/', $line);

                    if (count($parts) >= 3) {
                        $onuInfo[] = [
                            'onu-index' => $parts[0],
                            'model' => $parts[1],
                            'serial-number' => $parts[2],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'response' => $response,
            'error' => null,
            'result' => $onuInfo,
        ]);
    }

    /**
     * Get ONTs by pon interface - Telnet
     */
    public static function showGponOnuState(string $ponInterface): ?CommandResult
    {
        $command = "show gpon onu state gpon_olt-$ponInterface";

        try {
            $response = self::$telnetConn->exec($command);

            if (str_contains($response, 'No related information to show.')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'error' => null,
                    'result' => [
                        [
                            'onu-index' => 0,
                            'admin-state' => null,
                            'omcc-state' => null,
                            'phase-state' => null,
                            'channel' => null,
                        ],
                    ],
                ]);
            }

            if (! str_contains($response, 'OnuIndex')) {
                throw new \Exception($response);
            }

            $ontsList = [];
            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/^\d+\/\d+\/\d+:\d+/', $line)) {
                    $parts = preg_split('/\s+/', $line);

                    if (count($parts) >= 5) {
                        $ontsList[] = [
                            'onu-index' => $parts[0],
                            'admin-state' => $parts[1],
                            'omcc-state' => $parts[2],
                            'phase-state' => $parts[3],
                            'channel' => $parts[4],
                        ];
                    }
                }
            }

        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'response' => $response,
            'error' => null,
            'result' => $ontsList,
        ]);
    }

    /**
     * Enters interface gpon-olt mode - Telnet
     */
    public static function interfaceGponOlt(string $ponInterface): ?CommandResult
    {
        $command = "interface gpon_olt-$ponInterface";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Enters interface gpon-onu mode - Telnet
     */
    public static function interfaceGponOnu(string $interface): ?CommandResult
    {
        $command = "interface gpon_onu-$interface";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Enters interface vport mode - Telnet
     */
    public static function interfaceVport(string $interface, int $vport): ?CommandResult
    {
        $parts = explode(':', $interface);
        $ponInterface = $parts[0];
        $ontIndex = $parts[1];

        $command = "interface vport-$ponInterface.$ontIndex:$vport";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Enters pon-onu-mng gpon-onu mode - Telnet
     */
    public static function ponOnuMng(string $interface): ?CommandResult
    {
        $command = "pon-onu-mng gpon_onu-$interface";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Get ONT optical power - Telnet
     */
    public static function showPonPowerAttenuation(string $interface): ?CommandResult
    {
        $command = "show pon power attenuation gpon_onu-$interface";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'OLT')) {
                throw new \Exception($response);
            }

            $ontOpticalPower = [
                'up-olt-rx' => null,
                'up-onu-tx' => null,
                'up-attenuation' => null,
                'down-olt-tx' => null,
                'down-onu-rx' => null,
                'down-attenuation' => null,
            ];

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match(
                    '/^up\s+Rx\s*:\s*(-?\d+\.\d+)\(dbm\)\s+Tx\s*:\s*(-?\d+\.\d+)\(dbm\)\s+(\d+\.\d+)\(dB\)$/i',
                    $line,
                    $matches
                )) {
                    $ontOpticalPower['up-olt-rx'] = (float) $matches[1];
                    $ontOpticalPower['up-onu-tx'] = (float) $matches[2];
                    $ontOpticalPower['up-attenuation'] = (float) $matches[3];
                }

                if (preg_match(
                    '/^down\s+Tx\s*:\s*(-?\d+\.\d+)\(dbm\)\s+Rx\s*:\s*(-?\d+\.\d+)\(dbm\)\s+(\d+\.\d+)\(dB\)$/i',
                    $line,
                    $matches
                )) {
                    $ontOpticalPower['down-olt-tx'] = (float) $matches[1];
                    $ontOpticalPower['down-onu-rx'] = (float) $matches[2];
                    $ontOpticalPower['down-attenuation'] = (float) $matches[3];
                }
            }
        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'response' => $response,
            'error' => null,
            'result' => $ontOpticalPower,
        ]);
    }

    /**
     * Get ONT detail info - Telnet
     */
    public static function showGponOnuDetailInfo(string $interface): ?CommandResult
    {
        $command = "show gpon onu detail-info gpon_onu-$interface";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'ONU interface')) {
                throw new \Exception($response);
            }

            $ontDetailInfo = [
                'alarm-history' => [],
            ];

            $lines = explode("\n", $response);
            $inHistorySection = false;

            foreach ($lines as $line) {
                $line = trim($line);

                if (! $inHistorySection && preg_match('/^(.*?):\s+(.*)$/', $line, $matches)) {
                    $key = strtolower(str_replace(' ', '-', trim($matches[1])));
                    $value = trim($matches[2]);
                    $ontDetailInfo[$key] = $value ?? null;
                }

                if (str_contains($line, 'Authpass Time')) {
                    $inHistorySection = true;

                    continue;
                }

                if ($inHistorySection && preg_match('/^\s*\d+\s+([0-9\-]+\s[0-9:]+)\s{2,}([0-9\-]+\s[0-9:]+|0000-00-00 00:00:00)\s*(.*)$/', $line, $matches)) {
                    $ontDetailInfo['alarm-history'][] = [
                        'authpass-time' => $matches[1],
                        'offline-time' => $matches[2],
                        'cause' => empty($matches[3]) ? null : trim($matches[3]),
                    ];
                }

            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontDetailInfo,
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
     * Register ONT - Telnet
     */
    public static function onuTypeSn(int $ontIndex, string $profile, string $serial): ?CommandResult
    {
        $command = "onu $ontIndex type $profile sn $serial";

        try {
            $response = self::$telnetConn->exec($command);

            if ($command !== $response) {
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
     * Removes ONT - Telnet
     */
    public static function noOnu(int $ontIndex): ?CommandResult
    {
        $command = "no onu $ontIndex";

        try {
            $response = self::$telnetConn->exec($command);

            if ($command !== $response) {
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
     * Get ONT interface - Telnet
     */
    public static function showGponOnuBySn(string $serial): ?CommandResult
    {
        $command = "show gpon onu by sn $serial";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'SearchResult')) {
                throw new \Exception($response);
            }

            if (preg_match('/gpon_onu-(.*)/', $response, $match)) {
                $ontInterface = trim($match[1]);
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontInterface ?? null,
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
     * Get interface-onu's running config - Telnet
     */
    public static function showRunningConfigInterfaceGponOnu($interface): ?CommandResult
    {
        $command = "show running-config-interface gpon_onu-$interface";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'interface gpon_onu-')) {
                throw new \Exception($response);
            }

            $result = [];
            $lines = explode("\n", $response);

            $isInterfaceOnuBlock = false;
            $isPonOnuMngBlock = false;
            $currentBlock = '';
            $currentParams = [];

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/^interface gpon_onu-\d+\/\d+\/\d+:\d+$/', $line)) {
                    if ($currentBlock) {
                        $result[$currentBlock] = $currentParams;
                    }

                    $isInterfaceOnuBlock = true;
                    $currentBlock = 'interface-onu';
                    $currentParams = [];

                    $isPonOnuMngBlock = false;

                    continue;
                }

                if (preg_match('/^pon-onu-mng gpon_onu-\d+\/\d+\/\d+:\d+$/', $line)) {
                    if ($currentBlock) {
                        $result[$currentBlock] = $currentParams;
                    }

                    $isPonOnuMngBlock = true;
                    $currentBlock = 'pon-onu-mng';
                    $currentParams = [];

                    $isInterfaceOnuBlock = false;

                    continue;
                }

                if ($line === '$' || $line === '!<x/>pon') {
                    if ($currentBlock) {
                        $result[$currentBlock] = $currentParams;
                        $currentBlock = '';
                        $currentParams = [];
                    }

                    continue;
                }

                if ($isInterfaceOnuBlock || $isPonOnuMngBlock || ! empty($currentBlock)) {
                    if (! empty($line)) {
                        $currentParams[] = $line;
                    }
                }
            }

        } catch (\Exception $e) {
            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
            ]);
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'response' => $response,
            'error' => null,
            'result' => $result,
        ]);
    }

    /**
     * Sets ONT name - Telnet
     */
    public static function name(string $name): ?CommandResult
    {
        $command = "name $name";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Sets ONT description - Telnet
     */
    public static function description(string $description): ?CommandResult
    {
        $command = "description $description";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Sets ONT description - Telnet
     */
    public static function tcont(int $tcontId, string $profileName): ?CommandResult
    {
        $command = "tcont $tcontId profile $profileName";

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Configures gemport - Telnet
     */
    public static function gemport(GemportConfig $gemportConfig): ?CommandResult
    {
        $command = $gemportConfig->buildCommand();

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Configures service - Telnet
     */
    public static function service(ServiceConfig $serviceConfig): ?CommandResult
    {
        $command = $serviceConfig->buildCommand();

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Configures vlan port - Telnet
     */
    public static function vlanPort(VlanPortConfig $vlanPortConfig): ?CommandResult
    {
        $command = $vlanPortConfig->buildCommand();

        try {
            $response = self::$telnetConn->exec($command);

            if ($response !== $command) {
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
     * Configures service-port - Telnet
     */
    public static function servicePort(ServicePortConfig $servicePortConfig): ?CommandResult
    {
        $command = $servicePortConfig->buildCommand();

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
}
