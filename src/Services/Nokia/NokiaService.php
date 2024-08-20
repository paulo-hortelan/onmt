<?php

namespace PauloHortelan\Onmt\Services\Nokia;

use Exception;
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
        self::$telnetConn = Telnet::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Nokia-' . self::$model);
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
        self::$tl1Conn = TL1::getInstance($ipServer, $port, $this->connTimeout, $this->streamTimeout, $username, $password, 'Nokia-' . self::$model);
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
        if (empty(self::$interfaces)) {
            throw new Exception('Interface(s) not found.');
        }
    }

    private function validateSerials()
    {
        if (empty(self::$serials)) {
            throw new Exception('Serial(s) not found.');
        }
    }

    public function ontsDetail(): ?array
    {
        $this->validateInterfaces();

        if (self::$model === 'FX16') {
            return FX16::showEquipmentOntOptics();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function ontsInterface(): ?array
    {
        $this->validateSerials();

        if (self::$model === 'FX16') {
            return FX16::showEquipmentOntIndex();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function ontsDetailBySerials(): ?array
    {
        $this->validateSerials();

        $ontsDetail = [];
        $serials = self::$serials;

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

    public function ontsPortDetail(): ?array
    {
        $this->validateInterfaces();

        if (self::$model === 'FX16') {
            return FX16::showInterfacePort();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function unregisteredOnts(): ?array
    {
        if (self::$model === 'FX16') {
            return FX16::showPonUnprovisionOnu();
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function ontsByPonInterfaces(array $ponInterfaces): ?array
    {
        if (empty($ponInterfaces)) {
            throw new Exception("Pon Interface(s) not provided.");
        }

        if (self::$model === 'FX16') {
            return FX16::showEquipmentOntStatusPon($ponInterfaces);
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function getNextOntIndex(string $ponInterface): ?int
    {
        if (empty($ponInterface))
            throw new Exception('Pon Interface(s) not provided.');

        $onts = $this->ontsByPonInterfaces([$ponInterface]);

        $lastOntInterface = $onts[count($onts) - 1]['result']['interface'];
        $lastIndex = (int) array_slice(explode("/", $lastOntInterface), -1, 1)[0];

        return $lastIndex + 1;
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

            if (!empty($interfaces)) {
                $removedOnts = FX16::configureEquipmentOntNoInterface();
                $ontsResponse = array_merge($ontsResponse, $removedOnts);
            }

            return $ontsResponse;
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function provisionOnt(array $tid, array $ctag, array $ontNblk): ?array
    {
        if (count(self::$interfaces) !== 1 || count(self::$serials) !== 1) {
            throw new Exception('Number of interfaces and serials must be one.');
        }

        if (self::$model === 'FX16') {
            return FX16::entOnt($tid, $ctag, $ontNblk);
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }

    public function editProvisionOnts(array $pppoeUsernames, array $swVerPlnds, array $opticShists = [], array $plndCfgfiles1 = [], array $dlCfgfiles1 = [], array $voidAlloweds = []): ?array
    {
        $this->validateParameters([
            'pppoeUsernames' => $pppoeUsernames,
            'swVerPlnds' => $swVerPlnds,
        ]);

        if (! $this->assertSameLength([$pppoeUsernames, $swVerPlnds])) {
            throw new Exception('The number of parameters arrays are not the same.');
        }

        if (self::$model === 'FX16') {
            return FX16::entOnt($pppoeUsernames, $swVerPlnds, $opticShists, $plndCfgfiles1, $dlCfgfiles1, $voidAlloweds);
        }

        throw new Exception('Model ' . self::$model . ' is not supported.');
    }
}
