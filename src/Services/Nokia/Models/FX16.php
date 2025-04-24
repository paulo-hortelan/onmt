<?php

namespace PauloHortelan\Onmt\Services\Nokia\Models;

use Exception;
use Illuminate\Support\Carbon;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\HguTr069SparamConfig;
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            return CommandResult::make([
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

            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

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

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Executes the given command - TL1
     */
    public static function executeCommandTL1(string $command): ?CommandResult
    {
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Configure ONT admin state - Telnet
     */
    public static function adminEquipmentOntInterfaceRebootWithActiveImage(string $interface): ?CommandResult
    {
        $command = "admin equipment ont interface $interface reboot with-active-image";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (str_contains($response, 'invalid token')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Get ONTs info by PON interface - Telnet
     */
    public static function showEquipmentOntStatusPon(string $ponInterface): ?CommandResult
    {
        $command = "show equipment ont status pon $ponInterface detail";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'pon table')) {
                throw new \Exception($response);
            }

            $ontsData = [];
            $ontsBlocks = explode("\n--------------------------------------------------------------------------------\n", $response);

            foreach ($ontsBlocks as $ontBlock) {

                if (empty(trim($ontBlock))) {
                    continue;
                }

                $ontAttributes = [
                    'pon-interface' => null,
                    'interface' => null,
                    'sernum' => null,
                    'admin-status' => null,
                    'oper-status' => null,
                    'olt-rx-sig-level' => null,
                    'ont-olt-distance' => null,
                    'desc1' => null,
                    'desc2' => null,
                    'hostname' => null,
                ];

                $lines = explode("\n", $ontBlock);
                foreach ($lines as $line) {
                    $line = trim($line);

                    $patterns = [
                        'pon-interface' => '/pon\s*:\s*(\S+)/',
                        'interface' => '/ont\s*:\s*(\S+)/',
                        'sernum' => '/sernum\s*:\s*(\S+)/',
                        'admin-status' => '/admin-status\s*:\s*(\S+)/',
                        'oper-status' => '/oper-status\s*:\s*(\S+)/',
                        'olt-rx-sig-level' => '/olt-rx-sig-level\(dbm\)\s*:\s*([\S-]+)/',
                        'ont-olt-distance' => '/ont-olt-distance\(km\)\s*:\s*([\S-]+)/',
                        'desc1' => '/desc1\s*:\s*(.+)/',
                        'desc2' => '/desc2\s*:\s*(.+)/',
                        'hostname' => '/hostname\s*:\s*(\S+)/',
                    ];

                    foreach ($patterns as $key => $pattern) {
                        if (preg_match($pattern, $line, $matches)) {
                            $value = trim($matches[1]);

                            if (empty($value)) {
                                $ontAttributes[$key] = null;
                            } elseif (in_array($key, ['olt-rx-sig-level', 'ont-olt-distance'])) {
                                $ontAttributes[$key] = (float) $value;
                            } else {
                                $ontAttributes[$key] = $value;
                            }
                        }
                    }
                }

                if (! empty($ontAttributes['pon-interface'])) {
                    $ontsData[] = $ontAttributes;
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontsData,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }

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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (str_contains($response, 'invalid token')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Remove ONT interface - Telnet
     */
    public static function configureEquipmentOntNoInterface(string $interface): ?CommandResult
    {
        $command = "configure equipment ont no interface $interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (str_contains($response, 'invalid token')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Returns the ONT equipment optics - Telnet
     */
    public static function showEquipmentOntOptics(string $interface): ?CommandResult
    {
        $command = "show equipment ont optics $interface detail";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'tx-signal-level')) {
                throw new \Exception($response);
            }

            $ontDetails = [
                'tx-signal-level' => null,
                'ont-voltage' => null,
                'olt-rx-sig-level' => null,
                'rx-signal-level' => null,
                'ont-temperature' => null,
                'laser-bias-curr' => null,
            ];

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                foreach ($ontDetails as $key => $value) {
                    if (preg_match('/'.preg_quote($key, '/').'\s*:\s*([^\s:]+)/i', $line, $matches)) {
                        $value = trim($matches[1]);

                        $ontDetails[$key] = ! empty($value) ? (float) $value : null;
                    }
                }
            }

            extract($ontDetails);

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $ontDetails,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'index table')) {
                throw new \Exception($response);
            }

            if (! str_contains($response, 'ont-idx')) {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                    'created_at' => $createdAt,
                    'finished_at' => $finishedAt,
                ]);
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
                'response' => $response,
                'error' => null,
                'result' => $ontInterface,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Returns the ONT interface details - Telnet
     */
    public static function showEquipmentOntInterface(string $interface): ?CommandResult
    {
        $command = "show equipment ont interface $interface detail";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'ont-idx')) {
                throw new \Exception($response);
            }

            $interfaceDetails = [
                'sw-ver-act' => null,
                'vendor-id' => null,
                'equip-id' => null,
                'actual-num-slots' => null,
                'num-tconts' => null,
                'num-prio-queues' => null,
                'auto-sw-download-ver' => null,
                'yp-serial-no' => null,
                'oper-spec-ver' => null,
                'act-txpower-ctrl' => null,
                'cfgfile1-ver-act' => null,
                'cfgfile2-ver-act' => null,
                'actual-us-rate' => null,
                'template-name' => null,
                'auto-prov-status' => null,
                'eqpt-ver-num' => null,
                'sw-ver-psv' => null,
                'version-number' => null,
                'num-trf-sched' => null,
                'auto-sw-planned-ver' => null,
                'sernum' => null,
                'act-ont-type' => null,
                'sn-bundle-status' => null,
                'cfgfile1-ver-psv' => null,
                'cfgfile2-ver-psv' => null,
            ];

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                foreach ($interfaceDetails as $key => $value) {
                    if (preg_match('/'.preg_quote($key, '/').'\s*:\s*([^\s:]+)/i', $line, $matches)) {
                        $value = trim($matches[1]);

                        if (empty($value)) {
                            $interfaceDetails[$key] = null;
                        } elseif (in_array($key, ['num-tconts', 'num-prio-queues', 'actual-num-slots', 'num-trf-sched'])) {
                            $interfaceDetails[$key] = (int) $value;
                        } else {
                            $interfaceDetails[$key] = $value;
                        }
                    }
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $interfaceDetails,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Returns the ONT software download details - Telnet
     */
    public static function showEquipmentOntSwDownload(string $interface): ?CommandResult
    {
        $command = "show equipment ont sw-download $interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'ont-idx')) {
                throw new \Exception($response);
            }

            $swDownloadDetails = [
                'inactive' => null,
                'download-notok' => null,
                'ntlt-inprogress' => null,
                'ontflash-inprogress' => null,
                'ntlt-failure' => null,
                'ontflash-failure' => null,
                'download-file-notfound' => null,
                'sw-version-mismatch' => null,
                'sw-delayactivate' => null,
                'planned' => null,
                'planned-notok' => null,
                'download-inprogress' => null,
                'omci-inprogress' => null,
                'ontswact-inprogress' => null,
                'omci-failure' => null,
                'ontswact-failure' => null,
                'no-matching-software' => null,
                'sw-download-failure' => null,
                'sw-download-pending' => null,
            ];

            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                $line = trim($line);

                foreach ($swDownloadDetails as $key => $value) {
                    if (preg_match('/'.preg_quote($key, '/').'\s*:\s*([^\s:]+)/i', $line, $matches)) {
                        $value = trim($matches[1]);

                        if (empty($value)) {
                            $swDownloadDetails[$key] = null;
                        } else {
                            $swDownloadDetails[$key] = $value;
                        }
                    }
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $swDownloadDetails,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Returns the ONT alarm - Telnet
     */
    public static function showAlarmQueryOntPloam(string $interface): ?CommandResult
    {
        $command = "show alarm query ont-ploam all severity all servaff all ont $interface";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            $alarmData = [];

            if (strpos($response, 'ont-ploam table') !== false) {
                if (strpos($response, '--------------------------------------------------------------------------------') !== false) {
                    $blocks = explode('--------------------------------------------------------------------------------', $response);

                    foreach ($blocks as $block) {
                        if (empty(trim($block)) || strpos($block, 'ont-ploam') === false) {
                            continue;
                        }

                        $alarm = [
                            'aidinfo' => null,
                            'type' => null,
                            'name' => null,
                            'severity' => null,
                            'servaff' => null,
                            'time' => null,
                            'descript' => null,
                        ];

                        $fields = [
                            'aidinfo' => '/aidinfo\s*:\s*"?([^"\n]+)"?/i',
                            'type' => '/type\s*:\s*([^\n]+)/i',
                            'name' => '/name\s*:\s*([^\n]+)/i',
                            'severity' => '/severity\s*:\s*([^\n]+)/i',
                            'servaff' => '/servaff\s*:\s*([^\n]+)/i',
                            'time' => '/time\s*:\s*([^\n]+)/i',
                            'descript' => '/descript\s*:\s*"?([^"\n]+)"?/i',
                        ];

                        foreach ($fields as $key => $pattern) {
                            if (preg_match($pattern, $block, $matches)) {
                                $alarm[$key] = trim($matches[1]);
                            }
                        }

                        if ($alarm['name'] !== null) {
                            $alarmData[] = $alarm;
                        }
                    }
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $alarmData,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Returns the ONT port details - Telnet
     */
    public static function showInterfacePortOnt(string $interface): ?CommandResult
    {
        $command = "show interface port ont:$interface detail";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

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

            $portDetail = [
                'oprStatus' => $oprStatus ?? null,
                'adminStatus' => $adminStatus ?? null,
                'lastChgOprStat' => $lastChgOprStat ?? null,
            ];

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $portDetail,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Returns the ONTs unprovisioned - Telnet
     */
    public static function showPonUnprovisionOnu(): ?CommandResult
    {
        $unregData = [];
        $command = 'show pon unprovision-onu';
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$telnetConn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'unprovision-onu table')) {
                throw new \Exception($response);
            }

            $tableSection = '';
            if (preg_match('/unprovision-onu table.*?unprovision-onu count/s', $response, $matches)) {
                $tableSection = $matches[0];
            } else {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                    'created_at' => $createdAt,
                    'finished_at' => $finishedAt,
                ]);
            }

            $headerLine = '';
            if (preg_match('/alarm-idx\|.*?\|sernum/i', $tableSection, $headerMatch)) {
                $headerLine = $headerMatch[0];
            } else {
                return CommandResult::create([
                    'success' => true,
                    'command' => $command,
                    'response' => $response,
                    'error' => null,
                    'result' => [],
                    'created_at' => $createdAt,
                    'finished_at' => $finishedAt,
                ]);
            }

            $alarmIdxPos = strpos($headerLine, 'alarm-idx');
            $gponIndexPos = strpos($headerLine, 'gpon-index');
            $sernumPos = strpos($headerLine, 'sernum');

            $lines = preg_split('/\r\n|\n|\r/', $tableSection);
            $dataStarted = false;

            foreach ($lines as $line) {
                $trimmedLine = trim($line);

                if (empty($trimmedLine)) {
                    continue;
                }

                if (preg_match('/alarm-idx|unprovision-onu count/i', $trimmedLine)) {
                    $dataStarted = true;

                    continue;
                }

                if (preg_match('/^[-+]+$/', $trimmedLine) || preg_match('/^=+$/', $trimmedLine)) {
                    continue;
                }

                if ($dataStarted) {
                    $cleanLine = preg_replace('/^[-\\\\|\s]+/', '', $trimmedLine);

                    // 1. First approach: Use column positions if we have them
                    if ($alarmIdxPos !== false && $gponIndexPos !== false && $sernumPos !== false) {
                        $alarmIdx = trim(substr($cleanLine, 0, $gponIndexPos - $alarmIdxPos));
                        $interface = trim(substr($cleanLine, $gponIndexPos - $alarmIdxPos, $sernumPos - $gponIndexPos));
                        $serial = trim(substr($cleanLine, $sernumPos - $alarmIdxPos));

                        if (strpos($serial, ' ') !== false) {
                            $serial = trim(substr($serial, 0, strpos($serial, ' ')));
                        }
                    }

                    // 2. Second approach: Parse by delimiter '|' if present
                    if (empty($alarmIdx) || empty($interface) || empty($serial)) {
                        if (strpos($cleanLine, '|') !== false) {
                            $parts = explode('|', $cleanLine);
                            if (count($parts) >= 3) {
                                $alarmIdx = trim($parts[0]);
                                $interface = trim($parts[1]);
                                $serial = trim($parts[2]);
                            }
                        }
                    }

                    // 3. Third approach: Parse by spaces with specific validation
                    if (empty($alarmIdx) || empty($interface) || empty($serial)) {
                        $parts = preg_split('/\s+/', $cleanLine, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($parts) >= 3) {
                            if (is_numeric($parts[0])) {
                                $alarmIdx = $parts[0];
                                $interface = $parts[1];
                                $serial = $parts[2];

                                if (! preg_match('/^[A-Za-z]/', $serial)) {
                                    $serial = $parts[2];
                                }
                            }
                        }
                    }

                    // Add to results if we have valid data
                    if (! empty($alarmIdx) && ! empty($interface) && ! empty($serial)) {
                        $alarmIdx = (int) $alarmIdx;

                        if ($alarmIdx > 0 && strlen($interface) > 0 && strlen($serial) > 0) {
                            $unregData[] = [
                                'alarm-idx' => $alarmIdx,
                                'interface' => $interface,
                                'serial' => $serial,
                            ];
                        }
                    }
                }
            }

            return CommandResult::create([
                'success' => true,
                'command' => $command,
                'response' => $response,
                'error' => null,
                'result' => $unregData,
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        } catch (\Exception $e) {
            $finishedAt = Carbon::now();

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Inhibit all messages - TL1
     */
    public static function inhMsgAll(): ?CommandResult
    {
        $command = 'INH-MSG-ALL::ALL:::;';
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::make([
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

            return CommandResult::make([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Edit ONT VEIP - TL1
     */
    public static function edOntVeip(string $interface, EdOntVeipConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface);
        $command = "ED-ONTVEIP::$accessIdentifier:::::IS;";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Adds a egress port to the VLAN - TL1
     */
    public static function vlanEgPort(string $mode, string $interface, VlanEgPortConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface, 14, 1);
        $buildCommand = $config->buildCommand($mode);

        $command = "$mode-VLANEGPORT::$accessIdentifier:::$buildCommand;";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }

    /**
     * Provisions a new HGU TR069 short key-value pair - TL1
     */
    public static function hguTr069Sparam(string $mode, string $interface, HguTr069SparamConfig $config): ?CommandResult
    {
        $accessIdentifier = $config->buildIdentifier($interface);
        $buildCommand = $config->buildCommand($mode);

        $command = "$mode-HGUTR069-SPARAM::{$accessIdentifier}{$buildCommand};";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command, false);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  0 COMPLD')) {
                throw new \Exception($response);
            }

            return CommandResult::create([
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

            return CommandResult::create([
                'success' => false,
                'command' => $command,
                'response' => $response ?? null,
                'error' => $e->getMessage(),
                'result' => [],
                'created_at' => $createdAt,
                'finished_at' => $finishedAt,
            ]);
        }
    }
}
