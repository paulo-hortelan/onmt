<?php

namespace PauloHortelan\Onmt\Services\Datacom\Models;

use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Services\Datacom\DatacomService;

class DM4612 extends DatacomService
{
    public static function showInterfaceGponDiscoveredOnus(): ?CommandResult
    {
        $command = 'show interface gpon discovered-onus';

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'Serial Number')) {
                throw new \Exception($response);
            }

            $response = 'show interface gpon discovered-onus';

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

    public static function showInterfaceGponOnu(string $interface): ?CommandResult
    {
        $parts = explode('/', $interface);
        $ponInterface = implode('/', array_slice($parts, 0, 3));
        $ontIndex = end($parts);

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

    public static function showInterfaceGpon(string $ponInterface): ?CommandResult
    {
        $command = "show interface gpon $ponInterface onu";

        try {
            $response = self::$telnetConn->exec($command);

            if (! str_contains($response, 'Serial Number')) {
                throw new \Exception($response);
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
}
