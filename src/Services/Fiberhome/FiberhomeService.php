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

    protected int $connTimeout = 4;

    protected int $streamTimeout = 2;

    protected static string $ipOlt;

    public static array $serials = [];

    public static array $interfaces = [];

    public function connect(string $ipOlt, string $username, string $password, ?string $ipServer = null): ?object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (!$this->isValidIP($ipOlt) || !$this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        self::$ipOlt = $ipOlt;
        self::$connection = TL1::getInstance($ipServer, 3337, $this->connTimeout, $this->streamTimeout, $username, $password, 'Fiberhome-' . self::$model);
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

    public function interface(string $interface): mixed
    {
        self::$interfaces = [$interface];

        return new static;
    }

    public function interfaces(array $interfaces): mixed
    {
        self::$interfaces = $interfaces;

        return new static;
    }

    public function serial(string $serial): mixed
    {
        self::$serials = [$serial];

        return new static;
    }

    public function serials(array $serials): mixed
    {
        self::$serials = array_map('strtoupper', $serials);

        return new static;
    }

    /**
     * Gets ONT's optical power
     *
     * @param  array  $interfaces  Interfaces list like 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serials list like 'CMSZ123456'
     * @return array List with info about each ONT power: 'rxPower', 'txPower'
     */
    public function ontsOpticalPower(array $interfaces = [], array $serials = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (!$this->assertSameLength(self::$interfaces, self::$serials)) {
            throw new Exception('The number of interfaces and serials are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::lstOMDDM();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    /**
     * Gets ONT's state info
     *
     * @param  array  $interfaces  Interfaces list like 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serials list like 'CMSZ123456'
     * @return array List with info about each ONT state: 'adminState', 'oprState', 'auth', 'lastOffTime'
     */
    public function ontsStateInfo(array $interfaces = [], array $serials = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (!$this->assertSameLength(self::$interfaces, self::$serials)) {
            throw new Exception('The number of interfaces and serials are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::lstOnuState();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function ontsPortInfo(array $interfaces = [], array $serials = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (!$this->assertSameLength(self::$interfaces, self::$serials)) {
            throw new Exception('The number of interfaces and serials are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::lstPortVlan();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function ontsLanInfo(array $interfaces = [], array $serials = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (!$this->assertSameLength(self::$interfaces, self::$serials)) {
            throw new Exception('The number of interfaces and serials are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::lstOnuLanInfo();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function oltUplinksLanPerf(array $portInterfaces = []): ?array
    {
        if (empty($portInterfaces)) {
            throw new Exception('Port interface(s) not found.');
        }

        if (self::$model === 'AN551604') {
            return (new AN551604())->lstLanPerf($portInterfaces);
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function unregisteredOnts(): ?array
    {
        if (self::$model === 'AN551604') {
            return (new AN551604())->lstUnregOnu();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function registeredOnts(): ?array
    {
        if (self::$model === 'AN551604') {
            return (new AN551604())->lstOnu();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function authorizeOnts(array $interfaces = [], array $serials = [], array $ontTypes = [], array $pppoeUsernames = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (empty($ontTypes)) {
            throw new Exception('ONU(s) Type(s) not found.');
        }

        if (empty($pppoeUsernames)) {
            throw new Exception('PPPoe Username(s) not found.');
        }

        if (!$this->assertSameLengthFour(self::$interfaces, self::$serials, $ontTypes, $pppoeUsernames)) {
            throw new Exception('The number of interfaces, serials, ontTypes and pppoeUsernames are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::addOnu($ontTypes, $pppoeUsernames);
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function configureVlanOnts(array $interfaces = [], array $serials = [], array $portInterfaces = [], array $vLans = [], array $ccoss = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (empty($portInterfaces)) {
            throw new Exception('Port Interface(s) not found.');
        }

        if (empty($vLans)) {
            throw new Exception('Vlan(s) not found.');
        }

        if (empty($ccoss)) {
            throw new Exception('CCOS(s) not found.');
        }

        if (!$this->assertSameLengthFive(self::$interfaces, self::$serials, $portInterfaces, $vLans, $ccoss)) {
            throw new Exception('The number of interfaces, serials, portInterfaces, vLans and ccoss are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::cfgLanPortVlan($portInterfaces, $vLans, $ccoss);
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    /**
     * Remove/Deletes ONT's
     *
     * @param  array  $interfaces  Interfaces list like 'NA-NA-{SLOT}-{PON}'
     * @param  array  $serials  Serials list like 'CMSZ123456'
     * @return array Info about each ONT delete result
     */
    public function removeOnts(array $interfaces = [], array $serials = []): ?array
    {
        if (!empty($interfaces)) {
            self::$interfaces = $interfaces;
        }

        if (!empty($serials)) {
            self::$serials = array_map('strtoupper', $serials);
        }

        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }

        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }

        if (!$this->assertSameLength(self::$interfaces, self::$serials)) {
            throw new Exception('The number of interfaces, serials and ontTypes are not the same.');
        }

        if (self::$model === 'AN551604') {
            return AN551604::delOnu();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }
}
