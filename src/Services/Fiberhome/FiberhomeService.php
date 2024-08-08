<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
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

    public function connect(string $ipOlt, string $username, string $password, ?string $ipServer = null): ?object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        self::$ipOlt = $ipOlt;
        self::$connection = TL1::getInstance($ipServer, 3337, $this->connTimeout, $this->streamTimeout, $username, $password, 'Fiberhome-'.self::$model);
        self::$connection->stripPromptFromBuffer(true);

        return new static;
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

        return new static;
    }

    public function serials(array $serials): mixed
    {
        self::$serials = array_map('strtoupper', $serials);

        return new static;
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

    public function configureVlanOnts(array $portInterfaces, array $vlans, array $ccoss): ?array
    {
        $this->validateInterfacesSerials();

        if (! $this->assertSameLength([self::$interfaces, self::$serials, $portInterfaces, $vlans, $ccoss])) {
            throw new Exception('The number of interfaces, serials, portInterfaces, vlans and ccoss are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::cfgLanPortVlan($portInterfaces, $vlans, $ccoss);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Configure ONT's Veip and Vlan
     *
     * Parameters 'interfaces' and 'serials' must be already provided
     *
     * @param  array  $portInfaces  Port interface list. Example: 'NA-NA-NA-1'
     * @param  array  $serviceIds  Service Id list
     * @param  array  $vlans  Vlan list
     * @param  array  $serviceModelsProfiles  Service Model Profile list
     * @param  array  $serviceTypes  Serial list. Example: ['CMSZ123456']
     * @return array Info about each ONT configuration
     */
    public function configureVeipVlanOnts(array $portInterfaces, array $serviceIds, array $vlans, array $serviceModelProfiles, array $serviceTypes): ?array
    {
        $this->validateInterfacesSerials();

        if (! $this->assertSameLength([self::$interfaces, $portInterfaces, $serviceIds, $vlans, $serviceModelProfiles, $serviceTypes])) {
            throw new Exception('The number of array elements are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::cfgVeipService($portInterfaces, $serviceIds, $vlans, $serviceModelProfiles, $serviceTypes);
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
