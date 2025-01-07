<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\QosUsQueueConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanEgPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;

/**
 * @method static self connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null) Connect to the OLT via Telnet.
 * @method static self connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null) Connect to the OLT via TL1.
 * @method static void disconnect() Disconnect from the OLT.
 * @method static CommandResult|null inhibitAlarms() Inhibit alarms on the OLT.
 * @method static void enableDebug() Enable debug mode for the Telnet or TL1 session.
 * @method static void disableDebug() Disable debug mode for the Telnet or TL1 session.
 * @method static self model(string $model) Set the OLT model.
 * @method static self timeout(int $connTimeout, int $streamTimeout) Set the Telnet or TL1 connection and stream timeouts.
 * @method static self interfaces(array $interfaces) Set the interfaces to be used for operations.
 * @method static self serials(array $serials) Set the ONT serials to be used for operations.
 * @method static self setOperator(string $operator) Set the operator for the current session.
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Start recording commands in a batch.
 * @method static CommandResultBatch stopRecordingCommands() Stop recording commands and return the batch.
 * @method static Collection|null executeCommand(string $command) Execute a command on the OLT.
 * @method static Collection|null ontsDetail() Get ONTs detail. Parameter 'interfaces' must already be provided.
 * @method static Collection|null ontsDetailBySerials() Get ONTs detail by serials. Parameter 'serials' must already be provided.
 * @method static Collection|null ontsInterface() Get ONTs interface by serials. Parameter 'serials' must already be provided.
 * @method static Collection|null ontsInterfaceDetail() Get ONTs interface detail. Parameter 'interfaces' must already be provided.
 * @method static Collection|null ontsSwDownloadDetail() Get ONTs software download detail. Parameter 'interfaces' must already be provided.
 * @method static Collection|null ontsPortDetail() Get ONTs port detail. Parameter 'interfaces' must already be provided.
 * @method static Collection|null unregisteredOnts() Get the unregistered ONTs.
 * @method static Collection|null ontsByPonInterface(string $ponInterface) Get ONTs detail by PON interface.
 * @method static int|null getNextOntIndex(string $ponInterface) Get the next free ONT index for a PON interface.
 * @method static Collection|null removeOnts() Remove ONTs. Parameter 'interfaces' must already be provided.
 * @method static Collection|null provisionOnts(EntOntConfig $config) Provision ONTs. Parameter 'interfaces' must already be provided.
 * @method static Collection|null editProvisionedOnts(EdOntConfig $config) Edit provisioned ONTs. Parameter 'interfaces' must already be provided.
 * @method static Collection|null planOntsCard(EntOntCardConfig $config) Plan ONT card. Parameter 'interfaces' must already be provided.
 * @method static Collection|null createLogicalPortOnLT(EntLogPortConfig $config) Create a logical port on an LT. Parameter 'interfaces' must already be provided.
 * @method static Collection|null editVeipOnts(EdOntVeipConfig $config) Edit the VEIP on ONTs. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureUpstreamQueue(QosUsQueueConfig $config) Configure an upstream queue. Parameter 'interfaces' must already be provided.
 * @method static Collection|null boundBridgePortToVlan(VlanPortConfig $config) Bind a bridge port to the VLAN. Parameter 'interfaces' must already be provided.
 * @method static Collection|null addEgressPortToVlan(VlanEgPortConfig $config) Add an egress port to the VLAN. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Vlan(int $vlan = 110, int $sParamId = 1) Configure TR069 VLAN. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Pppoe(string $username, string $password, int $sParamIdUsername = 2, int $sParamIdPassword = 3) Configure TR069 PPPOE username and password. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Wifi2_4Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 4, int $sParamIdPreSharedKey = 5) Configure TR069 Wifi 2.4Ghz. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Wifi5Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 6, int $sParamIdPreSharedKey = 7) Configure TR069 Wifi 5Ghz. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069WebAccountPassword(string $password, int $sParamId = 8) Configure TR069 Web Account Password. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069AccountPassword(string $password, int $sParamId = 9) Configure TR069 Account Password. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069DNS(string $dns, int $sParamIdLan = 12, int $sParamIdWan = 13, int $sParamIdWan2 = 14) Configure TR069 DNS's. Parameter 'interfaces' must already be provided.
 *
 * @see \PauloHortelan\Onmt\Services\Nokia\NokiaService
 */
class Nokia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\Nokia\NokiaService::class;
    }
}
