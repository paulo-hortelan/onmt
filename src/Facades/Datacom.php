<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\Models\CommandResultBatch;

/**
 * Datacom OLT Management Facade
 *
 * This facade provides an interface to manage Datacom OLTs (DM4612 model)
 * through Telnet connections.
 *
 * == CONNECTION MANAGEMENT ==
 *
 * @method static self connectTelnet(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null, ?string $model = 'DM4612') Establishes Telnet connection to Datacom OLT with specified credentials. The $ipServer parameter defaults to $ipOlt if not provided.
 * @method static void disconnect() Terminates the current Telnet connection.
 * @method static void enableDebug() Enables verbose debug output for the Telnet session for troubleshooting.
 * @method static void disableDebug() Disables debug output for the Telnet session.
 *
 * == DATABASE TRANSACTION MANAGEMENT ==
 * @method static self enableDatabaseTransactions() Enables database transactions for batch and command saving (default behavior).
 * @method static self disableDatabaseTransactions() Disables database transactions for batch and command saving. Results will be created in memory only.
 *
 * == CONFIGURATION SETUP ==
 * @method static self model(string $model) Sets the OLT model (supported: 'DM4612').
 * @method static self timeout(int $connTimeout, int $streamTimeout) Configures connection timeout (in seconds) and stream timeout (in seconds) for Telnet operations.
 * @method static self interfaces(array $interfaces) Sets the ONU interfaces to operate on (format: ['1/1/1/2', '1/1/1/3']).
 * @method static self serials(array $serials) Sets the ONT serial numbers to operate on.
 * @method static self setOperator(string $operator) Sets the operator name for logging/tracking operations.
 *
 * == COMMAND RECORDING ==
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Starts recording all executed commands in a batch with optional metadata.
 * @method static CommandResultBatch stopRecordingCommands() Stops command recording and returns the complete command batch results.
 *
 * == TERMINAL MODE MANAGEMENT ==
 * @method static CommandResultBatch|null setDefaultTerminalMode() Switches to default terminal mode (equivalent to exiting any configuration mode).
 * @method static CommandResultBatch|null setConfigTerminalMode() Enters configuration terminal mode (equivalent to "configure terminal" command).
 * @method static CommandResultBatch|null setInterfaceGponTerminalMode(string $ponInterface) Enters GPON interface configuration mode for specified PON interface (e.g., '1/1/1').
 * @method static CommandResultBatch|null setOnuTerminalMode(string $interface) Enters ONU interface configuration mode for specified ONU interface (e.g., '1/1/1/2').
 * @method static CommandResultBatch|null setEthernetTerminalMode(string $interface, int $port) Enters Ethernet terminal mode for specified ONU interface and port.
 *
 * == ONT INFORMATION RETRIEVAL ==
 * @method static Collection|null unconfiguredOnts() Lists all unconfigured/unprovisioned ONTs detected by the OLT.
 * @method static Collection|null interfaceOnts() Gets interface assignments for ONTs specified by serials().
 * @method static Collection|null ontsInfo() Retrieves comprehensive ONT information for interfaces specified by interfaces().
 * @method static Collection|null ontsAlarm() Gets ONTs alarm information specified by interfaces().
 * @method static CommandResultBatch|null ontsServicePort() Gets all ONTs service port information.
 * @method static CommandResultBatch|null ontsServicePortByPonInterface(string $ponInterface) Gets ONTs service port information by PON interface.
 * @method static Collection|null ontsServicePortByInterfaces() Gets ONTs service port information for interfaces specified by interfaces().
 * @method static CommandResultBatch|null ontsByPonInterface(string $ponInterface) Gets all ONTs on a specific PON interface.
 * @method static int|null getNextOntIndex(string $ponInterface) Gets the next available ONT index for a PON interface.
 * @method static int|null getNextServicePort() Gets the next available Service Port.
 *
 * == ONT MANAGEMENT ==
 * @method static Collection|null ontsReboot() Reboots ONTs specified by interfaces().
 * @method static Collection|null commitConfigurations() Commits OLT configuration changes.
 * @method static Collection|null setName(string $name) Sets names for ONTs specified by interfaces().
 * @method static Collection|null setSerialNumber(string $serial) Sets serial numbers for ONTs specified by interfaces().
 * @method static Collection|null setSnmpProfile(string $profile) Sets SNMP profiles for ONTs specified by interfaces().
 * @method static Collection|null setSnmpRealTime() Enables SNMP real-time for ONTs specified by interfaces().
 * @method static Collection|null setLineProfile(string $profile) Sets line profiles for ONTs specified by interfaces().
 * @method static Collection|null setVeip(int $port = 1) Sets VEIP for ONTs specified by interfaces().
 * @method static Collection|null setServicePort(int $port, int $vlan, string $description) Sets service ports for ONTs specified by interfaces().
 * @method static Collection|null setNegotiation(int $ethernetPort) Sets Ethernet negotiation for ONTs specified by interfaces().
 * @method static Collection|null setNoShutdown(int $ethernetPort) Enables (no shutdown) Ethernet ports for ONTs specified by interfaces().
 * @method static Collection|null setNativeVlan(int $ethernetPort, int $vlan) Sets native VLANs for Ethernet ports on ONTs specified by interfaces().
 *
 * == ONT REMOVAL ==
 * @method static Collection|null removeOnts() Removes ONTs specified by interfaces().
 * @method static Collection|null removeServicePorts(array $ports) Removes service ports with specified indexes.
 * @method static Collection|null removeOntsServicePorts(array $ports) Removes ONTs and their service ports specified by interfaces() and port indexes.
 *
 * @see \PauloHortelan\Onmt\Services\Datacom\DatacomService
 */
class Datacom extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\Datacom\DatacomService::class;
    }
}
