<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanServiceConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipServiceConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanServiceConfig;
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
        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (! $this->assertSameLength([self::$interfaces, self::$serials])) {
            throw new Exception('The number of interface(s) and serial(s) are not the same.');
        }
    }

    /**
     * Gets ONT's optical power
     *
     * Parameters 'interfaces' and 'serials' must be already provided
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
     * Parameters 'interfaces' and 'serials' must be already provided
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
     * Parameters 'interfaces' and 'serials' must be already provided
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

    public function registeredOnts(): ?array
    {
        if (self::$model === 'AN551604') {
            return AN551604::lstOnu();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function authorizeOnts(array $ontTypes = [], array $pppoeUsernames = []): ?array
    {
        $this->validateInterfacesSerials();

        if (! $this->assertSameLength([self::$interfaces, self::$serials, $ontTypes, $pppoeUsernames])) {
            throw new Exception('The number of interfaces, serials, ontTypes and pppoeUsernames are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::addOnu($ontTypes, $pppoeUsernames);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Configure ONT's LAN service
     *
     * Parameters 'interfaces' and 'serials' must be already provided
     *
     * @param  array  $portInface  Port interface. Example: 'NA-NA-NA-1'
     * @param  LanServiceConfig  $config  LAN service configuration parameters
     * @return array Info about each ONT configuration
     */
    public function configureLanOnts(string $portInterface, LanServiceConfig $config): ?array
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
     * Parameters 'interfaces' and 'serials' must be already provided
     *
     * @param  array  $portInface  Port interface. Example: 'NA-NA-NA-1'
     * @param  VeipServiceConfig  $config  VEIP service configuration parameters
     * @return array Info about each ONT configuration
     */
    public function configureVeipOnts(string $portInterface, VeipServiceConfig $config): ?array
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
     * Parameters 'interfaces' and 'serials' must be already provided
     *
     * @param  WanServiceConfig  $config  WAN service configurations parameters
     * @return array Info about each ONT configuration
     */
    public function configureWanOnts(WanServiceConfig $config): ?array
    {
        $this->validateInterfacesSerials();

        if (self::$model === 'AN551604') {
            return AN551604::setWanService($config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Remove/Deletes ONT's
     *
     * Parameters 'interfaces' and 'serials' must be already provided
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
