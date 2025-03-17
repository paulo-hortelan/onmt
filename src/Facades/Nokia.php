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
 * @method static self setOperator(string $operator) Set the operator name for the current session.
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Start recording commands in a batch with optional context information.
 * @method static CommandResultBatch stopRecordingCommands() Stop recording commands and return the completed batch.
 * @method static Collection|null executeCommand(string $command) Execute a custom command on the OLT and return the results.
 * @method static Collection|null detailOnts() Retrieve detailed information about ONTs including optical parameters. Parameter 'interfaces' must already be provided.
 * @method static Collection|null detailOntsBySerials() Retrieve detailed information about ONTs by their serial numbers. Parameter 'serials' must already be provided.
 * @method static Collection|null interfaceOnts() Get ONT interface information by serial numbers. Parameter 'serials' must already be provided.
 * @method static Collection|null interfaceOntsDetail() Get detailed information about ONT interfaces. Parameter 'interfaces' must already be provided.
 * @method static Collection|null swDownloadDetailOnts() Get software download status and details for ONTs. Parameter 'interfaces' must already be provided.
 * @method static Collection|null portDetailOnts() Get detailed information about ONT ports. Parameter 'interfaces' must already be provided.
 * @method static Collection|null unregisteredOnts() Get a list of all unregistered (discovered but not provisioned) ONTs on the OLT.
 * @method static Collection|null ontsByPonInterface(string $ponInterface) Get a list of all ONTs on the specified PON interface with their details.
 * @method static int|null getNextOntIndex(string $ponInterface) Find the next available ONT index on the specified PON interface.
 * @method static Collection|null removeOnts() Remove ONTs from the OLT. Parameter 'interfaces' must already be provided.
 * @method static Collection|null provisionOnts(EntOntConfig $config) Provision new ONTs with the specified configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null editProvisionedOnts(EdOntConfig $config) Edit already provisioned ONTs with the new configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null planOntsCard(EntOntCardConfig $config) Plan ONT card with the specified configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null createLogicalPortOnLT(EntLogPortConfig $config) Create a logical port on a Line Termination with the specified configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null editVeipOnts(EdOntVeipConfig $config) Edit Virtual Ethernet Interface Point on ONTs with the specified configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureUpstreamQueue(QosUsQueueConfig $config) Configure Quality of Service upstream queues with the specified parameters. Parameter 'interfaces' must already be provided.
 * @method static Collection|null boundBridgePortToVlan(VlanPortConfig $config) Bind a bridge port to a VLAN with the specified configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null addEgressPortToVlan(VlanEgPortConfig $config) Add an egress port to a VLAN with the specified configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Vlan(int $vlan = 110, int $sParamId = 1) Configure TR-069 VLAN settings with optional custom parameters. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Pppoe(string $username, string $password, int $sParamIdUsername = 2, int $sParamIdPassword = 3) Configure TR-069 PPPoE authentication credentials. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Wifi2_4Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 4, int $sParamIdPreSharedKey = 5) Configure TR-069 WiFi 2.4GHz settings including SSID and password. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069Wifi5Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 6, int $sParamIdPreSharedKey = 7) Configure TR-069 WiFi 5GHz settings including SSID and password. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069WebAccountPassword(string $password, int $sParamId = 8) Configure TR-069 web account password for ONT management. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069AccountPassword(string $password, int $sParamId = 9) Configure TR-069 account password for ONT management. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureTr069DNS(string $dns, int $sParamIdLan = 12, int $sParamIdWan = 13, int $sParamIdWan2 = 14) Configure TR-069 DNS settings for LAN and WAN interfaces. Parameter 'interfaces' must already be provided.
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
