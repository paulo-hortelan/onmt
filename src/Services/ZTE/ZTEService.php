<?php

namespace PauloHortelan\OltMonitoring\Services\ZTE;

use Exception;
use PauloHortelan\OltMonitoring\Connections\Telnet;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Services\Concerns\Validations;
use PauloHortelan\OltMonitoring\Services\ZTE\Models\C300;
use PauloHortelan\OltMonitoring\Services\ZTE\Models\C600;

class ZTEService
{
    use Validations;

    private Telnet $connection;

    private string $model = 'C300';

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3): mixed
    {
        if (!$this->oltValid($olt)) {
            throw new Exception('OLT brand does not match the service.');
        }

        $this->model = $olt->model;
        $this->connection = Telnet::getInstance($olt->host, 23, $timeout, $streamTimeout, $olt->username, $olt->password, 'ZTE-' . $this->model);
        $this->connection->stripPromptFromBuffer(true);
        $this->connection->exec('terminal length 0');

        return $this;
    }

    public function disconnect(): void
    {
        if (empty($this->connection))
            throw new Exception('No connection established.');

        $this->connection->destroy();
    }

    /**
     * Returns the ONT optical power
     */
    public function ontOpticalPower(string $interface): float
    {
        if ($this->model === 'C300') {
            return (new C300($this->connection))->ontOpticalPower($interface);
        }

        if ($this->model === 'C600') {
            return (new C600($this->connection))->ontOpticalPower($interface);
        }

        throw new \Exception('Product model not supported');
    }

    /**
     * Returns the ONT interface
     */
    public function ontInterface(string $serial): string
    {
        if ($this->model === 'C300') {
            return (new C300($this->connection))->ontInterface($serial);
        }

        if ($this->model === 'C600') {
            return (new C600($this->connection))->ontInterface($serial);
        }

        throw new \Exception('Product model not supported');
    }
}
