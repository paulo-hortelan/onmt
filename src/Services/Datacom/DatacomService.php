<?php

namespace PauloHortelan\Onmt\Services\Datacom;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\DatacomTrait;
use PauloHortelan\Onmt\Services\Concerns\ValidationsTrait;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Datacom\Models\DM4612;

class DatacomService
{
    use Assertations, DatacomTrait, ValidationsTrait;

    protected static ?Telnet $telnetConn = null;

    protected static string $model;

    protected static ?string $operator = null;

    protected static $terminalMode;

    protected static int $connTimeout = 5;

    protected static int $streamTimeout = 4;

    protected static string $ipOlt = '';

    protected array $supportedModels = ['DM4612'];

    public static array $serials = [];

    public static array $interfaces = []; // Example: ['1/1/1/2']

    private ?CommandResultBatch $globalCommandBatch = null;

    private bool $useDatabaseTransactions = true;

    public function connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'DM4612'): object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        $this->validateIPs($ipOlt, $ipServer);

        $this->validateModel($model, $this->supportedModels);

        self::$ipOlt = $ipOlt;
        self::$model = $model;
        self::$terminalMode = '';
        self::$operator = config('onmt.default_operator');

        self::$telnetConn = Telnet::getInstance($ipServer, $port, self::$connTimeout, self::$streamTimeout);
        self::$telnetConn->stripPromptFromBuffer(true);
        self::$telnetConn->authenticate($username, $password, 'Datacom-'.self::$model);

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
        $this->useDatabaseTransactions = true;

        return $this;
    }

    /**
     * Disable database transactions for batch and command saving.
     */
    public function disableDatabaseTransactions(): self
    {
        $this->useDatabaseTransactions = false;

        return $this;
    }

    /**
     * Creates a CommandResult using create() or make() based on the useDatabaseTransactions setting
     *
     * @param  array  $attributes  The attributes to create the CommandResult with
     */
    protected static function createCommandResult(array $attributes): CommandResult
    {
        $callingClass = static::class;
        $instance = null;

        if ($callingClass !== self::class) {
            $instance = new $callingClass();
        }

        if ($instance && ! $instance->useDatabaseTransactions) {
            return CommandResult::make($attributes);
        } else {
            return CommandResult::create($attributes);
        }
    }

    /**
     * Creates a CommandResultBatch using create() or make() based on the useDatabaseTransactions setting
     *
     * @param  array  $attributes  The attributes to create the CommandResultBatch with
     */
    protected function createCommandResultBatch(array $attributes): CommandResultBatch
    {
        if ($this->useDatabaseTransactions) {
            return CommandResultBatch::create($attributes);
        } else {
            $batch = CommandResultBatch::make($attributes);
            $batch->inMemoryMode = true;

            if (! isset($batch->id)) {
                $batch->id = rand(1000, 9999);
            }

            return $batch;
        }
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
    public function timeout(int $connTimeout, int $streamTimeout): self
    {
        self::$connTimeout = $connTimeout;
        self::$streamTimeout = $streamTimeout;

        return $this;
    }

    public function interfaces(array $interfaces): object
    {
        $pattern = '/^\d+\/\d+\/\d+\/\d+$/';

        foreach ($interfaces as $interface) {
            if (! preg_match($pattern, $interface)) {
                throw new \InvalidArgumentException('Invalid interface format. Correct interface example: 1/1/1/1');
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
        if ((empty(self::$interfaces) || count(array_filter(self::$interfaces)) < count(self::$interfaces)) &&
            (empty(self::$serials) || count(array_filter(self::$serials)) < count(self::$serials))) {
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
        if (! in_array($terminalMode, ['config', 'interface-gpon', 'onu'])) {
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

        if ($this->useDatabaseTransactions) {
            $globalCommandBatch->save();
        }

        $this->globalCommandBatch = null;

        return $globalCommandBatch;
    }

    /**
     * Change terminal mode to default
     */
    public function setDefaultTerminalMode(): ?CommandResultBatch
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

        $response = DM4612::end();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = '';

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if ($this->useDatabaseTransactions) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Change terminal mode to 'config'
     */
    public function setConfigTerminalMode(): ?CommandResultBatch
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

        if (self::$terminalMode === '') {
            $response = DM4612::config();
        } else {
            $response = DM4612::top();
        }

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = 'config';

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if ($this->useDatabaseTransactions) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Change terminal mode to 'interface-gpon'
     */
    public function setInterfaceGponTerminalMode(string $ponInterface): ?CommandResultBatch
    {
        $batchCreatedHere = false;
        if (self::$terminalMode !== 'config') {
            $batchResponse = $this->setConfigTerminalMode();
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

        $response = DM4612::interfaceGpon($ponInterface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "interface-gpon-$ponInterface";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if ($this->useDatabaseTransactions) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Change or create terminal mode to 'onu'
     */
    public function setOnuTerminalMode(string $interface): ?CommandResultBatch
    {
        $ponInterface = $this->getPonInterfaceFromInterface($interface);
        $index = $this->getOntIndexFromInterface($interface);
        $batchCreatedHere = false;

        if (self::$terminalMode !== "interface-gpon-$ponInterface") {
            $batchResponse = $this->setInterfaceGponTerminalMode($ponInterface);
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
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }
        }

        $response = DM4612::onu($index);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "onu-$index";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if ($this->useDatabaseTransactions) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
    }

    /**
     * Change or create terminal mode to 'onu'
     */
    public function setEthernetTerminalMode(string $interface, int $port): ?CommandResultBatch
    {
        $ponInterface = $this->getPonInterfaceFromInterface($interface);
        $index = $this->getOntIndexFromInterface($interface);
        $batchCreatedHere = false;

        if (self::$terminalMode !== "onu-$index") {
            $batchResponse = $this->setOnuTerminalMode($interface);
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
                    'interface' => $interface,
                    'operator' => self::$operator,
                ]);
            }
        }

        $response = DM4612::ethernet($port);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "ethernet-$port";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if ($this->useDatabaseTransactions) {
                $commandResultBatch->save();
            }
        }

        return $commandResultBatch;
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

        $response = DM4612::showInterfaceGponDiscoveredOnus();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();

            if ($this->useDatabaseTransactions) {
                $commandResultBatch->save();
            }
        }
        $finalResponse->push($commandResultBatch);

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

            $response = DM4612::showInterfaceGponOnuInclude($serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONTs info - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsInfo(): ?Collection
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

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            $response = DM4612::showInterfaceGponOnu($ponInterface, $ontIndex);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }
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
    public function ontsAlarm(): ?Collection
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

            $response = DM4612::showAlarmInclude($interface);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }
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
    public function ontsReboot(): ?Collection
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

            if (self::$terminalMode !== 'config') {
                $batchResponse = $this->setConfigTerminalMode();

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();

                        if ($this->useDatabaseTransactions) {
                            $commandResultBatch->save();
                        }
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            $response = DM4612::interfaceGponOnuResetOnu($ponInterface, $ontIndex);
            $response->associateBatch($commandResultBatch);

            if (! $commandResultBatch->wasLastCommandSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();

                    if ($this->useDatabaseTransactions) {
                        $commandResultBatch->save();
                    }
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response = DM4612::yes();
            $response->associateBatch($commandResultBatch);

            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();

                if ($this->useDatabaseTransactions) {
                    $commandResultBatch->save();
                }
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    // Continue applying the same pattern to all remaining methods...
    // Follow the pattern established in FiberhomeService and NokiaService for all methods
}
