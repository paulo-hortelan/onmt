<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\GemportConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServiceConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServicePortConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\SwitchportBindConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanPortConfig;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;

/**
 * ZTE OLT Management Facade
 *
 * This facade provides an interface to manage ZTE OLTs (C300/C600 models)
 * through Telnet connections.
 *
 * == CONNECTION MANAGEMENT ==
 *
 * @method static self connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'C300') Establishes Telnet connection to ZTE OLT with specified credentials. The $ipServer parameter defaults to $ipOlt if not provided.
 * @method static void disconnect() Terminates the current Telnet connection and resets terminal mode.
 * @method static CommandResult|null disableTerminalLength() Disables terminal pagination (equivalent to "terminal length 0") to receive complete command output.
 * @method static void enableDebug() Enables verbose debug output for the Telnet session for troubleshooting.
 * @method static void disableDebug() Disables debug output for the Telnet session.
 *
 * == CONFIGURATION SETUP ==
 * @method static self model(string $model) Sets the OLT model (supported: 'C300', 'C600').
 * @method static self timeout(int $connTimeout, int $streamTimeout) Configures connection timeout (in seconds) and stream timeout (in seconds) for Telnet operations.
 * @method static self interfaces(array $interfaces) Sets the ONU interfaces to operate on (format: ['1/1/1:1', '1/2/3:4']).
 * @method static self serials(array $serials) Sets the ONT serial numbers to operate on.
 * @method static self setOperator(string $operator) Sets the operator name for logging/tracking operations.
 *
 * == COMMAND RECORDING ==
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Starts recording all executed commands in a batch with optional metadata.
 * @method static CommandResultBatch stopRecordingCommands() Stops command recording and returns the complete command batch results.
 *
 * == TERMINAL MODE MANAGEMENT ==
 * @method static CommandResultBatch|null setConfigureTerminalModel() Switches terminal to 'configure terminal' mode (equivalent to "conf t" command).
 * @method static CommandResultBatch|null setInterfaceOltTerminalModel(string $ponInterface) Enters GPON OLT interface configuration mode for specified PON interface (e.g., '1/1/1').
 * @method static CommandResultBatch|null setInterfaceOnuTerminalModel(string $interface) Enters GPON ONU interface configuration mode for specified ONU interface (e.g., '1/1/1:1').
 * @method static CommandResultBatch|null setInterfaceVportTerminalModel(string $interface, int $vport) Enters VPORT terminal mode for specified interface and virtual port (C600 model only).
 * @method static CommandResultBatch|null setPonOnuMngTerminalModel(string $interface) Enters PON ONU management terminal mode for deeper ONU configuration (e.g., '1/1/1:1').
 *
 * == ONT INFORMATION RETRIEVAL ==
 * @method static Collection|null ontsOpticalPower() Retrieves optical power levels (TX/RX) for all ONTs specified by interfaces().
 * @method static Collection|null interfaceOnts() Gets interface assignments for ONTs specified by serials().
 * @method static Collection|null detailOntsInfo() Retrieves comprehensive ONT information for interfaces specified by interfaces() (C300 model only).
 * @method static Collection|null unconfiguredOnts() Lists all unconfigured/unprovisioned ONTs detected by the OLT.
 * @method static Collection|null interfaceOntsRunningConfig() Retrieves running configuration for ONT interfaces specified by interfaces().
 * @method static Collection|null ontsRunningConfig() Gets complete running configuration for ONTs specified by interfaces() (C300 model only).
 * @method static Collection|null ontsByPonInterface(string $ponInterface) Lists all ONTs on the specified PON interface (e.g., '1/1/1').
 *
 * == ONT MANAGEMENT ==
 * @method static int|null getNextOntIndex(string $ponInterface) Finds the next available ONT index for provisioning on the specified PON interface.
 * @method static Collection|null removeOnts() Removes/deletes ONTs specified by interfaces().
 * @method static Collection|null rebootOnts() Reboots ONTs specified by interfaces().
 * @method static Collection|null provisionOnts(string $ponInterface, int $ontIndex, string $profile) Provisions ONTs by serial number on the specified PON interface with given index and profile.
 * @method static Collection|null setOntsName(string $name) Sets a display name for ONTs specified by interfaces().
 * @method static Collection|null setOntsDescription(string $description) Sets description text for ONTs specified by interfaces().
 *
 * == SERVICE CONFIGURATION ==
 * @method static Collection|null configureTCont(int $tcontId, string $profileName) Configures Traffic Container (T-CONT) with specified ID and bandwidth profile for ONTs.
 * @method static Collection|null configureGemport(GemportConfig $gemportConfig, string $terminalMode) Configures GEM ports with specified parameters using the selected terminal mode ('interface-onu' or 'pon-onu-mng').
 * @method static Collection|null configureServicePort(ServicePortConfig $servicePortConfig, ?int $vport = null) Configures service ports (data path) with specified parameters and optional virtual port for C600 model.
 * @method static Collection|null configureService(ServiceConfig $serviceConfig) Configures ONT service parameters like bandwidth profiles.
 *
 * == VLAN CONFIGURATION ==
 * @method static Collection|null configureVlanPort(VlanPortConfig $vlanPortConfig) Configures VLAN translation/conversion rules for the ONT interfaces.
 * @method static Collection|null configureFlowMode(FlowModeConfig $flowModeConfig) Configures the flow mode for traffic handling (C300 model only).
 * @method static Collection|null configureFlow(FlowConfig $flowConfig) Configures specific flow rules for traffic management (C300 model only).
 * @method static Collection|null configureSwitchportBind(SwitchportBindConfig $switchportBindConfig) Binds UNI ports with a layer 2 bridge for traffic forwarding (C300 model only).
 * @method static Collection|null configureVlanFilterMode(VlanFilterModeConfig $vlanFilterModeConfig) Sets VLAN filtering mode for ONT interfaces (C300 model only).
 * @method static Collection|null configureVlanFilter(VlanFilterConfig $vlanFilterConfig) Configures VLAN filtering rules for traffic control (C300 model only).
 *
 * @see \PauloHortelan\Onmt\Services\ZTE\ZTEService
 */
class ZTE extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\ZTE\ZTEService::class;
    }
}
