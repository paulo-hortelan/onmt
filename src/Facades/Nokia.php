<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureBridgePort;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureEquipmentOntInterface;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureEquipmentOntSlot;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureInterfacePort;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureQosInterface;
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
 * Nokia OLT Management Facade
 *
 * This facade provides an interface to manage Nokia OLTs (FX16 model)
 * through Telnet and TL1 connections.
 *
 * == CONNECTION MANAGEMENT ==
 *
 * @method static self connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'FX16') Establishes Telnet connection to Nokia OLT with specified credentials. The $ipServer parameter defaults to $ipOlt if not provided. The $model parameter defaults to 'FX16'.
 * @method static self connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'FX16') Establishes TL1 connection to Nokia OLT with specified credentials. TL1 is required for certain advanced provisioning commands. The $model parameter defaults to 'FX16'.
 * @method static void disconnect() Terminates the current Telnet or TL1 connection to the OLT.
 * @method static CommandResult|null inhibitAlarms() Disables alarm reporting on the OLT to prevent command execution interruptions.
 * @method static void enableDebug() Enables verbose debug output for the Telnet or TL1 session for troubleshooting purposes.
 * @method static void disableDebug() Disables debug output for the Telnet or TL1 session.
 *
 * == DATABASE TRANSACTION MANAGEMENT ==
 * @method static self enableDatabaseTransactions() Enables database transactions for batch and command saving (default behavior).
 * @method static self disableDatabaseTransactions() Disables database transactions for batch and command saving. Results will be created in memory only.
 *
 * == CONFIGURATION SETUP ==
 * @method static self model(string $model) Sets the OLT model (currently only 'FX16' is supported).
 * @method static self timeout(int $connTimeout, int $streamTimeout) Configures connection timeout (in seconds) and stream timeout (in seconds) for Telnet/TL1 operations.
 * @method static self interfaces(array $interfaces) Sets the ONU interfaces to operate on (format: ['1/1/1/1', '1/1/2/1']).
 * @method static self serials(array $serials) Sets the ONT serial numbers to operate on.
 * @method static self setOperator(string $operator) Sets the operator name for logging/tracking operations.
 *
 * == COMMAND RECORDING ==
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Starts recording all executed commands in a batch with optional metadata. Limited to single interface or serial.
 * @method static CommandResultBatch stopRecordingCommands() Stops command recording and returns the complete command batch results.
 * @method static Collection|null executeCommand(string $command) Executes a custom command directly on the OLT and returns the results. Useful for commands not covered by specific methods.
 *
 * == ONT INFORMATION RETRIEVAL ==
 * @method static Collection|null detailOnts() Retrieves detailed information about ONTs including optical parameters (TX/RX power levels). Parameter 'interfaces' must already be provided.
 * @method static Collection|null alarmsOnts() Retrieves detailed information about ONTs alarms. Parameter 'interfaces' must already be provided.
 * @method static Collection|null detailOntsBySerials() Retrieves detailed information about ONTs by serial numbers. Includes optical parameters and operational status. Parameter 'serials' must already be provided.
 * @method static Collection|null interfaceOnts() Gets ONT interface information (slot/port/ONT ID) by serial numbers. Parameter 'serials' must already be provided.
 * @method static Collection|null interfaceOntsDetail() Gets detailed configuration information about ONT interfaces including their service profiles. Parameter 'interfaces' must already be provided.
 * @method static Collection|null swDownloadDetailOnts() Gets software version information and download status for ONTs. Parameter 'interfaces' must already be provided.
 * @method static Collection|null portDetailOnts() Gets detailed information about ONT physical ports including their operational status. Parameter 'interfaces' must already be provided.
 * @method static Collection|null unregisteredOnts() Gets a list of all discovered but not yet provisioned ONTs on the OLT.
 * @method static Collection|null ontsByPonInterface(string $ponInterface) Gets a list of all ONTs on the specified PON interface (e.g., '1/1/1/1') with their status.
 *
 * == ONT MANAGEMENT ==
 * @method static int|null getNextOntIndex(string $ponInterface) Finds the next available ONT index for provisioning on the specified PON interface (e.g., '1/1/1/1').
 * @method static Collection|null removeOnts() Removes/deletes ONTs from the OLT configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null rebootOnts() Reboots ONTs and applies their current configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null rebootOntsBySerials() Reboots ONTs identified by their serial numbers. Parameter 'serials' must already be provided.
 *
 * == ONT CONFIGURATION (TELNET REQUIRED) ==
 * @method static Collection|null configureInterfaceOnts(ConfigureEquipmentOntInterface $config) Configures ONT interface parameters like description, admin state, etc. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureInterfaceAdminStateOnts(string $adminState) Sets the administrative state ('up' or 'down') for ONT interfaces. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureSlotOnts(ConfigureEquipmentOntSlot $config) Configures ONT slot parameters like card type and planned port types. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureQosInterfaces(ConfigureQosInterface $config) Configures Quality of Service (QoS) parameters for ONT interfaces. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureInterfacesPorts(ConfigureInterfacePort $config) Configures ONT port parameters like admin state and speed/duplex settings. Parameter 'interfaces' must already be provided.
 * @method static Collection|null configureBridgePorts(ConfigureBridgePort $config) Configures bridge port parameters like VLAN tagging and port associations. Parameter 'interfaces' must already be provided.
 *
 * == ONT PROVISIONING (TL1 REQUIRED) ==
 * @method static Collection|null provisionOnts(EntOntConfig $config) Creates new ONT entries on the OLT with the specified configuration settings. Parameter 'interfaces' must already be provided.
 * @method static Collection|null editProvisionedOnts(EdOntConfig $config) Modifies existing ONT configurations with new parameters. Parameter 'interfaces' must already be provided.
 * @method static Collection|null planOntsCard(EntOntCardConfig $config) Configures the ONT card type and port configuration. Parameter 'interfaces' must already be provided.
 * @method static Collection|null createLogicalPortOnLT(EntLogPortConfig $config) Creates a logical port on a Line Termination for service provisioning. Parameter 'interfaces' must already be provided.
 * @method static Collection|null editVeipOnts(EdOntVeipConfig $config) Configures Virtual Ethernet Interface Point settings for ONTs. Parameter 'interfaces' must already be provided.
 *
 * == QOS & VLAN CONFIGURATION (TL1 REQUIRED) ==
 * @method static Collection|null configureUpstreamQueue(QosUsQueueConfig $config) Configures upstream bandwidth profiles and scheduling for ONT traffic. Parameter 'interfaces' must already be provided.
 * @method static Collection|null boundBridgePortToVlan(VlanPortConfig $config) Associates a bridge port with a specific VLAN for traffic forwarding. Parameter 'interfaces' must already be provided.
 * @method static Collection|null addEgressPortToVlan(VlanEgPortConfig $config, string $mode = 'ENT') Adds an egress port to a VLAN configuration for outbound traffic. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 *
 * == TR-069 MANAGEMENT (TL1 REQUIRED) ==
 * @method static Collection|null configureTr069Vlan(int $vlan = 110, int $sParamId = 1, string $mode = 'ENT') Configures the VLAN ID used for TR-069 management traffic. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null configureTr069Pppoe(string $username, string $password, int $sParamIdUsername = 2, int $sParamIdPassword = 3, string $mode = 'ENT') Sets PPPoE credentials for TR-069 connectivity. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null configureTr069Wifi2_4Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 4, int $sParamIdPreSharedKey = 5, string $mode = 'ENT') Configures WiFi 2.4GHz network settings on the ONT via TR-069. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null configureTr069Wifi5Ghz(string $ssid, string $preSharedKey, int $sParamIdSsid = 6, int $sParamIdPreSharedKey = 7, string $mode = 'ENT') Configures WiFi 5GHz network settings on the ONT via TR-069. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null configureTr069WebAccountPassword(string $password, int $sParamId = 8, string $mode = 'ENT') Sets the web interface administrator password on the ONT via TR-069. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null configureTr069AccountPassword(string $password, int $sParamId = 9, string $mode = 'ENT') Sets the general account password on the ONT via TR-069. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null configureTr069DNS(string $dns, int $sParamIdLan = 12, int $sParamIdWan = 13, int $sParamIdWan2 = 14, string $mode = 'ENT') Configures DNS server addresses on the ONT via TR-069. Format: '0.0.0.0\,1.1.1.1'. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('ENT', 'ED' or 'DLT').
 * @method static Collection|null deleteTr069(int $sParamId, string $mode = 'DLT') Deletes TR-069 parameter on the ONTs. Parameter 'interfaces' must already be provided. The $mode parameter determines operation type ('DLT').
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
