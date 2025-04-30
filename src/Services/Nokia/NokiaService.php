<?php

namespace PauloHortelan\Onmt\Services\Nokia;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\HguTr069SparamConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\QosUsQueueConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanEgPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\NokiaTrait;
use PauloHortelan\Onmt\Services\Concerns\ValidationsTrait;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;

class NokiaService
{
    use Assertations, NokiaTrait, ValidationsTrait;

    protected static ?Telnet $telnetConn = null;

    protected static ?TL1 $tl1Conn = null;

    protected static string $model;

    protected static ?string $operator;

    protected int $connTimeout = 10;

    protected int $streamTimeout = 10;

    protected static string $ipOlt = '';

    protected array $supportedModels = ['FX16'];

    public static array $serials = [];

    public static array $interfaces = [];

    private ?CommandResultBatch $globalCommandBatch = null;

    private static bool $databaseTransactionsDisabled = false;

    public function connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'FX16'): object
    {
        if (self::$tl1Conn !== null) {
            self::$tl1Conn->destroy();
            self::$tl1Conn = null;
        }

        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        $this->validateIPs($ipOlt, $ipServer);

        $this->validateModel($model, $this->supportedModels);

        self::$ipOlt = $ipOlt;
        self::$model = $model;
        self::$operator = config('onmt.default_operator');

        self::$telnetConn = Telnet::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout);
        self::$telnetConn->stripPromptFromBuffer(true);
        self::$telnetConn->authenticate($username, $password, 'Nokia-'.self::$model);
        $this->inhibitAlarms();

        return $this;
    }

    public function connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'FX16'): object
    {
        if (self::$telnetConn !== null) {
            self::$telnetConn->destroy();
            self::$telnetConn = null;
        }

        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        self::$ipOlt = $ipOlt;
        self::$model = $model;
        self::$operator = config('onmt.default_operator');

        self::$tl1Conn = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout);
        self::$tl1Conn->stripPromptFromBuffer(true);
        self::$tl1Conn->authenticate($username, $password, 'Nokia-'.self::$model);
        $this->inhibitAlarms();

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

    public function inhibitAlarms(): ?CommandResult
    {
        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        if (isset(self::$telnetConn)) {
            return FX16::environmentInhibitAlarms();
        }

        if (isset(self::$tl1Conn)) {
            return FX16::inhMsgAll();
        }

        throw new Exception('No connection established.');
    }

    public function enableDebug(): void
    {
        if (isset(self::$telnetConn)) {
            self::$telnetConn->enableDebug();

            return;
        }

        if (isset(self::$tl1Conn)) {
            self::$tl1Conn->enableDebug();

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
    public function timeout(int $connTimeout, int $streamTimeout): object
    {
        $this->connTimeout = $connTimeout;
        $this->streamTimeout = $streamTimeout;

        return $this;
    }

    public function interfaces(array $interfaces): object
    {
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

    private function validateTL1(): void
    {
        if (empty(self::$tl1Conn)) {
            throw new Exception('TL1 connection not established.');
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

    /**
     * Executes the given command
     *
     * @param  string  $command  Command
     * @return Collection A collection of CommandResultBatch
     */
    public function executeCommand(string $command): ?Collection
    {
        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();
        $batchCreatedHere = false;

        $commandResultBatch = $this->globalCommandBatch ?? null;
        if ($commandResultBatch === null) {
            $batchCreatedHere = true;
            $commandResultBatch = $this->createCommandResultBatch([
                'ip' => self::$ipOlt,
                'operator' => self::$operator,
            ]);
        }

        if (! empty(self::$telnetConn)) {
            $response = FX16::executeCommandTelnet($command);
        } else {
            $response = FX16::executeCommandTL1($command);
        }

        $response->associateBatch($commandResultBatch);

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if (! self::$databaseTransactionsDisabled) {
                $commandResultBatch->save();
            }
            $commandResultBatch->load('commands');
        } else {
            $commandResultBatch->load('commands');
        }

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
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Reboot ONTs',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::adminEquipmentOntInterfaceRebootWithActiveImage($interface);

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
     * Reboot ONTs by serial - Telnet
     *
     * Parameter 'serials' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function rebootOntsBySerials(): ?Collection
    {
        $this->validateSerials();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;

            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Reboot ONTs by serial',
                    'ip' => self::$ipOlt,
                    'serial' => $serial,
                    'operator' => self::$operator,
                ]);
            }

            $response1 = FX16::showEquipmentOntIndex($serial);
            $interface = $response1->result['interface'] ?? null;

            if (empty($interface)) {
                $response1->associateBatch($commandResultBatch);

                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }

                $commandResultBatch->load('commands');

                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response2 = FX16::adminEquipmentOntInterfaceRebootWithActiveImage($interface);

            $response1->associateBatch($commandResultBatch);
            $response2->associateBatch($commandResultBatch);

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
     * Gets ONTs detail - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function detailOnts(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Get ONTs detail',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::showEquipmentOntOptics($interface);

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
     * Gets ONTs alarms - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function alarmsOnts(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Get ONTs alarm',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::showAlarmQueryOntPloam($interface);

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
     * Gets ONTs detail by serials - Telnet
     *
     * Parameter 'serials' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function detailOntsBySerials(): ?Collection
    {
        $this->validateSerials();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Get ONTs detail by serials',
                    'ip' => self::$ipOlt,
                    'serial' => $serial,
                    'operator' => self::$operator,
                ]);
            }

            $response1 = FX16::showEquipmentOntIndex($serial);
            $interface = $response1->result['interface'] ?? null;

            if (empty($interface)) {
                $response1->associateBatch($commandResultBatch);

                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if (! self::$databaseTransactionsDisabled) {
                        $commandResultBatch->save();
                    }
                }

                $commandResultBatch->load('commands');
                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response2 = FX16::showEquipmentOntOptics($interface);

            $response1->associateBatch($commandResultBatch);
            $response2->associateBatch($commandResultBatch);

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
     * Gets ONTs interface by serials - Telnet
     *
     * Parameter 'serials' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function interfaceOnts(): ?Collection
    {
        $this->validateSerials();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Get ONTs interface by serial',
                    'ip' => self::$ipOlt,
                    'serial' => $serial,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::showEquipmentOntIndex($serial);

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
     * Gets ONTs interface detail - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function interfaceOntsDetail(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Gets ONTs interface detail',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::showEquipmentOntInterface($interface);

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
     * Gets ONTs software download detail - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function swDownloadDetailOnts(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Gets ONTs software download details',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::showEquipmentOntSwDownload($interface);

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
     * Gets ONTs port detail - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function portDetailOnts(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'description' => 'Gets ONts port detail',
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::showInterfacePortOnt($interface);

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
     * Gets the unregistered ONTs - Telnet
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function unregisteredOnts(): ?Collection
    {
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();
        $batchCreatedHere = false;

        $commandResultBatch = $this->globalCommandBatch ?? null;
        if ($commandResultBatch === null) {
            $batchCreatedHere = true;
            $commandResultBatch = $this->createCommandResultBatch([
                'description' => 'List Unregistered ONTs',
                'ip' => self::$ipOlt,
                'operator' => self::$operator,
            ]);
        }

        $response = FX16::showPonUnprovisionOnu();

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
     * Gets ONTs detail by PON interface - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1/1'
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsByPonInterface(string $ponInterface): ?Collection
    {
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();
        $batchCreatedHere = false;

        $commandResultBatch = $this->globalCommandBatch ?? null;
        if ($commandResultBatch === null) {
            $batchCreatedHere = true;
            $commandResultBatch = $this->createCommandResultBatch([
                'description' => 'Gets ONTs detail by PON interface',
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'operator' => self::$operator,
            ]);
        }

        $response = FX16::showEquipmentOntStatusPon($ponInterface);

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
     * Gets the next free ONT index - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1/1'
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

        $lastSegments = array_map(function ($item) {
            $parts = explode('/', $item['interface']);

            return (int) end($parts);
        }, $onts);

        $nextPosition = 1;
        foreach ($lastSegments as $segment) {
            if ($segment != $nextPosition) {
                return $nextPosition;
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
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Remove ONTs',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response1 = FX16::configureEquipmentOntInterfaceAdminState($interface, 'down');
            $response2 = FX16::configureEquipmentOntNoInterface($interface);

            $response1->associateBatch($commandResultBatch);
            $response2->associateBatch($commandResultBatch);

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
     * Provision ONTs - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EntOntConfig  $config  Provision configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function provisionOnts(EntOntConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::entOnt($interface, $config);

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
     * Edit provisioned ONTs - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EdOntConfig  $config  Provision configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function editProvisionedOnts(EdOntConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::edOnt($interface, $config);

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
     * Plan ONT card - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EntOntCardConfig  $config  ONT card configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function planOntsCard(EntOntCardConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::entOntsCard($interface, $config);

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
     * Creates a logical port on an LT - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EntLogPortConfig  $config  Logical port configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function createLogicalPortOnLT(EntLogPortConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::entLogPort($interface, $config);

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
     * Edit the VEIP on ONTs - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EdOntVeipConfig  $config  ONT VEIP configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function editVeipOnts(EdOntVeipConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::edOntVeip($interface, $config);

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
     * Configures an upstream queue - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  QosUsQueueConfig  $config  QOS us queue configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function configureUpstreamQueue(QosUsQueueConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::setQosUsQueue($interface, $config);

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
     * Bounds a bridge port to the VLAN - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  VlanPortConfig  $config  VLAN port configuration parameters
     * @return Collection A collection of CommandResultBatch
     */
    public function boundBridgePortToVlan(VlanPortConfig $config): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::setVlanPort($interface, $config);

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
     * Adds a egress port to the VLAN - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  VlanEgPortConfig  $config  VLAN egress port configuration parameters
     * @param  string  $mode  TR069 mode (ENT, ED)
     * @return Collection A collection of CommandResultBatch
     */
    public function addEgressPortToVlan(VlanEgPortConfig $config, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::vlanEgPort($mode, $interface, $config);

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
     * Configures TR069 VLAN - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $vlan  VLAN value
     * @param  int  $sParamId  Parameter index
     * @param  string  $mode  TR069 mode (ENT, ED or DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Vlan(int $vlan = 110, int $sParamId = 1, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new HguTr069SparamConfig(
            paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.X_CT-COM_WANGponLinkConfig.VLANIDMark',
            paramValue: $vlan,
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::hguTr069Sparam($mode, $interface, $config);

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
     * Configures TR069 PPPOE username and password - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $username  PPPOE username
     * @param  string  $password  PPPOE password
     * @param  int  $sParamIdUsername  PPPOE username parameter index
     * @param  int  $sParamIdPassword  PPPOE password parameter index
     * @param  string  $mode  TR069 mode (ENT, ED or DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Pppoe(string $username, string $password, int $sParamIdUsername = 2, int $sParamIdPassword = 3, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
                paramValue: $username,
                sParamId: $sParamIdUsername
            ),
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password',
                paramValue: $password,
                sParamId: $sParamIdPassword
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $responses = collect($configs)->map(function ($config) use ($mode, $interface) {
                return FX16::hguTr069Sparam($mode, $interface, $config);
            });

            $responses->each(fn ($response) => $response->associateBatch($commandResultBatch));

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
     * Configures TR069 Wifi 2.4Ghz - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $ssid  SSID value
     * @param  string  $preSharedKey  Wifi password
     * @param  int  $sParamIdSsid  SSID parameter index
     * @param  int  $sParamIdPreSharedKey  Wifi password parameter index
     * @param  string  $mode  TR069 mode (ENT, ED or DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Wifi2_4Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 4, int $sParamIdPreSharedKey = 5, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                paramValue: $ssid,
                sParamId: $sParamIdSsid
            ),
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                paramValue: $preSharedKey,
                sParamId: $sParamIdPreSharedKey
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $responses = collect($configs)->map(function ($config) use ($mode, $interface) {
                return FX16::hguTr069Sparam($mode, $interface, $config);
            });

            $responses->each(fn ($response) => $response->associateBatch($commandResultBatch));

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
     * Configures TR069 Wifi 5Ghz - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $ssid  SSID value
     * @param  string  $preSharedKey  Wifi password
     * @param  int  $sParamIdSsid  SSID parameter index
     * @param  int  $sParamIdPreSharedKey  Wifi password parameter index
     * @param  string  $mode  TR069 mode (ENT, ED or DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Wifi5Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 6, int $sParamIdPreSharedKey = 7, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID',
                paramValue: $ssid,
                sParamId: $sParamIdSsid
            ),
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.PreSharedKey.1.PreSharedKey',
                paramValue: $preSharedKey,
                sParamId: $sParamIdPreSharedKey
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $responses = collect($configs)->map(function ($config) use ($mode, $interface) {
                return FX16::hguTr069Sparam($mode, $interface, $config);
            });

            $responses->each(fn ($response) => $response->associateBatch($commandResultBatch));

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
     * Configures TR069 Web Account Password - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $password  Password value
     * @param  int  $sParamId  Parameter index
     * @param  string  $mode  TR069 mode (ENT, ED or DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069WebAccountPassword(string $password, int $sParamId = 8, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new HguTr069SparamConfig(
            paramName: 'InternetGatewayDevice.X_Authentication.WebAccount.Password',
            paramValue: $password,
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::hguTr069Sparam($mode, $interface, $config);

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
     * Configures TR069 Account Password - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $password  Password value
     * @param  int  $sParamId  Parameter index
     * @param  string  $mode  TR069 mode (ENT, ED or DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069AccountPassword(string $password, int $sParamId = 9, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new HguTr069SparamConfig(
            paramName: 'InternetGatewayDevice.X_Authentication.Account.Password',
            paramValue: $password,
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::hguTr069Sparam($mode, $interface, $config);

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
     * Configures TR069 DNS's - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $dns  DNS value. Example '0.0.0.0\,1.1.1.1'
     * @param  int  $sParamIdLan  LAN parameter index
     * @param  int  $sParamIdWan  WAN parameter index
     * @param  int  $sParamIdWan2  WAN part 2 parameter index
     * @param  string  $mode  TR069 mode (ENT, ED OR DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069DNS(string $dns, int $sParamIdLan = 12, int $sParamIdWan = 13, int $sParamIdWan2 = 14, string $mode = 'ENT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.DNSServers',
                paramValue: $dns,
                sParamId: $sParamIdLan
            ),
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.DNSServers',
                paramValue: $dns,
                sParamId: $sParamIdWan
            ),
            new HguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.DNSServers',
                paramValue: $dns,
                sParamId: $sParamIdWan2
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $responses = collect($configs)->map(function ($config) use ($mode, $interface) {
                return FX16::hguTr069Sparam($mode, $interface, $config);
            });

            $responses->each(fn ($response) => $response->associateBatch($commandResultBatch));

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
     * Delete TR069 parameter - TL1
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $sParamId  WAN parameter index
     * @param  string  $mode  TR069 mode (DLT)
     * @return Collection A collection of CommandResultBatch
     */
    public function deleteTr069(int $sParamId, string $mode = 'DLT'): ?Collection
    {
        $this->validateTL1();
        $this->validateInterfaces();
        $this->validateMode($mode);

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new HguTr069SparamConfig(
            paramName: '',
            paramValue: '',
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? null;
            if ($commandResultBatch === null) {
                $batchCreatedHere = true;
                $commandResultBatch = $this->createCommandResultBatch([
                    'ip' => self::$ipOlt,
                    'description' => 'Configure TR069',
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }

            $response = FX16::hguTr069Sparam($mode, $interface, $config);

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
