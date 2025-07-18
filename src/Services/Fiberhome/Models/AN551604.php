<?php

namespace PauloHortelan\Onmt\Services\Fiberhome\Models;

use Illuminate\Support\Carbon;
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
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/ONUID/', $column)) {
                    $splitted = preg_split('/\\t/', $splittedResponse[$key + 1]);

                    $ontsOpticalPower = [
                        'RxPower' => (float) str_replace(',', '.', $splitted[1]) ?? null,
                        'RxPowerR' => $splitted[2] ?? null,
                        'TxPower' => (float) str_replace(',', '.', $splitted[3]) ?? null,
                        'TxPowerR' => $splitted[4] ?? null,
                        'CurrTxBias' => (float) str_replace(',', '.', $splitted[5]) ?? null,
                        'CurrTxBiasR' => $splitted[6] ?? null,
                        'Temperature' => (float) str_replace(',', '.', $splitted[7]) ?? null,
                        'TemperatureR' => $splitted[8] ?? null,
                        'Voltage' => (float) str_replace(',', '.', $splitted[9]) ?? null,
                        'VoltageR' => $splitted[10] ?? null,
                        'PTxPower' => (float) str_replace(',', '.', $splitted[11]) ?? null,
                        'PRxPower' => (float) str_replace(',', '.', $splitted[12]) ?? null,
                    ];
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
            'result' => $ontsOpticalPower,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Returns the ONTs state info
     */
    public static function lstOnuState(string $ponInterface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $opticalState = [];
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $command = "LST-ONUSTATE::OLTID=$ipOlt,PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/ONUID/', $column)) {
                    $splitted = preg_split('/\\t/', $splittedResponse[$key + 1]);

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
            'result' => $opticalState,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Returns the ONTs port info
     */
    public static function lstPortVlan(string $ponInterface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $ontsPortInfo = [];
        $command = "LST-PORTVLAN::OLTID=$ipOlt,PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/ONUIP/', $column)) {
                    $splitted = preg_split('/\\t/', $splittedResponse[$key + 1]);

                    $cvLan = isset($splitted[6]) ? (int) $splitted[6] : null;

                    $ontsPortInfo = [
                        'cvLan' => $cvLan,
                    ];
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
            'result' => $ontsPortInfo,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Returns the ONTs lan info
     */
    public static function lstOnuLanInfo(string $ponInterface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $ontsLanInfo = [];
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $command = "LST-ONULANINFO::OLTID=$ipOlt,PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/AdminStatus/', $column)) {
                    $splitted = preg_split('/\\t/', $splittedResponse[$key + 1]);

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
            'result' => $ontsLanInfo,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Returns the OLT uplink's lan perf
     */
    public static function lstLanPerf(string $portInterface): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $oltUplinksLanPerf = [];
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $command = "LST-LANPERF::OLTID=$ipOlt,PORTID=$portInterface,PORTID=NA-NA-NA-NA:CTAG::;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/AdminStatus/', $column)) {
                    $splitted = preg_split('/\\t/', $splittedResponse[$key + 1]);

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
            'result' => $oltUplinksLanPerf,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Returns the unregistered ONTs
     */
    public static function lstUnregOnu(?string $ponInterface = null): ?CommandResult
    {
        $unRegData = [];
        $ipOlt = self::$ipOlt;

        if ($ponInterface === null) {
            $command = "LST-UNREGONU::OLTID=$ipOlt:CTAG::;";
        } else {
            $command = "LST-UNREGONU::OLTID=$ipOlt,PONID=$ponInterface:CTAG::;";
        }

        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            $headerIndex = null;
            foreach ($splittedResponse as $key => $line) {
                if (preg_match('/^MAC\t/', $line)) {
                    $headerIndex = $key;
                    break;
                }
            }

            if ($headerIndex !== null) {
                $dataIndex = $headerIndex + 1;

                while ($dataIndex < count($splittedResponse) &&
                       ! preg_match('/^-{10,}$/', $splittedResponse[$dataIndex])) {

                    $line = trim($splittedResponse[$dataIndex]);
                    if (! empty($line)) {
                        $splitted = preg_split('/\t/', $line);

                        if ($ponInterface === null) {
                            if (count($splitted) >= 8) {
                                $unRegData[] = [
                                    'SLOTNO' => $splitted[0] ?? null,
                                    'PONNO' => $splitted[1] ?? null,
                                    'MAC' => $splitted[2] ?? null,
                                    'LOID' => $splitted[3] ?? null,
                                    'PWD' => $splitted[4] ?? null,
                                    'ERROR' => $splitted[5] ?? null,
                                    'AUTHTIME' => $splitted[6] ?? null,
                                    'DT' => $splitted[7] ?? null,
                                ];
                            }
                        } else {
                            if (count($splitted) >= 6) {
                                $unRegData[] = [
                                    'MAC' => $splitted[0] ?? null,
                                    'LOID' => $splitted[1] ?? null,
                                    'PWD' => $splitted[2] ?? null,
                                    'ERROR' => $splitted[3] ?? null,
                                    'AUTHTIME' => $splitted[4] ?? null,
                                    'DT' => $splitted[5] ?? null,
                                ];
                            }
                        }
                    }
                    $dataIndex++;
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
            'result' => $unRegData,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Returns the registered ONTs
     */
    public static function lstOnu(string $ponInterface): ?CommandResult
    {
        $regOnts = [];
        $ipOlt = self::$ipOlt;
        $command = "LST-ONU::OLTID=$ipOlt,PONID=$ponInterface:CTAG::;";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $splittedResponse = preg_split("/\r\n|\n|\r/", $response);

            foreach ($splittedResponse as $key => $column) {
                if (preg_match('/OLTID/', $column)) {
                    $numOnts = count($splittedResponse) - $key - 2;

                    if ($numOnts === 0) {
                        return self::createCommandResult([
                            'success' => true,
                            'command' => $command,
                            'response' => $response,
                            'error' => null,
                            'result' => [],
                            'created_at' => $createdAt,
                            'finished_at' => $finishedAt,
                        ]);
                    }

                    for ($i = 1; $i <= $numOnts; $i++) {
                        $splitted = preg_split('/\\t/', $splittedResponse[$key + $i]);

                        $regOnts[] = [
                            'OLTID' => $splitted[0] ?? null,
                            'PONID' => $splitted[1] ?? null,
                            'ONUNO' => $splitted[2] ?? null,
                            'NAME' => $splitted[3] ?? null,
                            'DESC' => $splitted[4] ?? null,
                            'ONUTYPE' => $splitted[5] ?? null,
                            'IP' => $splitted[6] ?? null,
                            'AUTHTYPE' => $splitted[7] ?? null,
                            'MAC' => $splitted[8] ?? null,
                            'LOID' => $splitted[9] ?? null,
                            'PWD' => $splitted[10] ?? null,
                            'SWVER' => $splitted[11] ?? null,
                        ];
                    }
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
            'result' => $regOnts,
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Authorize ONTs
     */
    public static function addOnu(string $ponInterface, string $serial, string $ontType, string $pppoeUsername): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $command = "ADD-ONU::OLTID=$ipOlt,PONID=$ponInterface:CTAG::AUTHTYPE=MAC,ONUID=$serial,ONUTYPE=$ontType,NAME=$pppoeUsername;";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Remove ONTs
     */
    public static function delOnu(string $ponInterface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $command = "DEL-ONU::OLTID=$ipOlt,PONID=$ponInterface:CTAG::ONUIDTYPE=MAC,ONUID=$serial;";
        $response = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Configure ONTs VLAN
     */
    public static function cfgLanPortVlan(string $ponInterface, string $serial, string $portInterface, LanConfig $config): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $response = null;
        $command = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $lanServiceCommand = $config->buildCommand();
            $command = "CFG-LANPORTVLAN::OLTID={$ipOlt},PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial,ONUPORT=$portInterface:CTAG::$lanServiceCommand;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Configure ONTs VEIP
     */
    public static function cfgVeipService(string $ponInterface, string $serial, string $portInterface, VeipConfig $config): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $response = null;
        $command = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $veipServiceCommand = $config->buildCommand();
            $command = "CFG-VEIPSERVICE::OLTID=$ipOlt,PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial,ONUPORT=$portInterface:CTAG::$veipServiceCommand;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Set ONTs WAN Service
     */
    public static function setWanService(string $ponInterface, string $serial, WanConfig $config): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $response = null;
        $command = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $wanServiceCommand = $config->buildCommand();
            $command = "SET-WANSERVICE::OLTID=$ipOlt,PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::$wanServiceCommand;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }

    /**
     * Reset ONUTs (restarts only)
     */
    public static function resetOnu(string $ponInterface, string $serial): ?CommandResult
    {
        $ipOlt = self::$ipOlt;
        $response = null;
        $command = null;
        $createdAt = Carbon::now();
        $finishedAt = null;

        try {
            $command = "RESET-ONU::OLTID=$ipOlt,PONID=$ponInterface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
            $response = self::$tl1Conn->exec($command);
            $finishedAt = Carbon::now();

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
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
            'result' => [],
            'created_at' => $createdAt,
            'finished_at' => $finishedAt,
        ]);
    }
}
