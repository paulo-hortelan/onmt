<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Fiberhome\Models\AN551604;

class FiberhomeService
{
    use Assertations, Validations;

    protected static TL1 $connection;

    protected static string $model = 'AN551604';

    protected int $connTimeout = 5;

    protected int $streamTimeout = 4;

    protected static string $ipOlt;

    public static array $serials = [];

    public static array $interfaces = [];

    public function connect(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null): ?object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('Provided IP(s) are not valid(s).');
        }

        self::$ipOlt = $ipOlt;
        self::$connection = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Fiberhome-'.self::$model);
        self::$connection->stripPromptFromBuffer(true);

        return $this;
    }

    public function disconnect(): void
    {
        if (empty($this->connection)) {
            throw new Exception('No connection established.');
        }

        $this->connection->destroy();
    }

    public function timeout(int $connTimeout, int $streamTimeout): object
    {
        $this->connTimeout = $connTimeout;
        $this->streamTimeout = $streamTimeout;

        return $this;
    }

    public function interfaces(array $interfaces): mixed
    {
        self::$interfaces = $interfaces;

        return $this;
    }

    public function serials(array $serials): mixed
    {
        self::$serials = array_map('strtoupper', $serials);

        return $this;
    }

    private function validateInterfacesSerials()
    {
        if (empty(self::$interfaces) || count(array_filter(self::$interfaces)) < count(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials) || count(array_filter(self::$interfaces)) < count(self::$interfaces)) {
            throw new Exception('Serial(s) not found.');
        }

        if (! $this->assertSameLength([self::$interfaces, self::$serials])) {
            throw new Exception('The number of interface(s) and serial(s) are not the same.');
        }
    }

    /**
     * Gets ONT's optical power
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  array  $interfaces  Interfaces list in the format 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serials list. Example: ['CMSZ123456']
     * @return array List with info about each ONT power: 'rxPower', 'txPower'
     */
    public function ontsOpticalPower(): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::lstOMDDM();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Gets ONT's state info
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  array  $interfaces  Interface list in the format 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serial list. Example: ['CMSZ123456']
     * @return array List with info about each ONT state: 'adminState', 'oprState', 'auth', 'lastOffTime'
     */
    public function ontsStateInfo(): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::lstOnuState();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Gets ONT's port info
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  array  $interfaces  Interface list in the format 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serial list. Example: ['CMSZ123456']
     * @return array List with info about each ONT port: 'cVlan'
     */
    public function ontsPortInfo(): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::lstPortVlan();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function ontsLanInfo(): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::lstOnuLanInfo();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function oltUplinksLanPerf(array $portInterfaces): ?array
    {
        if (self::$model === 'AN551604') {
            return AN551604::lstLanPerf($portInterfaces);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * List unregistered ONT's
     *
     * @return array Info about each unregistered ONT
     */
    public function unregisteredOnts(): ?array
    {
        if (self::$model === 'AN551604') {
            return AN551604::lstUnregOnu();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * List registered ONT's
     *
     * @return array Info about each registered ONT
     */
    public function registeredOnts(): ?array
    {
        if (self::$model === 'AN551604') {
            return AN551604::lstOnu();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Authorize ONT's
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $ontType  ONT's type. Example: 'HG260'
     * @param  string  $pppoeUsername  PPPOE username.
     * @return array Info about each ONT authorization
     */
    public function authorizeOnts(string $ontType, string $pppoeUsername): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::addOnu($ontType, $pppoeUsername);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Configure ONT's LAN service
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  array  $portInface  Port interface. Example: 'NA-NA-NA-1'
     * @param  LanConfig  $config  LAN service configuration parameters
     * @return array Info about each ONT configuration
     */
    public function configureLanOnts(string $portInterface, LanConfig $config): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::cfgLanPortVlan($portInterface, $config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Configure ONT's VEIP service
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $portInterface  Port interface. Example: 'NA-NA-NA-1'
     * @param  VeipConfig  $config  VEIP service configuration parameters
     * @return array Info about each ONT configuration
     */
    public function configureVeipOnts(string $portInterface, VeipConfig $config): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::cfgVeipService($portInterface, $config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Configure ONT's WAN service
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  WanConfig  $config  WAN service configurations parameters
     * @return array Info about each ONT configuration
     */
    public function configureWanOnts(WanConfig $config): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::setWanService($config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Provision ONT's Router with VEIP
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $ontType  ONT's type. Example: 'HG260'
     * @param  string  $pppoeUsername  PPPOE username.
     * @param  string  $portInterface  Port interface. Example: 'NA-NA-NA-1'
     * @param  VeipConfig  $veipConfig  VEIP service configuration parameters
     * @return array Info about each ONT configuration
     */
    public function provisionRouterVeipOnts(string $ontType, string $pppoeUsername, string $portInterface, VeipConfig $veipConfig): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $provisionResult = [];

        $interfaces = self::$interfaces;
        $serials = self::$serials;

        for ($i = 0; $i < count($interfaces); $i++) {
            $interface = $interfaces[$i];
            $serial = $serials[$i];

            $this->interfaces([$interface])->serials([$serial]);

            if (self::$model === 'AN551604') {
                $authorizedOnt = AN551604::addOnu($ontType, $pppoeUsername);

                if (! $authorizedOnt[0]['success']) {
                    $provisionResult[] = $authorizedOnt[0];

                    continue;
                }

                $configuredOnt = AN551604::cfgVeipService($portInterface, $veipConfig);

                $provisionResult[] = $configuredOnt[0];
            }
        }

        $this->interfaces([$interfaces])->serials([$serials]);

        return $provisionResult;
    }

    /**
     * Provision ONT's Router with WAN
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $ontType  ONT's type. Example: 'HG260'
     * @param  string  $pppoeUsername  PPPOE username.
     * @param  WanConfig  $wanConfig  WAN service configuration parameters
     * @return array Info about each ONT configuration
     */
    public function provisionRouterWanOnts(string $ontType, string $pppoeUsername, WanConfig $wanConfig): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $provisionResult = [];

        $interfaces = self::$interfaces;
        $serials = self::$serials;

        for ($i = 0; $i < count($interfaces); $i++) {
            $interface = $interfaces[$i];
            $serial = $serials[$i];

            $this->interfaces([$interface])->serials([$serial]);

            if (self::$model === 'AN551604') {
                $authorizedOnt = AN551604::addOnu($ontType, $pppoeUsername);

                if (! $authorizedOnt[0]['success']) {
                    $provisionResult[] = $authorizedOnt[0];

                    continue;
                }

                $wanConfig->uPort = 0;
                $wanConfig->ssdId = null;

                // UPORT = 0
                $configuredOnt = AN551604::setWanService($wanConfig);

                if (! $configuredOnt[0]['success']) {
                    $provisionResult[] = $configuredOnt[0];

                    continue;
                }

                $wanConfig->uPort = null;
                $wanConfig->ssdId = 1;

                // SSDID = 0
                $configuredOnt = AN551604::setWanService($wanConfig);

                if (! $configuredOnt[0]['success']) {
                    $provisionResult[] = $configuredOnt[0];

                    continue;
                }

                $wanConfig->ssdId = 5;

                $configuredOnt = AN551604::setWanService($wanConfig);

                if (! $configuredOnt[0]['success']) {
                    $provisionResult[] = $configuredOnt[0];

                    continue;
                }

                $provisionResult[] = $configuredOnt[0];
            }
        }

        $this->interfaces([$interfaces])->serials([$serials]);

        return $provisionResult;
    }

    /**
     * Provision ONT's Bridge
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $ontType  ONT's type. Example: 'HG260'
     * @param  string  $pppoeUsername  PPPOE username.
     * @param  string  $portInterface  Port interface. Example: 'NA-NA-NA-1'
     * @param  LanConfig  $lanConfig  LAN service configuration parameters
     * @return array An array per interface containing an array for each command
     */
    public function provisionBridgeOnts(string $ontType, string $pppoeUsername, string $portInterface, LanConfig $lanConfig): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $provisionResult = [];

        $interfaces = self::$interfaces;
        $serials = self::$serials;

        for ($i = 0; $i < count($interfaces); $i++) {
            $interface = $interfaces[$i];
            $serial = $serials[$i];

            $this->interfaces([$interface])->serials([$serial]);

            if (self::$model === 'AN551604') {
                $authorizedOnt = AN551604::addOnu($ontType, $pppoeUsername);

                $provisionResult[$i][] = $authorizedOnt[0];

                if (! $authorizedOnt[0]['success']) {
                    continue;
                }

                $configuredOnt = AN551604::cfgLanPortVlan($portInterface, $lanConfig);

                $provisionResult[$i][] = $configuredOnt[0];
            }
        }

        $this->interfaces([$interfaces])->serials([$serials]);

        return $provisionResult;
    }

    /**
     * Remove/Deletes ONT's
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @return array Info about each ONT delete result
     */
    public function removeOnts(): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::delOnu();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }
}
