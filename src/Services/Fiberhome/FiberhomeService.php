<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Fiberhome\Models\AN551604;

class FiberhomeService
{
    use Assertations, Validations;

    protected static ?TL1 $tl1Conn = null;

    protected static ?Telnet $telnetConn = null;

    protected static string $model = 'AN551604';

    protected static ?string $operator;

    protected int $connTimeout = 5;

    protected int $streamTimeout = 4;

    protected static string $ipOlt = '';

    public static array $serials = [];

    public static array $interfaces = [];

    private ?CommandResultBatch $globalCommandBatch = null;

    public function connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null): ?object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('Provided IP(s) are not valid(s).');
        }

        self::$ipOlt = $ipOlt;
        self::$operator = config('onmt.default_operator');

        self::$tl1Conn = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Fiberhome-'.self::$model);
        self::$tl1Conn->stripPromptFromBuffer(true);
        self::$tl1Conn->authenticate($username, $password, 'Fiberhome-'.self::$model);

        return $this;
    }

    public function disconnect(): void
    {
        if (isset(self::$telnetConn)) {
            self::$telnetConn->destroy();
            self::$telnetConn = null;

            return;
        }

        if (isset(self::$tl1Conn)) {
            self::$tl1Conn->destroy();
            self::$tl1Conn = null;

            return;
        }

        throw new Exception('No connection established.');
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

    public function enableDebug(): void
    {
        if (isset(self::$tl1Conn)) {
            self::$tl1Conn->enableDebug();

            return;
        }

        throw new Exception('No connection established.');
    }

    public function disableDebug(): void
    {
        if (isset(self::$tl1Conn)) {
            self::$tl1Conn->disableDebug();

            return;
        }

        throw new Exception('No connection established.');
    }

    private function validateTelnet(): void
    {
        if (empty(self::$telnetConn)) {
            throw new Exception('Telnet connection not established.');
        }
    }

    private function validateTL1(): void
    {
        if (empty(self::$tl1Conn)) {
            throw new Exception('TL1 connection not established.');
        }
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

    private function validateSingleInterfaceSerial(): void
    {
        if (count(self::$interfaces) > 1 || count(self::$serials) > 1) {
            throw new Exception('Multiple Interfaces or Serials found.');
        }
    }

    public function setOperator(string $operator): object
    {
        self::$operator = $operator;

        return $this;
    }

    /**
     * Starts the commands execution and saves in a single CommandResultBatch
     */
    public function startRecordingCommands(
        ?string $description = null,
        ?string $ponInterface = null,
        ?string $interface = null,
        ?string $serial = null,
        ?string $operator = null
    ): void {
        $this->validateSingleInterfaceSerial();

        $this->globalCommandBatch =
            CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => $description,
                'pon_interface' => $ponInterface,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => $operator,
            ]);
    }

    /**
     * Gets ONT's optical power
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @return Collection Collection with info about each ONT power: 'rxPower', 'txPower'
     */
    public function ontsOpticalPower(): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::lstOMDDM($interface, $serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONT's state info
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @return Collection Collection with info about each ONT state: 'adminState', 'oprState', 'auth', 'lastOffTime'
     */
    public function ontsStateInfo(): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::lstOnuState($interface, $serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONT's port info
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @return Collection Collection with info about each ONT port: 'cVlan'
     */
    public function ontsPortInfo(): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::lstPortVlan($interface, $serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * List ONT's LAN Info
     *
     * @return Collection Info about ONT LAN
     */
    public function ontsLanInfo(): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = AN551604::lstOnuLanInfo();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * List OLT uplink's lan perf
     *
     * @return Collection Info about OLT
     */
    public function oltUplinksLanPerf(string $portInterface): ?Collection
    {
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = AN551604::lstLanPerf($portInterface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * List unregistered ONT's
     *
     * @return Collection Info about each unregistered ONT
     */
    public function unregisteredOnts(): ?Collection
    {
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = AN551604::lstUnregOnu();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * List registered ONT's
     *
     * @return Collection Info about each registered ONT
     */
    public function registeredOnts(): ?Collection
    {
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = AN551604::lstOnu();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * Authorize ONT's
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $ontType  ONT's type. Example: 'HG260'
     * @param  string  $pppoeUsername  PPPOE username.
     * @return Collection Info about each ONT authorization
     */
    public function authorizeOnts(string $ontType, string $pppoeUsername): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::addOnu($interface, $serial, $ontType, $pppoeUsername);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONT's LAN service
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $portInface  Port interface. Example: 'NA-NA-NA-1'
     * @param  LanConfig  $config  LAN service configuration parameters
     * @return Collection Info about each ONT configuration
     */
    public function configureLanOnts(string $portInterface, LanConfig $config): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::cfgLanPortVlan($interface, $serial, $portInterface, $config);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONT's VEIP service
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  string  $portInterface  Port interface. Example: 'NA-NA-NA-1'
     * @param  VeipConfig  $config  VEIP service configuration parameters
     * @return Collection Info about each ONT configuration
     */
    public function configureVeipOnts(string $portInterface, VeipConfig $config): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::cfgVeipService($interface, $serial, $portInterface, $config);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONT's WAN service
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @param  WanConfig  $config  WAN service configurations parameters
     * @return Collection Info about each ONT configuration
     */
    public function configureWanOnts(WanConfig $config): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::setWanService($interface, $serial, $config);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Remove/Deletes ONT's
     *
     * Parameters 'interfaces' and 'serials' must already be provided
     *
     * @return Collection Info about each ONT delete result
     */
    public function removeOnts(): ?Collection
    {
        $this->validateInterfacesSerials();
        $this->validateTL1();

        if (self::$model !== 'AN551604') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$interfaces); $i++) {
            $interface = self::$interfaces[$i];
            $serial = self::$serials[$i];

            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = AN551604::delOnu($interface, $serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }
}
