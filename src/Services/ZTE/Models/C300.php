<?php

namespace PauloHortelan\Onmt\Services\ZTE\Models;

use Illuminate\Support\Carbon;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\GemportConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServiceConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServicePortConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\SwitchportBindConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

class C300 extends ZTEService
{
    /**
     * Executes the given command - Telnet
     */
    public static function executeCommandTelnet(string $command): ?CommandResult
    {
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Disables terminal length - Telnet
     */
    public static function terminalLength0(): ?CommandResult
    {
        $response = null;
        $command = 'terminal length 0';
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ], true);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Enters configure terminal mode - Telnet
     */
    public static function configureTerminal(): ?CommandResult
    {
        $response = null;
        $command = 'configure terminal';
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'Enter configuration commands')) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Exits - Telnet
     */
    public static function exit(): ?CommandResult
    {
        $response = null;
        $command = 'exit';
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * End - Telnet
     */
    public static function end(): ?CommandResult
    {
        $response = null;
        $command = 'end';
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Yes - Telnet
     */
    public static function yes(): ?CommandResult
    {
        $response = null;
        $command = 'yes';
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Show current terminal/interface info - Telnet
     */
    public static function showThis(): ?CommandResult
    {
        $response = null;
        $command = 'show this';
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [$response],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Enters interface gpon-olt mode - Telnet
     */
    public static function interfaceGponOlt(string $ponInterface): ?CommandResult
    {
        $command = "interface gpon-olt_$ponInterface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Enters interface gpon-onu mode - Telnet
     */
    public static function interfaceGponOnu(string $interface): ?CommandResult
    {
        $command = "interface gpon-onu_$interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Enters pon-onu-mng gpon-onu mode - Telnet
     */
    public static function ponOnuMng(string $interface): ?CommandResult
    {
        $command = "pon-onu-mng gpon-onu_$interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get ONT optical power - Telnet
     */
    public static function showPonPowerAttenuation(string $interface): ?CommandResult
    {
        $command = "show pon power attenuation gpon-onu_$interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

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
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }

        return self::createCommandResult([
            'success' => true,
            'command' => $command,
            'response' => $response,
            'error' => null,
            'result' => $ontOpticalPower,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Get ONT interface - Telnet
     */
    public static function showGponOnuBySn(string $serial): ?CommandResult
    {
        $command = "show gpon onu by sn $serial";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'Search result')) {
                throw new \Exception($response);
            }

            if (preg_match('/gpon-onu_(.*)/', $response, $match)) {
                $ontInterface = trim($match[1]);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontInterface ?? null,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get ONT detail info - Telnet
     */
    public static function showGponOnuDetailInfo(string $interface): ?CommandResult
    {
        $command = "show gpon onu detail-info gpon-onu_$interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

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

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontDetailInfo,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get ONTs by pon interface - Telnet
     */
    public static function showGponOnuState(string $ponInterface): ?CommandResult
    {
        $command = "show gpon onu state gpon-olt_$ponInterface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (str_contains($response, 'No related information to show.')) {
                return self::createCommandResult([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
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
                    'created_at' => $createdAt,
                    'finished_at' => $finishedAt,
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

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontsList,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);

        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get unconfigured ONTs - Telnet
     */
    public static function showGponOnuUncfg(): ?CommandResult
    {
        $command = 'show gpon onu uncfg';
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'OnuIndex')) {
                throw new \Exception($response);
            }

            $onuInfo = [];

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/^gpon-onu_\d+\/\d+\/\d+:\d+\s+[A-Z0-9]+(?:\s+[a-zA-Z0-9]+)?$/', $line, $matches)) {
                    $parts = preg_split('/\s+/', $line);

                    if (count($parts) >= 3) {
                        $onuInfo[] = [
                            'onu-index' => $parts[0],
                            'serial-number' => $parts[1],
                            'state' => $parts[2],
                        ];
                    }
                }
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $onuInfo,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get interface-onu's running config - Telnet
     */
    public static function showRunningConfigInterfaceGponOnu($interface): ?CommandResult
    {
        $command = "show running-config interface gpon-onu_$interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'interface gpon-onu_')) {
                throw new \Exception($response);
            }

            $result = [];
            $lines = explode("\n", $response);

            $isInterfaceBlock = false;

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/^interface gpon-onu_\d+\/\d+\/\d+:\d+$/', $line)) {
                    $isInterfaceBlock = true;

                    continue;
                }

                if ($line === '!' || $line === 'end') {
                    $isInterfaceBlock = false;

                    continue;
                }

                if ($isInterfaceBlock && ! empty($line)) {
                    $result[] = $line;
                }
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $result,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get ONTs running config - Telnet
     */
    public static function showOnuRunningConfigGponOnu($interface): ?CommandResult
    {
        $command = "show onu running config gpon-onu_$interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'pon-onu-mng gpon-onu_')) {
                throw new \Exception($response);
            }

            $result = [];
            $lines = explode("\n", $response);

            $isInterfaceBlock = false;

            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/^pon-onu-mng gpon-onu_\d+\/\d+\/\d+:\d+$/', $line)) {
                    $isInterfaceBlock = true;

                    continue;
                }

                if ($line === '!' || $line === 'end') {
                    $isInterfaceBlock = false;

                    continue;
                }

                if ($isInterfaceBlock && ! empty($line)) {
                    $result[] = $line;
                }
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $result,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Register ONT - Telnet
     */
    public static function onuTypeSn(int $ontIndex, string $profile, string $serial): ?CommandResult
    {
        $command = "onu $ontIndex type $profile sn $serial";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'Successful')) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Removes ONT - Telnet
     */
    public static function noOnu(int $ontIndex): ?CommandResult
    {
        $command = "no onu $ontIndex";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'Successful')) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Sets ONT name - Telnet
     */
    public static function name(string $name): ?CommandResult
    {
        $command = "name $name";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Sets ONT description - Telnet
     */
    public static function description(string $description): ?CommandResult
    {
        $command = "description $description";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Sets ONT description - Telnet
     */
    public static function tcont(int $tcontId, string $profileName): ?CommandResult
    {
        $command = "tcont $tcontId profile $profileName";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures gemport - Telnet
     */
    public static function gemport(GemportConfig $gemportConfig): ?CommandResult
    {
        $command = $gemportConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures service-port - Telnet
     */
    public static function servicePort(ServicePortConfig $servicePortConfig): ?CommandResult
    {
        $command = $servicePortConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures service - Telnet
     */
    public static function service(ServiceConfig $serviceConfig): ?CommandResult
    {
        $command = $serviceConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures switchport-bind - Telnet
     */
    public static function switchportBind(SwitchportBindConfig $switchportBindConfig): ?CommandResult
    {
        $command = $switchportBindConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures vlan port - Telnet
     */
    public static function vlanPort(VlanPortConfig $vlanPortConfig): ?CommandResult
    {
        $command = $vlanPortConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures flow mode - Telnet
     */
    public static function flowMode(FlowModeConfig $flowModeConfig): ?CommandResult
    {
        $command = $flowModeConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures flow - Telnet
     */
    public static function flow(FlowConfig $flowConfig): ?CommandResult
    {
        $command = $flowConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures vlan-filter-mode - Telnet
     */
    public static function vlanFilterMode(VlanFilterModeConfig $vlanFilterModeConfig): ?CommandResult
    {
        $command = $vlanFilterModeConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configures vlan-filter - Telnet
     */
    public static function vlanFilter(VlanFilterConfig $vlanFilterConfig): ?CommandResult
    {
        $command = $vlanFilterConfig->buildCommand();
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Reboot (needs to be confirm [yes/no]) - Telnet
     */
    public static function reboot(): ?CommandResult
    {
        $command = 'reboot';
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            self::$telnetConn->changePromptRegex(':');

            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! empty($response)) {
                throw new \Exception($response);
            }

            self::$telnetConn->resetPromptRegex();

            return self::createCommandResult([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();
            self::$telnetConn->resetPromptRegex();

            return self::createCommandResult([
                'success' => false,
                'command' => $command,
                'response' => $response,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }
}
