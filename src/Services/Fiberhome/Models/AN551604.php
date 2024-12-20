<?php

namespace PauloHortelan\Onmt\Services\Fiberhome\Models;

use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;

class AN551604 extends FiberhomeService
{
    /**
     * Returns the ONTs optical power
     */
    public static function lstOMDDM(string $interface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $ontsOpticalPower = [];
        $command = "LST-OMDDM::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";

        try {
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/ONUID/', $column)) {
                    $splitted = preg_split('/\\t/', $response[$key + 1]);

                    $rxPower = (float) str_replace(',', '.', $splitted[1]);
                    $txPower = (float) str_replace(',', '.', $splitted[3]);

                    $ontsOpticalPower = [
                        'rxPower' => $rxPower ?? null,
                        'txPower' => $txPower ?? null,
                    ];
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
            'result' => $ontsOpticalPower,
        ]);
    }

    /**
     * Returns the ONTs state info
     */
    public static function lstOnuState(string $interface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $opticalState = [];

        try {
            $command = "LST-ONUSTATE::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/ONUID/', $column)) {
                    $splitted = preg_split('/\\t/', $response[$key + 1]);

                    $adminState = $splitted[1];
                    $oprState = $splitted[2];
                    $auth = $splitted[3];
                    $lastOffTime = $splitted[6];

                    $opticalState = [
                        'adminState' => $adminState ?? null,
                        'oprState' => $oprState ?? null,
                        'auth' => $auth ?? null,
                        'lastOffTime' => $lastOffTime ?? null,
                    ];
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
            'result' => $opticalState,
        ]);
    }

    /**
     * Returns the ONTs port info
     */
    public static function lstPortVlan(string $interface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $ontsPortInfo = [];
        $command = "LST-PORTVLAN::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";

        try {
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/ONUIP/', $column)) {
                    $splitted = preg_split('/\\t/', $response[$key + 1]);

                    $cvLan = isset($splitted[6]) ? (int) $splitted[6] : null;

                    $ontsPortInfo = [
                        'cvLan' => $cvLan,
                    ];
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
            'result' => $ontsPortInfo,
        ]);
    }

    /**
     * Returns the ONTs lan info
     */
    public static function lstOnuLanInfo(): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $ontsLanInfo = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $command = "LST-ONULANINFO::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
                $response = self::$tl1Conn->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $response = preg_split("/\r\n|\n|\r/", $response);

                foreach ($response as $key => $column) {
                    if (preg_match('/AdminStatus/', $column)) {
                        $splitted = preg_split('/\\t/', $response[$key + 1]);

                        $adminStatus = $splitted[0];
                        $operStatus = $splitted[1];
                        $duplex = $splitted[2];
                        $pVid = (int) $splitted[3];
                        $vlanPriority = $splitted[4];
                        $speed = $splitted[5];

                        $ontsLanInfo = [
                            'adminStatus' => $adminStatus ?? null,
                            'operStatus' => $operStatus ?? null,
                            'duplex' => $duplex ?? null,
                            'pVid' => $pVid ?? null,
                            'vlanPriority' => $vlanPriority ?? null,
                            'speed' => $speed ?? null,
                        ];
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
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'error' => null,
            'result' => $ontsLanInfo,
        ]);
    }

    /**
     * Returns the OLT uplink's lan perf
     */
    public static function lstLanPerf(string $portInterface): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $oltUplinksLanPerf = [];

        try {
            $command = "LST-LANPERF::OLTID=$ipOlt,PORTID=$portInterface,PORTID=NA-NA-NA-NA:CTAG::;";
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/AdminStatus/', $column)) {
                    $splitted = preg_split('/\\t/', $response[$key + 1]);

                    $adminStatus = $splitted[0];
                    $operStatus = $splitted[1];
                    $duplex = $splitted[2];
                    $pVid = (int) $splitted[3];
                    $vlanPriority = $splitted[4];
                    $speed = $splitted[5];

                    $oltUplinksLanPerf = [
                        'portInterface' => $portInterface,
                        'adminStatus' => $adminStatus ?? null,
                        'operStatus' => $operStatus ?? null,
                        'duplex' => $duplex ?? null,
                        'pVid' => $pVid ?? null,
                        'vlanPriority' => $vlanPriority ?? null,
                        'speed' => $speed ?? null,
                    ];
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
            'result' => $oltUplinksLanPerf,
        ]);
    }

    /**
     * Returns the unregistered ONTs
     */
    public static function lstUnregOnu(): ?CommandResult
    {
        $unRegData = [];
        $ipOlt = self::$ipOlt;
        $command = "LST-UNREGONU::OLTID=$ipOlt:CTAG::;";

        try {
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/SLOTNO/', $column)) {
                    $numOnts = count($response) - $key - 2;

                    if ($numOnts === 0) {
                        return CommandResult::create([
                            'success' => true,
                            'command' => $command,
                            'error' => null,
                            'result' => [],
                        ]);
                    }

                    for ($i = 1; $i <= $numOnts; $i++) {
                        $splitted = preg_split('/\\t/', $response[$key + $i]);

                        $slot = (int) $splitted[0];
                        $pon = (int) $splitted[1];
                        $serial = $splitted[2];
                        $loid = $splitted[3];
                        $pwd = $splitted[4];
                        $error = $splitted[5];
                        $authTime = $splitted[6];
                        $dt = $splitted[7];

                        $unRegData = [
                            'slot' => $slot ?? null,
                            'pon' => $pon ?? null,
                            'serial' => $serial ?? null,
                            'loid' => $loid ?? null,
                            'pwd' => $pwd ?? null,
                            'error' => $error ?? null,
                            'authTime' => $authTime ?? null,
                            'dt' => $dt ?? null,
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
            'result' => $unRegData,
        ]);
    }

    /**
     * Returns the registered ONTs
     */
    public static function lstOnu(): ?CommandResult
    {
        $regOnts = [];
        $ipOlt = self::$ipOlt;
        $command = "LST-ONU::OLTID=$ipOlt:CTAG::;";

        try {
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/OLTID/', $column)) {
                    $numOnts = count($response) - $key - 2;

                    if ($numOnts === 0) {
                        return CommandResult::create([
                            'success' => true,
                            'command' => $command,
                            'error' => null,
                            'result' => [],
                        ]);
                    }

                    for ($i = 1; $i <= $numOnts; $i++) {
                        $splitted = preg_split('/\\t/', $response[$key + $i]);

                        $oltId = $splitted[0];
                        $ponId = $splitted[1];
                        $onuNo = $splitted[2];
                        $name = $splitted[3];
                        $desc = $splitted[4];
                        $onuTypeIp = $splitted[5];
                        $authType = $splitted[6];
                        $serial = $splitted[7];
                        $loid = $splitted[8];
                        $pwd = $splitted[9];
                        $swVer = $splitted[10];

                        $regOnts[] = [
                            'oltId' => $oltId ?? null,
                            'ponId' => $ponId ?? null,
                            'onuNo' => $onuNo ?? null,
                            'name' => $name ?? null,
                            'desc' => $desc ?? null,
                            'onuTypeIp' => $onuTypeIp ?? null,
                            'authType' => $authType ?? null,
                            'serial' => $serial ?? null,
                            'loid' => $loid ?? null,
                            'pwd' => $pwd ?? null,
                            'swVer' => $swVer ?? null,
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
            'result' => $regOnts,
        ]);
    }

    /**
     * Authorize ONTs
     */
    public static function addOnu(string $interface, string $serial, string $ontType, string $pppoeUsername): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $command = "ADD-ONU::OLTID=$ipOlt,PONID=$interface:CTAG::AUTHTYPE=MAC,ONUID=$serial,ONUTYPE=$ontType,NAME=$pppoeUsername;";

        try {
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
        ]);
    }

    /**
     * Remove ONTs
     */
    public static function delOnu(string $interface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $command = "DEL-ONU::OLTID=$ipOlt,PONID=$interface:CTAG::ONUIDTYPE=MAC,ONUID=$serial;";

        try {
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
        ]);
    }

    /**
     * Configure ONTs VLAN
     */
    public static function cfgLanPortVlan(string $interface, string $serial, string $portInterface, LanConfig $config): ?CommandResult
    {
        $ipOlt = self::$ipOlt;

        try {
            $lanServiceCommand = $config->buildCommand();

            $command = "CFG-LANPORTVLAN::OLTID={$ipOlt},PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial,ONUPORT=$portInterface:CTAG::$lanServiceCommand;";
            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
        ]);
    }

    /**
     * Configure ONTs VEIP
     */
    public static function cfgVeipService(string $interface, string $serial, string $portInterface, VeipConfig $config): ?CommandResult
    {
        $ipOlt = self::$ipOlt;

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $veipServiceCommand = $config->buildCommand();

                $command = "CFG-VEIPSERVICE::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial,ONUPORT=$portInterface:CTAG::$veipServiceCommand;";

                $response = self::$tl1Conn->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }
            } catch (\Exception $e) {
                return CommandResult::create([
                    'success' => false,
                    'command' => $command,
                    'error' => $e->getMessage(),
                    'result' => [],
                ]);
            }
        }

        return CommandResult::create([
            'success' => true,
            'command' => $command,
            'error' => null,
            'result' => [],
        ]);
    }

    /**
     * Set ONTs WAN Service
     */
    public static function setWanService(string $interface, string $serial, WanConfig $config): ?CommandResult
    {
        $ipOlt = self::$ipOlt;

        try {
            $wanServiceCommand = $config->buildCommand();

            $command = "SET-WANSERVICE::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::$wanServiceCommand;";

            $response = self::$tl1Conn->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
        ]);
    }
}
