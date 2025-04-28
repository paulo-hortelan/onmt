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

        $this->disableDatabaseTransactions();
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
     * Disable database transactions for command results
     *
     * @return $this
     */
    public function disableDatabaseTransactions(): self
    {
        CommandResultBatch::disableDatabaseTransactions();
        CommandResult::disableDatabaseTransactions();

        return $this;
    }

    /**
     * Enable database transactions for command results
     *
     * @return $this
     */
    public function enableDatabaseTransactions(): self
    {
        CommandResultBatch::enableDatabaseTransactions();
        CommandResult::enableDatabaseTransactions();

        return $this;
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
        $this->globalCommandBatch =
            CommandResultBatch::create([
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
        $globalCommandBatch->save();

        $this->globalCommandBatch = null;

        return $globalCommandBatch;
    }

    /**
     * Change terminal mode to default
     */
    public function setDefaultTerminalMode(): ?CommandResultBatch
    {
        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = DM4612::end();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            return $commandResultBatch;
        }

        self::$terminalMode = '';

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
        }

        return $commandResultBatch;
    }

    /**
     * Change terminal mode to 'config'
     */
    public function setConfigTerminalMode(): ?CommandResultBatch
    {
        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
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
                $commandResultBatch->save();
            }

            return $commandResultBatch;
        }

        self::$terminalMode = 'config';

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        }

        $response = DM4612::interfaceGpon($ponInterface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "interface-gpon-$ponInterface";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        }

        $response = DM4612::onu($index);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "onu-$index";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'pon_interface' => $ponInterface,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }
        }

        $response = DM4612::ethernet($port);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if (! $commandResultBatch->wasLastCommandSuccessful()) {
            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            return $commandResultBatch;
        }

        self::$terminalMode = "ethernet-$port";

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
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
        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = DM4612::showInterfaceGponDiscoveredOnus();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = DM4612::showInterfaceGponOnuInclude($serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            $response = DM4612::showInterfaceGponOnu($ponInterface, $ontIndex);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $response = DM4612::showAlarmInclude($interface);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
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
                        $commandResultBatch->save();
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
                    $commandResultBatch->save();
                }
                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response = DM4612::yes();
            $response->associateBatch($commandResultBatch);

            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONTs service port - Telnet
     *
     * @return CommandResultBatch CommandResultBatch
     */
    public function ontsServicePort(): ?CommandResultBatch
    {
        $this->validateTelnet();
        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        if (! empty(self::$terminalMode)) {
            $batchResponse = $this->setDefaultTerminalMode();

            if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                $commandResultBatch = $batchResponse;
            } else {
                $commandResultBatch->associateCommands($batchResponse->commands);
            }

            if (! $commandResultBatch->allCommandsSuccessful()) {
                if ($batchCreatedHere) {
                    $commandResultBatch->finished_at = Carbon::now();
                    $commandResultBatch->save();
                }

                return $commandResultBatch;
            }
        }

        $response = DM4612::showRunningConfigServicePort();
        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
        }

        return $commandResultBatch;
    }

    /**
     * Gets ONTs service port by PON Interface - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1'
     * @return CommandResultBatch CommandResultBatch
     */
    public function ontsServicePortByPonInterface(string $ponInterface): ?CommandResultBatch
    {
        $this->validateTelnet();
        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'pon_interface' => $ponInterface,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = DM4612::showRunningConfigServicePortSelectGpon($ponInterface);
        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
        }

        return $commandResultBatch;
    }

    /**
     * Gets ONTs service port by PON Interface and ONT Index - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsServicePortByInterfaces(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            $response = DM4612::showRunningConfigServicePortSelectGponContextMatch($ponInterface, $ontIndex);
            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }
            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets the next free Service Port index - Telnet
     *
     * @return int The next available Service Port
     */
    public function getNextServicePort(): ?int
    {
        $this->validateTelnet();

        $commandResultBatch = $this->ontsServicePort();

        if (! $commandResultBatch->allCommandsSuccessful()) {
            throw new Exception('Provided PON Interface is not valid.');
        }

        $onts = $commandResultBatch->commands->last()['result'];

        $indexes = array_map(function ($item) {
            return $item['servicePortId'];
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
     * Gets ONTs by PON interface - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1'
     * @return CommandResultBatch CommandResultBatch
     */
    public function ontsByPonInterface(string $ponInterface): ?CommandResultBatch
    {
        $this->validateTelnet();
        $batchCreatedHere = false;
        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'pon_interface' => $ponInterface,
            'operator' => self::$operator,
        ]);
        if ($this->globalCommandBatch === null) {
            $batchCreatedHere = true;
        }

        $response = DM4612::showInterfaceGpon($ponInterface);
        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        if ($batchCreatedHere) {
            $commandResultBatch->finished_at = Carbon::now();
            $commandResultBatch->save();
        }

        return $commandResultBatch;
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

        $commandResultBatch = $this->ontsByPonInterface($ponInterface);

        if (! $commandResultBatch->allCommandsSuccessful()) {
            throw new Exception('Provided PON Interface is not valid.');
        }

        $onts = $commandResultBatch->commands[0]['result'];

        $indexes = array_map(function ($item) {
            return $item['onuId'];
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
     * Commit configurations - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function commitConfigurations(): ?Collection
    {
        $this->validateTelnet();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
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
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::commit();

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
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
    public function setName(string $name): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "onu-$ontIndex") {
                $batchResponse = $this->setOnuTerminalMode($interface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::name($name);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs serial number - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $serial  ONT serial number
     * @return Collection A collection of CommandResultBatch
     */
    public function setSerialNumber(string $serial): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "onu-$ontIndex") {
                $batchResponse = $this->setOnuTerminalMode($interface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::serialNumber($serial);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs SNMP profile - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $profile  ONT SNMP Profile
     * @return Collection A collection of CommandResultBatch
     */
    public function setSnmpProfile(string $profile): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "onu-$ontIndex") {
                $batchResponse = $this->setOnuTerminalMode($interface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::snmpProfile($profile);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs SNMP Real Time - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @return Collection A collection of CommandResultBatch
     */
    public function setSnmpRealTime(): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "onu-$ontIndex") {
                $batchResponse = $this->setOnuTerminalMode($interface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::snmpRealTime();

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs line profile - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  string  $profile  ONT Line Profile (PPPoE-Bridge, PPPoE-Router)
     * @return Collection A collection of CommandResultBatch
     */
    public function setLineProfile(string $profile): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "onu-$ontIndex") {
                $batchResponse = $this->setOnuTerminalMode($interface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::lineProfile($profile);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs VEIP - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $port  VEIP port
     * @return Collection A collection of CommandResultBatch
     */
    public function setVeip(int $port = 1): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "onu-$ontIndex") {
                $batchResponse = $this->setOnuTerminalMode($interface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::veip($port);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs Service Port - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $port  Service port
     * @param  int  $vlan  VLAN
     * @param  int  $description  Description
     * @return Collection A collection of CommandResultBatch
     */
    public function setServicePort(int $port, int $vlan, string $description): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
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
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            $response = DM4612::servicePort($port, $ponInterface, $ontIndex, $vlan, $description);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs Ethernet Negotiation - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $ethernetPort  Ethernet Port
     * @return Collection A collection of CommandResultBatch
     */
    public function setNegotiation(int $ethernetPort): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            if (self::$terminalMode !== "ethernet-$ethernetPort") {
                $batchResponse = $this->setEthernetTerminalMode($interface, $ethernetPort);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::negotiation();

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs Ethernet No Shutdown - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $ethernetPort  Ethernet Port
     * @return Collection A collection of CommandResultBatch
     */
    public function setNoShutdown(int $ethernetPort): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            if (self::$terminalMode !== "ethernet-$ethernetPort") {
                $batchResponse = $this->setEthernetTerminalMode($interface, $ethernetPort);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::noShutdown();

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Set ONTs Ethernet Native Vlan - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  int  $ethernetPort  Ethernet Port
     * @param  int  $vlan  VLAN
     * @return Collection A collection of CommandResultBatch
     */
    public function setNativeVlan(int $ethernetPort, int $vlan): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            if (self::$terminalMode !== "ethernet-$ethernetPort") {
                $batchResponse = $this->setEthernetTerminalMode($interface, $ethernetPort);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::nativeVlanVlanId($vlan);

            $commandResultBatch->associateCommand($response);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
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

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Remove ONTs',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "interface-gpon-$ponInterface") {
                $batchResponse = $this->setInterfaceGponTerminalMode($ponInterface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::noOnu($ontIndex);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Remove Service Ports - Telnet
     *
     * @param  array  $ports  Service Ports Indexes
     * @return Collection A collection of CommandResultBatch
     */
    public function removeServicePorts(array $ports): ?Collection
    {
        $this->validateTelnet();

        if (empty($ports)) {
            throw new Exception('Service Ports must be provided.');
        }

        $finalResponse = collect();

        foreach ($ports as $port) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Remove Service Ports',
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
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
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::noServicePort($port);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Remove ONTs and Service Ports - Telnet
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  array  $ports  Service Ports Indexes in the same order as the 'interfaces'
     * @return Collection A collection of CommandResultBatch
     */
    public function removeOntsServicePorts(array $ports): ?Collection
    {
        $this->validateTelnet();
        $this->validateInterfaces();

        if (empty($ports) || count($ports) !== count(self::$interfaces)) {
            throw new Exception('Invalid Ports');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $key => $interface) {
            $batchCreatedHere = false;
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Remove ONTs and Service Ports',
                'operator' => self::$operator,
            ]);
            if ($this->globalCommandBatch === null) {
                $batchCreatedHere = true;
            }

            $ponInterface = $this->getPonInterfaceFromInterface($interface);
            $ontIndex = $this->getOntIndexFromInterface($interface);

            if (self::$terminalMode !== "interface-gpon-$ponInterface") {
                $batchResponse = $this->setInterfaceGponTerminalMode($ponInterface);

                if ($batchCreatedHere && $batchResponse !== $commandResultBatch) {
                    $commandResultBatch = $batchResponse;
                } else {
                    $commandResultBatch->associateCommands($batchResponse->commands);
                }

                if (! $commandResultBatch->wasLastCommandSuccessful()) {
                    if ($batchCreatedHere) {
                        $commandResultBatch->finished_at = Carbon::now();
                        $commandResultBatch->save();
                    }
                    $finalResponse->push($commandResultBatch);

                    continue;
                }
            }

            $response = DM4612::noOnu($ontIndex);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $response = DM4612::noServicePort($ports[$key]);

            $response->associateBatch($commandResultBatch);

            if ($batchCreatedHere) {
                $commandResultBatch->finished_at = Carbon::now();
                $commandResultBatch->save();
            }

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }
}
