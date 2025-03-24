<?php

namespace PauloHortelan\Onmt\Services\Datacom;

use Exception;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\ValidationsTrait;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Datacom\Models\DM4612;

class DatacomService
{
    use Assertations, ValidationsTrait;

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
        if (isset(self::$telnetConn)) {
            self::$telnetConn->destroy();
            self::$telnetConn = null;
            self::$terminalMode = '';

            return;
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

    // private function validateTerminalMode(string $terminalMode): void
    // {
    //     if (! in_array($terminalMode, ['configure', 'interface-olt', 'interface-onu', 'pon-onu-mng'])) {
    //         throw new Exception('Terminal mode '.$terminalMode.' is not supported.');
    //     }
    // }

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

        $this->globalCommandBatch = null;

        return $globalCommandBatch;
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

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = DM4612::showInterfaceGponDiscoveredOnus();

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = DM4612::showInterfaceGponOnuInclude($serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = DM4612::showInterfaceGponOnu($interface);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }

    /**
     * Gets ONTs by PON interface - Telnet
     *
     * @param  string  $ponInterface  PON interface. Example: '1/1/1'
     * @return Collection A collection of CommandResultBatch
     */
    public function ontsByPonInterface(string $ponInterface): ?Collection
    {
        $this->validateTelnet();

        $finalResponse = collect();

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'pon_interface' => $ponInterface,
            'operator' => self::$operator,
        ]);

        $response = DM4612::showInterfaceGpon($ponInterface);

        $response->associateBatch($commandResultBatch);
        $commandResultBatch->load('commands');

        $finalResponse->push($commandResultBatch);

        return $finalResponse;
    }
}
