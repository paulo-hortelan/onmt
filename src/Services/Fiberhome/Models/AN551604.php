<?php

namespace PauloHortelan\Onmt\Services\Fiberhome\Models;

use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanServiceConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipServiceConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanServiceConfig;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;

class AN551604 extends FiberhomeService
{
    /**
     * Returns the ONT's optical power
     */
    public static function lstOMDDM(): ?array
    {
        $ipOlt = self::$ipOlt;
        $ontsOpticalPower = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $command = "LST-OMDDM::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $response = preg_split("/\r\n|\n|\r/", $response);

                foreach ($response as $key => $column) {
                    if (preg_match('/ONUID/', $column)) {
                        $splitted = preg_split('/\\t/', $response[$key + 1]);

                        $rxPower = (float) str_replace(',', '.', $splitted[1]);
                        $txPower = (float) str_replace(',', '.', $splitted[3]);

                        $ontsOpticalPower[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
                                'interface' => $interface,
                                'serial' => $serial,
                                'rxPower' => $rxPower ?? null,
                                'txPower' => $txPower ?? null,
                            ],
                        ];
                    }
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsOpticalPower[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                        'serial' => $serial,
                    ],
                ];
            }
        }

        return $ontsOpticalPower;
    }

    /**
     * Returns the ONT's state info
     */
    public static function lstOnuState(): ?array
    {
        $ipOlt = self::$ipOlt;
        $opticalStates = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $command = "LST-ONUSTATE::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
                $response = self::$connection->exec($command);

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

                        $opticalStates[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
                                'interface' => $interface,
                                'serial' => $serial,
                                'adminState' => $adminState ?? null,
                                'oprState' => $oprState ?? null,
                                'auth' => $auth ?? null,
                                'lastOffTime' => $lastOffTime ?? null,
                            ],
                        ];
                    }
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $opticalStates[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                        'serial' => $serial,
                    ],
                ];
            }
        }

        return $opticalStates;
    }

    /**
     * Returns the ONT's port info
     */
    public static function lstPortVlan(): ?array
    {
        $ipOlt = self::$ipOlt;
        $ontsPortInfo = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $command = "LST-PORTVLAN::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $response = preg_split("/\r\n|\n|\r/", $response);

                var_dump($response);

                foreach ($response as $key => $column) {
                    if (preg_match('/ONUIP/', $column)) {
                        $splitted = preg_split('/\\t/', $response[$key + 1]);

                        $cvLan = isset($splitted[6]) ? (int) $splitted[6] : null;

                        $ontsPortInfo[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
                                'interface' => $interface,
                                'serial' => $serial,
                                'cvLan' => $cvLan,
                            ],
                        ];
                    }
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsPortInfo[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                        'serial' => $serial,
                    ],
                ];
            }
        }

        return $ontsPortInfo;
    }

    /**
     * Returns the ONT's lan info
     */
    public static function lstOnuLanInfo(): ?array
    {
        $ipOlt = self::$ipOlt;
        $ontsLanInfo = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $command = "LST-ONULANINFO::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;";
                $response = self::$connection->exec($command);

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

                        $ontsLanInfo[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
                                'interface' => $interface,
                                'serial' => $serial,
                                'adminStatus' => $adminStatus ?? null,
                                'operStatus' => $operStatus ?? null,
                                'duplex' => $duplex ?? null,
                                'pVid' => $pVid ?? null,
                                'vlanPriority' => $vlanPriority ?? null,
                                'speed' => $speed ?? null,
                            ],
                        ];
                    }
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $ontsLanInfo[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [
                        'interface' => $interface,
                        'serial' => $serial,
                    ],
                ];
            }
        }

        return $ontsLanInfo;
    }

    /**
     * Returns the OLT uplink's lan perf
     */
    public static function lstLanPerf(array $portInterfaces): ?array
    {
        $ipOlt = self::$ipOlt;
        $oltUplinksLanPerf = [];

        for ($i = 0; $i < count($portInterfaces); $i++) {
            $portInterface = $portInterfaces[$i];

            try {
                $command = "LST-LANPERF::OLTID=$ipOlt,PORTID=$portInterface,PORTID=NA-NA-NA-NA:CTAG::;";
                $response = self::$connection->exec($command);

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

                        $oltUplinksLanPerf[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
                                'portInterface' => $portInterface,
                                'adminStatus' => $adminStatus ?? null,
                                'operStatus' => $operStatus ?? null,
                                'duplex' => $duplex ?? null,
                                'pVid' => $pVid ?? null,
                                'vlanPriority' => $vlanPriority ?? null,
                                'speed' => $speed ?? null,
                            ],
                        ];
                    }
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $oltUplinksLanPerf[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $oltUplinksLanPerf;
    }

    /**
     * Returns the unregistered ONT's
     */
    public static function lstUnregOnu(): ?array
    {
        $ipOlt = self::$ipOlt;
        $unregOnts = [];

        try {
            $command = "LST-UNREGONU::OLTID=$ipOlt:CTAG::;";
            $response = self::$connection->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/SLOTNO/', $column)) {
                    $numOnts = count($response) - $key - 2;

                    if ($numOnts === 0) {
                        $unregOnts[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [],
                        ];
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

                        $unregOnts[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
                                'slot' => $slot ?? null,
                                'pon' => $pon ?? null,
                                'serial' => $serial ?? null,
                                'loid' => $loid ?? null,
                                'pwd' => $pwd ?? null,
                                'error' => $error ?? null,
                                'authTime' => $authTime ?? null,
                                'dt' => $dt ?? null,
                            ],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            $unregOnts[] = [
                'success' => false,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }

        return $unregOnts;
    }

    /**
     * Returns the registered ONT's
     */
    public static function lstOnu(): ?array
    {
        $ipOlt = self::$ipOlt;
        $regOnts = [];

        try {
            $command = "LST-ONU::OLTID=$ipOlt:CTAG::;";
            $response = self::$connection->exec($command);

            if (! str_contains($response, 'M  CTAG COMPLD')) {
                throw new \Exception($response);
            }

            $response = preg_split("/\r\n|\n|\r/", $response);

            foreach ($response as $key => $column) {
                if (preg_match('/OLTID/', $column)) {
                    $numOnts = count($response) - $key - 2;

                    if ($numOnts === 0) {
                        $regOnts[] = [
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [],
                        ];
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
                            'success' => true,
                            'command' => $command,
                            'errorInfo' => null,
                            'result' => [
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
                            ],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $errorInfo = $e->getMessage();

            $regOnts[] = [
                'success' => false,
                'errorInfo' => $errorInfo,
                'result' => [],
            ];
        }

        return $regOnts;
    }

    /**
     * Authorize ONT's
     */
    public static function addOnu($ontTypes, $pppoeUsernames): ?array
    {
        $ipOlt = self::$ipOlt;
        $authResponse = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];
            $ontType = $ontTypes[$i];
            $pppoeUsername = $pppoeUsernames[$i];

            try {
                $command = "ADD-ONU::OLTID=$ipOlt,PONID=$interface:CTAG::AUTHTYPE=MAC,ONUID=$serial,ONUTYPE=$ontType,NAME=$pppoeUsername;";
                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $authResponse[] = [
                    'success' => true,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $authResponse[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $authResponse;
    }

    /**
     * Remove ONT's
     */
    public static function delOnu(): ?array
    {
        $ipOlt = self::$ipOlt;
        $delResponse = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $command = "DEL-ONU::OLTID=$ipOlt,PONID=$interface:CTAG::ONUIDTYPE=MAC,ONUID=$serial;";
                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $delResponse[] = [
                    'success' => true,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $delResponse[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $delResponse;
    }

    /**
     * Configure ONT's VLAN
     */
    public static function cfgLanPortVlan($portInterface, LanServiceConfig $config): ?array
    {
        $ipOlt = self::$ipOlt;
        $configVlanResponse = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $lanServiceCommand = $config->buildCommand();

                $command = "CFG-LANPORTVLAN::OLTID={$ipOlt},PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial,ONUPORT=$portInterface:CTAG::$lanServiceCommand;";
                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $configVlanResponse[] = [
                    'success' => true,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $configVlanResponse[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $configVlanResponse;
    }

    /**
     * Configure ONT's VEIP
     */
    public static function cfgVeipService(string $portInterface, VeipServiceConfig $config): ?array
    {
        $ipOlt = self::$ipOlt;
        $cfgVeipVlanResponses = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $veipServiceCommand = $config->buildCommand();

                $command = "CFG-VEIPSERVICE::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial,ONUPORT=$portInterface:CTAG::$veipServiceCommand;";

                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $cfgVeipVlanResponses[] = [
                    'success' => true,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $cfgVeipVlanResponses[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $cfgVeipVlanResponses;
    }

    /**
     * Set ONT's WAN Service
     */
    public static function setWanService(WanServiceConfig $config): ?array
    {
        $ipOlt = self::$ipOlt;
        $setWanServiceResponses = [];

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            try {
                $wanServiceCommand = $config->buildCommand();

                $command = "SET-WANSERVICE::OLTID=$ipOlt,PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::$wanServiceCommand;";

                $response = self::$connection->exec($command);

                if (! str_contains($response, 'M  CTAG COMPLD')) {
                    throw new \Exception($response);
                }

                $setWanServiceResponses[] = [
                    'success' => true,
                    'command' => $command,
                    'errorInfo' => null,
                    'result' => [],
                ];
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();

                $setWanServiceResponses[] = [
                    'success' => false,
                    'command' => $command,
                    'errorInfo' => $errorInfo,
                    'result' => [],
                ];
            }
        }

        return $setWanServiceResponses;
    }
}
