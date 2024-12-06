<?php

namespace PauloHortelan\Onmt\Services\Nokia;

use Exception;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\Services\Concerns\Assertations;
use PauloHortelan\Onmt\Services\Concerns\Validations;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;

class NokiaService
{
    use Assertations, Validations;

    protected static Telnet $telnetConn;

    protected static TL1 $tl1Conn;

    protected static string $model = 'FX16';

    protected int $connTimeout = 5;

    protected int $streamTimeout = 4;

    protected static string $ipOlt;

    public static array $serials = [];

    public static array $interfaces = [];

    public function connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null): object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('Provided IP(s) are not valid(s).');
        }

        self::$ipOlt = $ipOlt;
        self::$telnetConn = Telnet::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Nokia-'.self::$model);
        self::$telnetConn->stripPromptFromBuffer(true);
        self::$telnetConn->exec('environment inhibit-alarms');

        return $this;
    }

    public function connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null): object
    {
        $ipServer = empty($ipServer) ? $ipOlt : $ipServer;

        if (! $this->isValidIP($ipOlt) || ! $this->isValidIP($ipServer)) {
            throw new Exception('OLT brand does not match the service.');
        }

        self::$ipOlt = $ipOlt;
        self::$tl1Conn = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Nokia-'.self::$model);
        self::$tl1Conn->stripPromptFromBuffer(true);

        return $this;
    }

    public function disconnect(): void
    {
        if (empty(self::$telnetConn) && empty(self::$tl1Conn)) {
            throw new Exception('No connection established.');
        }

        self::$telnetConn->destroy();
        self::$tl1Conn->destroy();
    }

    public function model(string $model): object
    {
        self::$model = $model;

        return $this;
    }

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

    private function validateInterfaces()
    {
        if (empty(self::$interfaces) || count(array_filter(self::$interfaces)) < count(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }
    }

    private function validateSerials()
    {
        if (empty(self::$serials) || count(array_filter(self::$serials)) < count(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }
    }

    public function ontsDetail(): ?array
    {
        $this->validateInterfaces();

        if (self::$model === 'FX16') {
            return FX16::showEquipmentOntOptics();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function ontsDetailBySerials(): ?array
    {
        $this->validateSerials();

        $ontsDetail = [];
        $serials = self::$serials;

        var_dump($serials);

        foreach ($serials as $serial) {
            $this->serials([$serial]);
            $interfaceResponse = $this->ontsInterface()[0];

            if ($interfaceResponse['success']) {
                $interface = $interfaceResponse['result']['interface'];
                $this->interfaces([$interface]);

                $ontsDetail[] = $this->ontsDetail()[0];
            } else {
                $ontsDetail[] = $interfaceResponse;
            }
        }

        return $ontsDetail;
    }

    public function ontsInterface(): ?array
    {
        $this->validateSerials();

        if (self::$model === 'FX16') {
            return FX16::showEquipmentOntIndex();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function ontsPortDetail(): ?array
    {
        $this->validateInterfaces();

        if (self::$model === 'FX16') {
            return FX16::showInterfacePort();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function unregisteredOnts(): ?array
    {
        if (self::$model === 'FX16') {
            return FX16::showPonUnprovisionOnu();
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function ontsByPonInterface(string $ponInterface): ?array
    {
        if (empty($ponInterface)) {
            throw new Exception('Pon Interface(s) not provided.');
        }

        if (self::$model === 'FX16') {
            return FX16::showEquipmentOntStatusPon($ponInterface);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    public function getNextOntIndex(string $ponInterface): ?int
    {
        if (empty($ponInterface)) {
            throw new Exception('PON Interface not provided.');
        }

        $response = $this->ontsByPonInterface($ponInterface);

        if ($response['success'] === false) {
            throw new Exception('Provided PON Interface is not valid.');
        }

        $onts = $response['result'];

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

    public function removeOnts(): ?array
    {
        $this->validateInterfaces();

        if (self::$model === 'FX16') {
            $ontsResponse = [];
            $interfaces = [];

            $ontsStateDown = FX16::configureEquipmentOntInterfaceAdminState('down');

            foreach ($ontsStateDown as $ontStateDown) {
                if ($ontStateDown['success'] === true) {
                    $interfaces[] = $ontStateDown['result']['interface'];
                } else {
                    $ontsResponse = array_merge($ontsResponse, $ontsStateDown);
                }
            }

            $this->interfaces($interfaces);

            if (! empty($interfaces)) {
                $removedOnts = FX16::configureEquipmentOntNoInterface();
                $ontsResponse = array_merge($ontsResponse, $removedOnts);
            }

            return $ontsResponse;
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Provision ONT's
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EntOntConfig  $config  Provision configuration parameters
     * @return array Info about each ONT provision
     */
    public function provisionOnts(EntOntConfig $config): ?array
    {
        if (self::$model === 'FX16') {
            return FX16::entOnts($config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Edit provisioned ONT's
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EdOntConfig  $config  Provision configuration parameters
     * @return array Info about each ONT provision
     */
    public function editProvisionedOnts(EdOntConfig $config): ?array
    {
        if (self::$model === 'FX16') {
            return FX16::edOnts($config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }

    /**
     * Plan ONT card to ONT's
     *
     * Parameter 'interfaces' must already be provided
     *
     * @param  EntOntCardConfig  $config  ONT card configuration parameters
     * @return array Info about each ONT planned
     */
    public function planOntCardToOnts(EntOntCardConfig $config): ?array
    {
        if (self::$model === 'FX16') {
            return FX16::entOntsCard($config);
        }

        throw new Exception('Model '.self::$model.' is not supported.');
    }
}
