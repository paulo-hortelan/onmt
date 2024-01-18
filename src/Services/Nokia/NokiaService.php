<?php

namespace PauloHortelan\OltMonitoring\Services\Nokia;

use Exception;
use PauloHortelan\OltMonitoring\Connections\Telnet;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Services\Concerns\Validations;
use PauloHortelan\OltMonitoring\Services\Nokia\Models\FX16;

class NokiaService
{
    use Validations;

    private Telnet $connection;

    private string $model = 'FX16';

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3): mixed
    {
        if (! $this->oltValid($olt)) {
            throw new Exception('OLT brand does not match the service.');
        }

        $this->model = $olt->model;
        $this->connection = Telnet::getInstance($olt->host, 23, $timeout, $streamTimeout, $olt->username, $olt->password, 'Nokia-'.$this->model);
        $this->connection->stripPromptFromBuffer(true);
        $this->connection->exec('environment inhibit-alarms');

        return $this;
    }

    /**
     * Returns the ONT optical power
     */
    public function ontOpticalPower(string $interface): float
    {
        if ($this->model === 'FX16') {
            return (new FX16($this->connection))->ontOpticalPower($interface);
        }

        throw new \Exception('Product model not supported');
    }

    /**
     * Returns the ONT interface
     */
    public function ontInterface(string $serial): string
    {
        if ($this->model === 'FX16') {
            return (new FX16($this->connection))->ontInterface($serial);
        }

        throw new \Exception('Product model not supported');
    }
}
