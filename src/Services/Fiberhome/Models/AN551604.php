<?php

namespace PauloHortelan\Onmt\Services\Fiberhome\Models;

use PauloHortelan\Onmt\Connections\TL1;

class AN551604
{
    protected TL1 $connection;

    protected string $hostServer;

    public function __construct(TL1 $connection, string $hostServer)
    {
        $this->connection = $connection;
        $this->hostServer = $hostServer;
    }

    /**
     * Returns the ONT optical power
     */
    public function ontOpticalPower(array $interfaces, array $serials): array|float|null
    {
        $opticalPower = [];

        for ($i = 0; $i < count($interfaces); $i++) {
            $interface = $interfaces[$i];
            $serial = $serials[$i];

            $response = $this->connection->exec("LST-OMDDM::OLTID={$this->hostServer},PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;");

            if (str_contains($response, 'M  CTAG COMPLD')) {
                $response = preg_split("/\r\n|\n|\r/", $response);

                foreach ($response as $key => $column) {
                    if (preg_match('/ONUID/', $column)) {
                        $splitted = preg_split('/\\t/', $response[$key + 1]);

                        isset($splitted[1]) ? $sinal = str_replace(',', '.', $splitted[1]) : $sinal = null;

                        $opticalPower[] = (float) $sinal;
                    }
                }
            } else {
                $opticalPower[] = null;
            }
        }

        if (count($opticalPower) === 1) {
            return $opticalPower[0];
        }

        return $opticalPower;
    }

    /**
     * Returns the ONT optical interface
     */
    public function ontOpticalInterface(array $serials): array|string
    {
        $opticalInterface = [];

        foreach ($serials as $serial) {
            $formattedSerial = substr_replace($serial, ':', 4, 0);

            $response = $this->connection->exec("show equipment ont index sn:$formattedSerial detail");

            if (preg_match('/ont-idx.*:(.*\s)/m', $response, $match)) {
                $opticalInterface[] = trim((string) $match[1]);
            } else {
                throw new \Exception('Ont interface not found.');
            }
        }

        if (count($opticalInterface) === 1) {
            return $opticalInterface[0];
        }

        return $opticalInterface;
    }
}
