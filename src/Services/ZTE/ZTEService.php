<?php

namespace PauloHortelan\Onmt\Services\ZTE;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\GemportConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServiceConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServicePortConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\SwitchportBindConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\ValidationsTrait;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\ZTE\Models\C300;
use PauloHortelan\Onmt\Services\ZTE\Models\C600;

class ZTEService
{
    use Assertations, ValidationsTrait;

    protected static ?Telnet $telnetConn = null;

    protected static string $model;

    protected static ?string $operator = null;

    protected static $terminalMode;

    protected int $connTimeout = 10;

    protected float $streamTimeout = 10;

    protected static string $ipOlt = '';

    protected array $supportedModels = ['C300', 'C600'];

    public static array $serials = [];

    public static array $interfaces = []; // Example: ['1/7/9:1']

    private ?CommandResultBatch $globalCommandBatch = null;

    private static bool $databaseTransactionsDisabled = false;

    public function connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'C300'): object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        $this->validateIPs($ipOlt, $ipServer);

        $this->validateModel($model, $this->supportedModels);

        self::$ipOlt = $ipOlt;
        self::$model = $model;
        self::$terminalMode = '';
        self::$operator = config('onmt.default_operator');

        self::$telnetConn = Telnet::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout);
        self::$telnetConn->stripPromptFromBuffer(true);
        self::$telnetConn->authenticate($username, $password, 'ZTE-'.self::$model);
        $this->disableTerminalLength();

        return $this;
    }

    public function disconnect(): void
    {
        if (self::$telnetConn === null) {
            throw new Exception('No connection established.');
        }

        if (self::$telnetConn !== null) {
            self::$telnetConn->destroy();
            self::$telnetConn = null;
        }

        self::$model = '';
        self::$operator = null;
        self::$terminalMode = '';
        self::$ipOlt = '';
        self::$serials = [];
        self::$interfaces = [];
        self::$databaseTransactionsDisabled = false;
    }

    public function disableTerminalLength(): ?CommandResult
    {
        $this->validateTelnet();

        if (self::$model === 'C300') {
            return C300::terminalLength0();
        }

        if (self::$model === 'C600') {
            return C600::terminalLength0();
        }

        throw new Exception('No connection established.');
    }

    public function enableDebug(): void
    {
        if (isset(self::$telnetConn)) {
            self::$telnetConn->enableDebug();

            return;
        }

        throw new Exception('No connection established.');
    }

    public function disableDebug(): void
    {
        if (isset(self::$telnetConn)) {
            self::$telnetConn->disableDebug();

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

    /**
     * Set the OLT model
     */
    public function model(string $model): object
    {
        self::$model = $model;

        return $this;
    }

    /**
     * Set the timeout
     */
    public function timeout(int $connTimeout, float $streamTimeout): object
    {
        $this->connTimeout = $connTimeout;
        $this->streamTimeout = $streamTimeout;

        return $this;
    }

    public function interfaces(array $interfaces): object
    {
        $pattern = '/^\d+\/\d+\/\d+:\d+$/';

        foreach ($interfaces as $interface) {
            if (! preg_match($pattern, $interface)) {
                throw new \InvalidArgumentException('Invalid interface format. Correct interface example: 1/1/1:1');
            }
        }

        self::$interfaces = $interfaces;

        return $this;
    }

    public function serials(array $serials): object
    {
        self::$serials = $serials;

        return $this;
    }

    private function validateTelnet(): void
    {
        if (empty(self::$telnetConn)) {
            throw new Exception('Telnet connection not established.');
        }
    }

    private function validateInterfaces(): void
    {
        if (empty(self::$interfaces) || count(array_filter(self::$interfaces)) < count(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }
    }

    private function validateSerials(): void
    {
        if (empty(self::$serials) || count(array_filter(self::$serials)) < count(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }
    }

    private function validateInterfacesSerials(): void
    {
        if (empty(self::$interfaces) || count(array_filter(self::$interfaces)) < count(self::$interfaces) &&
            empty(self::$serials) || count(array_filter(self::$serials)) < count(self::$serials)) {
            throw new Exception('Interface(s) and Serial(s) not found.');
        }
    }

    private function validateModels(array $models): void
    {
        if (! in_array(self::$model, $models)) {
            throw new Exception('Model '.self::$model.' does not support this operation.');
        }
    }

    private function validateTerminalMode(string $terminalMode): void
    {
        if (! in_array($terminalMode, ['configure', 'interface-olt', 'interface-onu', 'pon-onu-mng'])) {
            throw new Exception('Terminal mode '.$terminalMode.' is not supported.');
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

    /**
     * Change terminal mode to 'configure terminal'
     */
    public function setConfigureTerminalMode(): ?CommandResultBatch
    {
        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? null;
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
            $commandResultBatch = $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'operator' => self::$operator,
            ]);
        }

        $response = self::$model === 'C300' ? C300::end() : C600::end();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->allCommandsSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        $response = self::$model === 'C300' ? C300::configureTerminal() : C600::configureTerminal();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->allCommandsSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = 'configure';

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Enters gpon olt interface terminal mode
     */
    public function setInterfaceOltTerminalMode(string $ponInterface)
    {
        $batchCreatedHere = false;
        if (self::$terminalMode !== 'configure') {
            $batchResponse = $this->setConfigureTerminalMode();
            $commandResultBatch = $this->globalCommandBatch ?? $batchResponse;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        } else {
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'pon_interface' => $ponInterface,
                    'operator' => self::$operator,
                ]);
            }
        }

        $response = self::$model === 'C300' ? C300::interfaceGponOlt($ponInterface) : C600::interfaceGponOlt($ponInterface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->allCommandsSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "interface-olt-$ponInterface";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Enters gpon onu interface terminal mode
     */
    public function setInterfaceOnuTerminalMode(string $interface): ?CommandResultBatch
    {
        $batchCreatedHere = false;
        if (self::$terminalMode !== 'configure') {
            $batchResponse = $this->setConfigureTerminalMode();
            $commandResultBatch = $this->globalCommandBatch ?? $batchResponse;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        } else {
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }
        }

        $response = self::$model === 'C300' ? C300::interfaceGponOnu($interface) : C600::interfaceGponOnu($interface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->allCommandsSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "interface-onu-$interface";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Enters gpon onu interface terminal mode
     */
    public function setInterfaceVportTerminalMode(string $interface, int $vport): ?CommandResultBatch
    {
        $this->validateModels(['C600']);

        $batchCreatedHere = false;
        if (self::$terminalMode !== 'configure') {
            $batchResponse = $this->setConfigureTerminalMode();
            $commandResultBatch = $this->globalCommandBatch ?? $batchResponse;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        } else {
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }
        }

        $response = C600::interfaceVport($interface, $vport);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->allCommandsSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        $parts = explode(':', $interface);
        $ponInterface = $parts[0];
        $ontIndex = $parts[1];

        self::$terminalMode = "interface vport-$ponInterface.$ontIndex:$vport";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Enters pon onu mng terminal mode
     */
    public function setPonOnuMngTerminalMode(string $interface): ?CommandResultBatch
    {
        $batchCreatedHere = false;
        if (self::$terminalMode !== 'configure') {
            $batchResponse = $this->setConfigureTerminalMode();
            $commandResultBatch = $this->globalCommandBatch ?? $batchResponse;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        } else {
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }
        }

        $response = self::$model === 'C300' ? C300::ponOnuMng($interface) : C600::ponOnuMng($interface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->allCommandsSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "pon-onu-mng-$interface";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Gets ONTs optical power - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsOpticalPower(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = self::$model === 'C300'
                ? C300::showPonPowerAttenuation($interface)
                : C600::showPonPowerAttenuation($interface);

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
     * Gets ONTs interface by serial - Telnet
     *
     * Parameter 'serials' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function interfaceOnts(): ?Collection
    {
        $this->validateTelnet();
        $this->validateSerials();

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'serial' => $serial,
                    'operator' => self::$operator,
                ]);
            }

            $response = self::$model === 'C300'
                ? C300::showGponOnuBySn($serial)
                : C600::showGponOnuBySn($serial);

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
     * Gets ONTs alarm - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function alarmOnts(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = self::$model === 'C300'
                ? C300::showGponOnuDetailInfo($interface)
                : C600::showGponOnuDetailInfo($interface);

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
     * Gets ONTs detail info - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function detailOntsInfo(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = self::$model === 'C300'
                ? C300::showGponOnuDetailInfo($interface)
                : C600::showGponOnuDetailInfo($interface);

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
     * Gets unconfigured ONTs - Telnet
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function unconfiguredOnts(): ?Collection
    {
        $this->validateTelnet();

        $finalResponse = collect();

        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? null;
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
            $commandResultBatch = $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'operator' => self::$operator,
            ]);
        }

        $response = self::$model === 'C300'
            ? C300::showGponOnuUncfg()
            : C600::showGponOnuUncfg();

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
     * Gets ONTs interface running config - Telnet
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function interfaceOntsRunningConfig(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = self::$model === 'C300'
                ? C300::showRunningConfigInterfaceGponOnu($interface)
                : C600::showRunningConfigInterfaceGponOnu($interface);

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
     * Gets ONTs running config - Telnet
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsRunningConfig(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();
        $this->validateModels(['C300']);

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = C300::showOnuRunningConfigGponOnu($interface);

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
     * Gets ONTs by pon interface - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1'
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsByPonInterface(string $ponInterface): ?Collection
    {
        $this->validateTelnet();

        $finalResponse = collect();

        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? null;
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
            $commandResultBatch = $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'operator' => self::$operator,
            ]);
        }

        $response = self::$model === 'C300'
            ? C300::showGponOnuState($ponInterface)
            : C600::showGponOnuState($ponInterface);

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
     * Reboot ONTs - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function rebootOnts(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Reboot ONTs',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "pon-onu-mng-$interface") {
                $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::reboot() : C600::reboot();

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response = self::$model === 'C300' ? C300::yes() : C600::yes();

            $commandResultBatch->associateCommand($response);

            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets the next free ONT index - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1'
     * @return int The next ONT index
     */
    public function getNextOntIndex(string $ponInterface): ?int
    {
        $this->validateTelnet();

        $commandResultBatch = $this->ontsByPonInterface($ponInterface)->first();

        if (! $commandResultBatch->allCommandsSuccessful()) {
            throw new Exception('Provided PON Interface is not valid.');
        }

        $onts = $commandResultBatch->commands[0]['result'];

        $indexes = array_map(function ($item) {
            $parts = explode(':', $item['onu-index']);

            return (int) end($parts);
        }, $onts);

        sort($indexes);

        $nextPosition = 1;
        foreach ($indexes as $index) {
            if ($index !== $nextPosition) {
                break;
            }

            $nextPosition++;
        }

        return $nextPosition;
    }

    /**
     * Remove ONTs - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function removeOnts(): ?Collection
    {
        $this->validateTelnet();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $parts = explode(':', $interface);
            $ponInterface = $parts[0];
            $ontIndex = $parts[1];

            if (self::$terminalMode !== "interface-olt-$ponInterface") {
                $batchResponse = $this->setInterfaceOltTerminalMode($ponInterface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::noOnu($ontIndex) : C600::noOnu($ontIndex);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Provision ONTs by PON Interface - Telnet
     *
     * Parameter 'serials' must already be provided
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1'
     * @param  string  $ontIndex  ONT index
     * @param  string  $profile  Provision profile Example: 'ROUTER'
     * @return Collection A collection of CommandResultBatch
     */
    public function provisionOnts(string $ponInterface, int $ontIndex, string $profile): ?Collection
    {
        $this->validateTelnet();
        $this->validateSerials();

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'pon_interface' => $ponInterface,
                    'serial' => $serial,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "interface-olt-$ponInterface") {
                $batchResponse = $this->setInterfaceOltTerminalMode($ponInterface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300'
                ? C300::onuTypeSn($ontIndex, $profile, $serial)
                : C600::onuTypeSn($ontIndex, $profile, $serial);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs name - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $name  ONT name
     * @return Collection A collection of CommandResultBatch
     */
    public function setOntsName(string $name): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "interface-onu-$interface") {
                $batchResponse = $this->setInterfaceOnuTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::name($name) : C600::name($name);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs description - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $description  ONT description
     * @return Collection A collection of CommandResultBatch
     */
    public function setOntsDescription(string $description): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "interface-onu-$interface") {
                $batchResponse = $this->setInterfaceOnuTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::description($description) : C600::description($description);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs TCont - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $tcontId  T-CONT ID
     * @param  string  $profileName  Name of the bandwidth profile used by TCONT
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTCont(int $tcontId, string $profileName): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "interface-onu-$interface") {
                $batchResponse = $this->setInterfaceOnuTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::tcont($tcontId, $profileName) : C600::tcont($tcontId, $profileName);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs Gemport - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  GemportConfig  $gemportConfig  Gemport settings
     * @param  string  $terminalMode  Terminal mode to run: 'interface-onu', 'pon-onu-mng'
     * @return Collection A collection of CommandResultBatch
     */
    public function configureGemport(GemportConfig $gemportConfig, string $terminalMode): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();
        $this->validateTerminalMode($terminalMode);

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if ($terminalMode === 'interface-onu' && self::$terminalMode !== "interface-onu-$interface") {
                $batchResponse = $this->setInterfaceOnuTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            if ($terminalMode === 'pon-onu-mng' && self::$terminalMode !== "pon-onu-mng-$interface") {
                $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::gemport($gemportConfig) : C600::gemport($gemportConfig);
            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs Service Port - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  ServicePortConfig  $servicePortConfig  Service port settings
     * @param  int  $vport  Vport number, used for C600
     * @return Collection A collection of CommandResultBatch
     */
    public function configureServicePort(ServicePortConfig $servicePortConfig, ?int $vport = null): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$model === 'C300') {
                if (self::$terminalMode !== "interface-onu-$interface") {
                    $batchResponse = $this->setInterfaceOnuTerminalMode($interface);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }
            }

            if (self::$model === 'C600') {
                $parts = explode(':', $interface);
                $ponInterface = $parts[0];
                $ontIndex = $parts[1];

                if (self::$terminalMode !== "interface vport-$ponInterface.$ontIndex:$vport") {
                    $batchResponse = $this->setInterfaceVportTerminalMode($interface, $vport);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }
            }

            $response = self::$model === 'C300'
                ? C300::servicePort($servicePortConfig, $vport)
                : C600::servicePort($servicePortConfig, $vport);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configure ONTs Service - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  ServiceConfig  $serviceConfig  Service settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureService(ServiceConfig $serviceConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "pon-onu-mng-$interface") {
                $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::service($serviceConfig) : C600::service($serviceConfig);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configures a VLAN conversion rule - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  VlanPortConfig  $vlanPortConfig  Vlan port settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureVlanPort(VlanPortConfig $vlanPortConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$terminalMode !== "pon-onu-mng-$interface") {
                $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                if ($batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = self::$model === 'C300' ? C300::vlanPort($vlanPortConfig) : C600::vlanPort($vlanPortConfig);

            $commandResultBatch->associateCommand($response);

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configures flow mode - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  FlowModeConfig  $flowModeConfig  Flow mode settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureFlowMode(FlowModeConfig $flowModeConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$model === 'C300') {
                if (self::$terminalMode !== "pon-onu-mng-$interface") {
                    $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }

                $response = C300::flowMode($flowModeConfig);

                $commandResultBatch->associateCommand($response);

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Configures flow - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  FlowConfig  $flowConfig  Flow settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureFlow(FlowConfig $flowConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$model === 'C300') {
                if (self::$terminalMode !== "pon-onu-mng-$interface") {
                    $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }

                $response = C300::flow($flowConfig);

                $commandResultBatch->associateCommand($response);

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Binds the UNI with a layer 2 bridge - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  SwitchportBindConfig  $switchportBindConfig  Switchport-bind settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureSwitchportBind(SwitchportBindConfig $switchportBindConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$model === 'C300') {
                if (self::$terminalMode !== "pon-onu-mng-$interface") {
                    $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }

                $response = C300::switchportBind($switchportBindConfig);

                $commandResultBatch->associateCommand($response);

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Creates a VLAN filtering mode - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  VlanFilterModeConfig  $vlanFilterModeConfig  Vlan-filter-mode settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureVlanFilterMode(VlanFilterModeConfig $vlanFilterModeConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$model === 'C300') {
                if (self::$terminalMode !== "pon-onu-mng-$interface") {
                    $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }

                $response = C300::vlanFilterMode($vlanFilterModeConfig);

                $commandResultBatch->associateCommand($response);

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Creates a VLAN filtering item - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  VlanFilterConfig  $vlanFilterConfig  Vlan-filter-mode settings
     * @return Collection A collection of CommandResultBatch
     */
    public function configureVlanFilter(VlanFilterConfig $vlanFilterConfig): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            if (self::$model === 'C300') {
                if (self::$terminalMode !== "pon-onu-mng-$interface") {
                    $batchResponse = $this->setPonOnuMngTerminalMode($interface);

                    if ($batchResponse !== $commandResultBatch) {
                        $commandResultBatch = $batchResponse;
                    } else {
                        $commandResultBatch->associateCommands($batchResponse->commands);
                    }

                    if (! $commandResultBatch->allCommandsSuccessful()) {
                        if ($batchCreatedHere) {
                            $commandResultBatch->finished_at = Carbon::now();

                            if (! self::$databaseTransactionsDisabled) {
                                $commandResultBatch->save();
                            }
                        }
                        $finalResponse->push($commandResultBatch);

                        continue;
                    }
                }

                $response = C300::vlanFilter($vlanFilterConfig);

                $commandResultBatch->associateCommand($response);

                if (! $commandResultBatch->allCommandsSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if (! self::$databaseTransactionsDisabled) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if (! self::$databaseTransactionsDisabled) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }
}
