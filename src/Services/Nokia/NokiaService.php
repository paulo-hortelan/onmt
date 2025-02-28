<?php

namespace PauloHortelan\Onmt\Services\Nokia;

use Exception;
use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntHguTr069SparamConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\QosUsQueueConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanEgPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;

class NokiaService
{
    use Assertations, Validations;

    protected static ?Telnet $telnetConn = null;

    protected static ?TL1 $tl1Conn = null;

    protected static string $model = 'FX16';

    protected static ?string $operator;

    protected int $connTimeout = 5;

    protected int $streamTimeout = 4;

    protected static string $ipOlt = '';

    public static array $serials = [];

    public static array $interfaces = [];

    private ?CommandResultBatch $globalCommandBatch = null;

    public function connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null): object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('Provided IP(s) are not valid(s).');
        }

        self::$ipOlt = $ipOlt;
        self::$operator = config('onmt.default_operator');

        self::$telnetConn = Telnet::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout);
        self::$telnetConn->stripPromptFromBuffer(true);
        self::$telnetConn->authenticate($username, $password, 'Nokia-'.self::$model);
        $this->inhibitAlarms();

        return $this;
    }

    public function connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null): object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        self::$ipOlt = $ipOlt;
        self::$operator = config('onmt.default_operator');

        self::$tl1Conn = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout);
        self::$tl1Conn->stripPromptFromBuffer(true);
        self::$tl1Conn->authenticate($username, $password, 'Nokia-'.self::$model);
        $this->inhibitAlarms();

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

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        if (! empty(self::$telnetConn)) {
            $response = FX16::executeCommandTelnet($command);
        } else {
            $response = FX16::executeCommandTL1($command);
        }

        $response->associateBatch($commandResultBatch);
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
    public function ontsReboot(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Reboot ONTs',
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::adminEquipmentOntInterfaceRebootWithActiveImage($interface);

            $response->associateBatch($commandResultBatch);
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
    public function ontsRebootBySerials(): ?Collection
    {
        $this->validateSerials();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Reboot ONTs by serial',
                'ip' => self::$ipOlt,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = FX16::showEquipmentOntIndex($serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $interface = $response->result['interface'] ?? null;

            if (empty($interface)) {
                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response = FX16::adminEquipmentOntInterfaceRebootWithActiveImage($interface);

            $response->associateBatch($commandResultBatch);
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
    public function ontsDetail(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Get ONTs detail',
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::showEquipmentOntOptics($interface);

            $response->associateBatch($commandResultBatch);
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
    public function ontsDetailBySerials(): ?Collection
    {
        $this->validateSerials();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Get ONTs detail by serials',
                'ip' => self::$ipOlt,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = FX16::showEquipmentOntIndex($serial);

            $response->associateBatch($commandResultBatch);
            $commandResultBatch->load('commands');

            $interface = $response->result['interface'] ?? null;

            if (empty($interface)) {
                $finalResponse->push($commandResultBatch);

                continue;
            }

            $response = FX16::showEquipmentOntOptics($interface);

            $response->associateBatch($commandResultBatch);
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
    public function ontsInterface(): ?Collection
    {
        $this->validateSerials();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$serials as $serial) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Get ONTs interface by serial',
                'ip' => self::$ipOlt,
                'serial' => $serial,
                'operator' => self::$operator,
            ]);

            $response = FX16::showEquipmentOntIndex($serial);

            $response->associateBatch($commandResultBatch);
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
    public function ontsInterfaceDetail(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Gets ONTs interface detail',
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::showEquipmentOntInterface($interface);

            $response->associateBatch($commandResultBatch);
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
    public function ontsSwDownloadDetail(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Gets ONTs software download details',
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::showEquipmentOntSwDownload($interface);

            $response->associateBatch($commandResultBatch);
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
    public function ontsPortDetail(): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTelnet();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'description' => 'Gets ONts port detail',
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::showInterfacePort($interface);

            $response->associateBatch($commandResultBatch);
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

        $commandResultBatch = CommandResultBatch::create([
            'description' => 'List Unregistered ONTs',
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = FX16::showPonUnprovisionOnu();

        $response->associateBatch($commandResultBatch);
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

        $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
            'description' => 'Gets ONTs detail by PON interface',
            'ip' => self::$ipOlt,
            'operator' => self::$operator,
        ]);

        $response = FX16::showEquipmentOntStatusPon($ponInterface);

        $response->associateBatch($commandResultBatch);
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Remove ONTs',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::configureEquipmentOntInterfaceAdminState($interface, 'down');
            $response->associateBatch($commandResultBatch);

            $response = FX16::configureEquipmentOntNoInterface($interface);
            $response->associateBatch($commandResultBatch);

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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entOnt($interface, $config);

            $response->associateBatch($commandResultBatch);
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::edOnt($interface, $config);

            $response->associateBatch($commandResultBatch);
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entOntsCard($interface, $config);

            $response->associateBatch($commandResultBatch);
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entLogPort($interface, $config);
            $response->associateBatch($commandResultBatch);
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::edOntVeip($interface, $config);

            $response->associateBatch($commandResultBatch);
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
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::setQosUsQueue($interface, $config);
            $response->associateBatch($commandResultBatch);
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
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::setVlanPort($interface, $config);

            $response->associateBatch($commandResultBatch);
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
     * @return Collection A collection of CommandResultBatch
     */
    public function addEgressPortToVlan(VlanEgPortConfig $config): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entVlanEgPort($interface, $config);

            $response->associateBatch($commandResultBatch);
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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Vlan(int $vlan = 110, int $sParamId = 1): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new EntHguTr069SparamConfig(
            paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.X_CT-COM_WANGponLinkConfig.VLANIDMark',
            paramValue: $vlan,
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entHguTr069Sparam($interface, $config);

            $response->associateBatch($commandResultBatch);
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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Pppoe(string $username, string $password, int $sParamIdUsername = 2, int $sParamIdPassword = 3): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
                paramValue: $username,
                sParamId: $sParamIdUsername
            ),
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password',
                paramValue: $password,
                sParamId: $sParamIdPassword
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            collect($configs)->map(function ($config) use ($interface, $commandResultBatch) {
                $response = FX16::entHguTr069Sparam($interface, $config);

                $response->associateBatch($commandResultBatch);
                $commandResultBatch->load('commands');

                return $response;
            });

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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Wifi2_4Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 4, int $sParamIdPreSharedKey = 5): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                paramValue: $ssid,
                sParamId: $sParamIdSsid
            ),
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey',
                paramValue: $preSharedKey,
                sParamId: $sParamIdPreSharedKey
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            collect($configs)->map(function ($config) use ($interface, $commandResultBatch) {
                $response = FX16::entHguTr069Sparam($interface, $config);

                $response->associateBatch($commandResultBatch);
                $commandResultBatch->load('commands');

                return $response;
            });

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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069Wifi5Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 6, int $sParamIdPreSharedKey = 7): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID',
                paramValue: $ssid,
                sParamId: $sParamIdSsid
            ),
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.PreSharedKey.1.PreSharedKey',
                paramValue: $preSharedKey,
                sParamId: $sParamIdPreSharedKey
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            collect($configs)->map(function ($config) use ($interface, $commandResultBatch) {
                $response = FX16::entHguTr069Sparam($interface, $config);

                $response->associateBatch($commandResultBatch);
                $commandResultBatch->load('commands');

                return $response;
            });

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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069WebAccountPassword(string $password, int $sParamId = 8): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new EntHguTr069SparamConfig(
            paramName: 'InternetGatewayDevice.X_Authentication.WebAccount.Password',
            paramValue: $password,
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entHguTr069Sparam($interface, $config);

            $response->associateBatch($commandResultBatch);
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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069AccountPassword(string $password, int $sParamId = 9): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $config = new EntHguTr069SparamConfig(
            paramName: 'InternetGatewayDevice.X_Authentication.Account.Password',
            paramValue: $password,
            sParamId: $sParamId
        );

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            $response = FX16::entHguTr069Sparam($interface, $config);

            $response->associateBatch($commandResultBatch);
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
     * @return Collection A collection of CommandResultBatch
     */
    public function configureTr069DNS(string $dns, int $sParamIdLan = 12, int $sParamIdWan = 13, int $sParamIdWan2 = 14): ?Collection
    {
        $this->validateInterfaces();
        $this->validateTL1();

        if (self::$model !== 'FX16') {
            throw new Exception('Model '.self::$model.' is not supported.');
        }

        $configs = [
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.LANDevice.1.LANHostConfigManagemENT.DNSServers',
                paramValue: $dns,
                sParamId: $sParamIdLan
            ),
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.DNSServers',
                paramValue: $dns,
                sParamId: $sParamIdWan
            ),
            new EntHguTr069SparamConfig(
                paramName: 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_ALU_COM_TR69DNSServers',
                paramValue: $dns,
                sParamId: $sParamIdWan2
            ),
        ];

        $finalResponse = collect();

        foreach (self::$interfaces as $interface) {
            $commandResultBatch = $this->globalCommandBatch ?? CommandResultBatch::create([
                'ip' => self::$ipOlt,
                'description' => 'Configure TR069',
                'interface' => $interface,
                'operator' => self::$operator,
            ]);

            collect($configs)->map(function ($config) use ($interface, $commandResultBatch) {
                $response = FX16::entHguTr069Sparam($interface, $config);

                $response->associateBatch($commandResultBatch);
                $commandResultBatch->load('commands');

                return $response;
            });

            $finalResponse->push($commandResultBatch);
        }

        return $finalResponse;
    }
}
