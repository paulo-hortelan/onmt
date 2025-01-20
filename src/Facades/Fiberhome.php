<?php

namespace PauloHortelan\Onmt\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN5516_04\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN5516_04\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN5516_04\WanConfig;
use PauloHortelan\Onmt\Models\CommandResultBatch;

/**
 * @method static self connectTL1(string $ipOlt, string $username, string $password, int $port, ?string $ipServer = null) Connect to the OLT via TL1.
 * @method static void disconnect() Disconnect from the OLT.
 * @method static self timeout(int $connTimeout, int $streamTimeout) Set the TL1 connection and stream timeouts.
 * @method static self interfaces(array $interfaces) Set the interfaces to be used for operations.
 * @method static self serials(array $serials) Set the ONT serials to be used for operations.
 * @method static void enableDebug() Enable debug mode for the TL1 session.
 * @method static void disableDebug() Disable debug mode for the TL1 session.
 * @method static self setOperator(string $operator) Set the operator for the current session.
 * @method static void startRecordingCommands(?string $description = null, ?string $ponInterface = null, ?string $interface = null, ?string $serial = null, ?string $operator = null) Start recording commands in a batch.
 * @method static CommandResultBatch stopRecordingCommands() Stop recording commands and return the batch.
 * @method static Collection|null ontsOpticalPower(string $ponInterface) Get ONTs optical power. Parameter 'serials' must already be provided.
 * @method static Collection|null ontsStateInfo(string $ponInterface) Get ONTs state info. Parameter 'serials' must already be provided.
 * @method static Collection|null ontsPortInfo(string $ponInterface) Get ONTs port info. Parameter 'serials' must already be provided.
 * @method static Collection|null ontsLanInfo(string $ponInterface) List ONTs LAN info. Parameter 'serials' must already be provided.
 * @method static Collection|null oltUplinksLanPerf(string $portInterface) List OLT uplink's LAN performance.
 * @method static Collection|null unregisteredOnts() List unregistered ONTs.
 * @method static Collection|null registeredOnts() List registered ONTs.
 * @method static Collection|null authorizeOnts(string $ponInterface, string $ontType, string $pppoeUsername) Authorize ONTs. Parameter 'serials' must already be provided.
 * @method static Collection|null configureLanOnts(string $ponInterface, string $portInterface, LanConfig $config) Configure ONTs LAN service. Parameter 'serials' must already be provided.
 * @method static Collection|null configureVeipOnts(string $ponInterface, string $portInterface, VeipConfig $config) Configure ONTs VEIP service. Parameter 'serials' must already be provided.
 * @method static Collection|null configureWanOnts(string $ponInterface, WanConfig $config) Configure ONTs WAN service. Parameter 'serials' must already be provided.
 * @method static Collection|null removeOnts(string $ponInterface) Remove/Delete ONTs. Parameter 'serials' must already be provided.
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
