<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Models\CommandResultBatch;

/**
 * Fiberhome OLT Management Facade
 *
 * This facade provides an interface to manage Fiberhome OLTs
 * (AN5516-04, AN5516-06, AN5516-06B models) through TL1 connections.
 *
 * == CONNECTION MANAGEMENT ==
 *
 * @method static self connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null) Establishes TL1 connection to Fiberhome OLT with specified credentials. The $ipServer parameter defaults to $ipOlt if not provided.
 * @method static void disconnect() Terminates the current TL1 connection to the OLT.
 * @method static void enableDebug() Enables verbose debug output for the TL1 session for troubleshooting purposes.
 * @method static void disableDebug() Disables debug output for the TL1 session.
 *
 * == DATABASE TRANSACTION MANAGEMENT ==
 * @method static self enableDatabaseTransactions() Enables database transactions for batch and command saving (default behavior).
 * @method static self disableDatabaseTransactions() Disables database transactions for batch and command saving. Results will be created in memory only.
 *
 * == CONFIGURATION SETUP ==
 * @method static self timeout(int $connTimeout, int $streamTimeout) Configures connection timeout (in seconds) and stream timeout (in seconds) for TL1 operations.
 * @method static self interfaces(array $interfaces) Sets the ONU interfaces to operate on - primarily used for future compatibility.
 * @method static self serials(array $serials) Sets the ONT serial numbers to operate on. All serials are automatically converted to uppercase.
 * @method static self setOperator(string $operator) Sets the operator name for logging/tracking operations.
 *
 * == COMMAND RECORDING ==
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Starts recording all executed commands in a batch with optional metadata. Limited to single interface or serial.
 * @method static CommandResultBatch stopRecordingCommands() Stops command recording and returns the complete command batch results.
 *
 * == ONT INFORMATION RETRIEVAL ==
 * @method static Collection|null ontsOpticalPower(string $ponInterface) Retrieves optical power levels (TX/RX) for ONTs specified by serials() on the given PON interface (e.g., 'NA-NA-1-1').
 * @method static Collection|null ontsStateInfo(string $ponInterface) Gets operational state information (admin state, operational state, authentication status) for ONTs specified by serials() on the given PON interface.
 * @method static Collection|null ontsPortInfo(string $ponInterface) Gets port configuration information (including VLAN settings) for ONTs specified by serials() on the given PON interface.
 * @method static Collection|null ontsLanInfo(string $ponInterface) Gets LAN port details for ONTs specified by serials() on the given PON interface.
 * @method static Collection|null oltUplinksLanPerf(string $portInterface) Retrieves performance statistics for an OLT uplink port (e.g., 'NA-NA-1-1').
 * @method static Collection|null unregisteredOnts(string $ponInterface) Lists all discovered but not yet provisioned ONTs on the specified PON interface.
 * @method static Collection|null registeredOnts(string $ponInterface) Lists all registered/provisioned ONTs on the entire OLT.
 *
 * == ONT MANAGEMENT ==
 * @method static Collection|null rebootOnts(string $ponInterface) Reboots ONTs specified by serials() on the given PON interface. Useful after configuration changes.
 * @method static Collection|null removeOnts(string $ponInterface) Removes/deletes ONTs specified by serials() from the given PON interface.
 *
 * == ONT PROVISIONING ==
 * @method static Collection|null authorizeOnts(string $ponInterface, string $ontType, string $pppoeUsername) Authorizes/provisions ONTs specified by serials() on the given PON interface using the specified ONT type (e.g., 'HG260') and PPPoE username.
 * @method static Collection|null configureLanOnts(string $ponInterface, string $portInterface, LanConfig $config) Configures LAN port settings (including VLAN tagging) for ONTs specified by serials() on the given PON and port interfaces.
 * @method static Collection|null configureVeipOnts(string $ponInterface, string $portInterface, VeipConfig $config) Configures Virtual Ethernet Interface Point (VEIP) service for ONTs specified by serials() on the given PON and port interfaces.
 * @method static Collection|null configureWanOnts(string $ponInterface, WanConfig $config) Configures WAN service settings (Internet connectivity parameters) for ONTs specified by serials() on the given PON interface.
 *
 * @see \PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService
 */
class Fiberhome extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService::class;
    }
}
