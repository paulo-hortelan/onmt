<?php

namespace PauloHortelan\Onmt\Services\Fiberhome;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\ValidationsTrait;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Fiberhome\Models\AN551604;

class FiberhomeService
{
    use Assertations, ValidationsTrait;

    protected static ?TL1 $tl1Conn = null;

    protected static ?Telnet $telnetConn = null;

    protected static string $model;

    protected static ?string $operator;

    protected int $connTimeout = 10;

    protected float $streamTimeout = 10;

    protected static string $ipOlt = '';

    protected array $supportedModels = ['AN5516-04', 'AN5516-06', 'AN5516-06B'];

    public static array $serials = [];

    public static array $interfaces = [];

    private ?CommandResultBatch $globalCommandBatch = null;

    protected static bool $databaseTransactionsDisabled = false;

    public function connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'AN5516-04'): ?object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        $this->validateIPs($ipOlt, $ipServer);

        $this->validateModel($model, $this->supportedModels);

        self::$ipOlt = $ipOlt;
        self::$model = $model;
        self::$operator = config('onmt.default_operator');

        self::$tl1Conn = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Fiberhome-'.self::$model);
        self::$tl1Conn->stripPromptFromBuffer(true);
        self::$tl1Conn->authenticate($username, $password, 'Fiberhome-'.self::$model);

        return $this;
    }

    public function disconnect(): void
    {
        if (self::$telnetConn === null && self::$tl1Conn === null) {
            throw new Exception('No connection established.');
        }

        if (self::$telnetConn !== null) {
            self::$telnetConn->destroy();
            self::$telnetConn = null;
        }

        if (self::$tl1Conn !== null) {
            self::$tl1Conn->destroy();
            self::$tl1Conn = null;
        }

        self::$model = '';
        self::$operator = null;
        self::$ipOlt = '';
        self::$serials = [];
        self::$interfaces = [];
        self::$databaseTransactionsDisabled = false;
    }

    public function timeout(int $connTimeout, float $streamTimeout): object
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

    /**
     * Enable database transactions for batch and command saving.
     */
    public function enableDatabaseTransactions(): self
    {
        self::$databaseTransactionsDisabled = false;

        return $this;
    }

    /**
     * Disable database transactions for batch and command saving.
     */
    public function disableDatabaseTransactions(): self
    {
        self::$databaseTransactionsDisabled = true;

        return $this;
    }

    /**
     * Creates a CommandResult using create() or make() based on the useDatabaseTransactions setting
     *
     * @param  array  $attributes  The attributes to create the CommandResult with
     * @param  array  $skipTransaction  Determine if the transaction should be skipped
     */
    protected static function createCommandResult(array $attributes, bool $skipTransaction = false): CommandResult
    {
        if (self::$databaseTransactionsDisabled) {
            $skipTransaction = true;
        }

        if ($skipTransaction) {
            return CommandResult::make($attributes);
        }

        return CommandResult::create($attributes);
    }

    /**
     * Creates a CommandResultBatch using create() or make() based on the useDatabaseTransactions setting
     *
     * @param  array  $attributes  The attributes to create the CommandResultBatch with
     * @param  array  $skipTransaction  Determine if the transaction should be skipped
     */
    protected function createCommandResultBatch(array $attributes, bool $skipTransaction = false): CommandResultBatch
    {
        if (self::$databaseTransactionsDisabled) {
            $skipTransaction = true;
        }

        if (! $skipTransaction) {
            return CommandResultBatch::create($attributes);
        }

        $batch = CommandResultBatch::make($attributes);

        $batch->inMemoryMode = true;

        if (! isset($batch->id)) {
            $batch->id = rand(1000, 9999);
        }

        return $batch;
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

    private function validateSerials()
    {
        if (empty(self::$serials) || count(array_filter(self::$interfaces)) < count(self::$interfaces)) {
            throw new Exception('Serial(s) not found.');
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

        $this->globalCommandBatch = $this->createCommandResultBatch([
            'ip' => self::$ipOlt,
            'description' => $description,
            'pon_interface' => $ponInterface,
            'interface' => $interface,
            'serial' => $serial,
            'operator' => self::$operator ?? $operator,
        ]);
    }

    public function stopRecordingCommands(): CommandResultBatch
    {
        if ($this->globalCommandBatch === null) {
            throw new Exception('The Record Commands has not started');
        }

        $globalCommandBatch = $this->globalCommandBatch;
        $globalCommandBatch->finished_at = Carbon::now();

        if (! self::$databaseTransactionsDisabled) {
            $globalCommandBatch->save();
        }

        $this->globalCommandBatch = null;

        return $globalCommandBatch;
    }

    private function validateModels(array $models): void
    {
        if (! in_array(self::$model, $models)) {
            throw new Exception('Model '.self::$model.' does not support this operation.');
        }
    }

    /**
     * Gets ONTs optical power
     *
     * Parameters 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Collection with info about each ONT power: 'rxPower', 'txPower'
     */
    public function ontsOpticalPower(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::lstOMDDM($ponInterface, $serial);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONTs state info
     *
     * Parameter 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Collection with info about each ONT state: 'adminState', 'oprState', 'auth', 'lastOffTime'
     */
    public function ontsStateInfo(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::lstOnuState($ponInterface, $serial);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONTs port info
     *
     * Parameter 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Collection with info about each ONT port: 'cVlan'
     */
    public function ontsPortInfo(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::lstPortVlan($ponInterface, $serial);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * List ONTs LAN Info
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Info about ONT LAN
     */
    public function ontsLanInfo(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::lstOnuLanInfo($ponInterface, $serial);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

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
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();
        $batchCreatedHere = false;

        $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = AN551604::lstLanPerf($portInterface);

        $response->associateBatch($commandResultBatch);

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * List unregistered ONTs
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Info about each unregistered ONT
     */
    public function unregisteredOnts(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();
        $batchCreatedHere = false;

        $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
            'ip' => self::$ipOlt,
            'pon_interface' => $ponInterface,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = AN551604::lstUnregOnu($ponInterface);

        $response->associateBatch($commandResultBatch);

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * List registered ONTs
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Info about each registered ONT
     */
    public function registeredOnts(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();
        $batchCreatedHere = false;

        $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
            'ip' => self::$ipOlt,
            'pon_interface' => $ponInterface,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = AN551604::lstOnu($ponInterface);

        $response->associateBatch($commandResultBatch);

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }

    /**
     * Authorize ONTs
     *
     * Parameter 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Info about each ONT authorization
     */
    public function rebootOnts(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::resetOnu($ponInterface, $serial);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Authorize ONTs
     *
     * Parameter 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @param  string  $ontType  ONTs type. Example: 'HG260'
     * @param  string  $pppoeUsername  PPPOE username.
     * @return Collection Info about each ONT authorization
     */
    public function authorizeOnts(string $ponInterface, string $ontType, string $pppoeUsername): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::addOnu($ponInterface, $serial, $ontType, $pppoeUsername);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs LAN service
     *
     * Parameters 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @param  string  $portInface  Port interface. Example: 'NA-NA-NA-1'
     * @param  LanConfig  $config  LAN service configuration parameters
     * @return Collection Info about each ONT configuration
     */
    public function configureLanOnts(string $ponInterface, string $portInterface, LanConfig $config): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::cfgLanPortVlan($ponInterface, $serial, $portInterface, $config);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs VEIP service
     *
     * Parameters 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @param  string  $portInterface  Port interface. Example: 'NA-NA-NA-1'
     * @param  VeipConfig  $config  VEIP service configuration parameters
     * @return Collection Info about each ONT configuration
     */
    public function configureVeipOnts(string $ponInterface, string $portInterface, VeipConfig $config): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::cfgVeipService($ponInterface, $serial, $portInterface, $config);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs WAN service
     *
     * Parameters 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @param  WanConfig  $config  WAN service configurations parameters
     * @return Collection Info about each ONT configuration
     */
    public function configureWanOnts(string $ponInterface, WanConfig $config): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::setWanService($ponInterface, $serial, $config);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Remove/Deletes ONTs
     *
     * Parameters 'serials' must already be provided
     *
     * @param  string  $ponInterface  ONT pon interface. Example: 'NA-NA-1-1'
     * @return Collection Info about each ONT delete result
     */
    public function removeOnts(string $ponInterface): ?Collection
    {
        $this->validateTL1();
        $this->validateSerials();
        $this->validateModels(['AN5516-04', 'AN5516-06', 'AN5516-06B']);

        $finalResponse = collect();

        for ($i = 0; $i < count(self::$serials); $i++) {
            $serial = self::$serials[$i];
            $batchCreatedHere = false;

            $commandResultBatch = $this->globalCommandBatch ?? $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => null,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = AN551604::delOnu($ponInterface, $serial);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }
}
