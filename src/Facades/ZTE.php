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
 * @method static self connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'C300') Connect to the OLT via Telnet.
 * @method static void disconnect() Disconnect from the OLT.
 * @method static CommandResult|null disableTerminalLength() Disable terminal length for the Telnet session.
 * @method static void enableDebug() Enable debug mode for the Telnet session.
 * @method static void disableDebug() Disable debug mode for the Telnet session.
 * @method static self model(string $model) Set the OLT model.
 * @method static self timeout(int $connTimeout, int $streamTimeout) Set the Telnet connection and stream timeouts.
 * @method static self interfaces(array $interfaces) Set the interfaces to be used for operations.
 * @method static self serials(array $serials) Set the ONT serials to be used for operations.
 * @method static self setOperator(string $operator) Set the operator for the current session.
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Start recording commands in a batch.
 * @method static CommandResultBatch stopRecordingCommands() Stop recording commands and return the batch.
 * @method static CommandResultBatch|null setConfigureTerminalModel() Change terminal mode to 'configure terminal'.
 * @method static CommandResultBatch|null setInterfaceOltTerminalModel(string $ponInterface) Enter GPON OLT interface terminal mode.
 * @method static CommandResultBatch|null setInterfaceOnuTerminalModel(string $interface) Enter GPON ONU interface terminal mode.
 * @method static CommandResultBatch|null setInterfaceVportTerminalModel(string $interface, int $vport) Enter GPON ONU Vport terminal mode (C600 only).
 * @method static CommandResultBatch|null setPonOnuMngTerminalModel(string $interface) Enter PON ONU management terminal mode.
 * @method static Collection|null ontsOpticalPower() Get ONTs optical power.
 * @method static Collection|null ontsInterface() Get ONTs interface by serial.
 * @method static Collection|null ontsDetailInfo() Get ONTs detail info (C300 only).
 * @method static Collection|null unconfiguredOnts() Get unconfigured ONTs.
 * @method static Collection|null ontsInterfaceRunningConfig() Get ONTs interface running config.
 * @method static Collection|null ontsRunningConfig() Get ONTs running config (C300 only).
 * @method static Collection|null ontsByPonInterface(string $ponInterface) Get ONTs by PON interface.
 * @method static int|null getNextOntIndex(string $ponInterface) Get the next free ONT index for a PON interface.
 * @method static Collection|null removeOnts() Remove ONTs.
 * @method static Collection|null provisionOnts(string $ponInterface, int $ontIndex, string $profile) Provision ONTs by PON interface.
 * @method static Collection|null setOntsName(string $name) Set ONTs name.
 * @method static Collection|null setOntsDescription(string $description) Set ONTs description.
 * @method static Collection|null configureTCont(int $tcontId, string $profileName) Configure ONTs TCont.
 * @method static Collection|null configureGemport(GemportConfig $gemportConfig, string $terminalMode) Configure ONTs Gemport.
 * @method static Collection|null configureServicePort(ServicePortConfig $servicePortConfig, ?int $vport = null) Configure ONTs Service Port.
 * @method static Collection|null configureService(ServiceConfig $serviceConfig) Configure ONTs Service.
 * @method static Collection|null configureVlanPort(VlanPortConfig $vlanPortConfig) Configure VLAN conversion rule.
 * @method static Collection|null configureFlowMode(FlowModeConfig $flowModeConfig) Configure flow mode.
 * @method static Collection|null configureFlow(FlowConfig $flowConfig) Configure flow.
 * @method static Collection|null configureSwitchportBind(SwitchportBindConfig $switchportBindConfig) Bind the UNI with a layer 2 bridge.
 * @method static Collection|null configureVlanFilterMode(VlanFilterModeConfig $vlanFilterModeConfig) Create a VLAN filtering mode.
 * @method static Collection|null configureVlanFilter(VlanFilterConfig $vlanFilterConfig) Create a VLAN filtering item.
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
