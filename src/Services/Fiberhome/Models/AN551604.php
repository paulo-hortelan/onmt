<?php

namespace PauloHortelan\Onmt\Services\Fiberhome\Models;

use PauloHortelan\Onmt\Services\Connections\TL1;

class AN551604
{
    protected TL1 $connection;

    protected string $ipOlt;

    public function __construct(TL1 $connection, string $ipOlt)
    {
        $this->connection = $connection;
        $this->ipOlt = $ipOlt;
    }

    /**
     * Returns the ONT optical powers
     */
    public function ontOpticalPowers(array $interfaces, array $serials): ?array
    {
        $opticalPowers = [];

        for ($i = 0; $i < count($interfaces); $i++) {
            $success = false;
            $interface = $interfaces[$i];
            $serial = $serials[$i];

            try {
                $response = $this->connection->exec("LST-OMDDM::OLTID={$this->ipOlt},PONID=$interface,ONUIDTYPE=MAC,ONUID=$serial:CTAG::;");

                if (str_contains($response, 'M  CTAG COMPLD')) {
                    $response = preg_split("/\r\n|\n|\r/", $response);

                    foreach ($response as $key => $column) {
                        if (preg_match('/ONUID/', $column)) {
                            $success = true;
                            $splitted = preg_split('/\\t/', $response[$key + 1]);

                            $rxPower = (float) str_replace(',', '.', $splitted[1]);
                            $txPower = (float) str_replace(',', '.', $splitted[3]);
                        }
                    }
                }

                if (! $success) {
                    $errorInfo = $response;
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            $opticalPowers[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'interface' => $interface,
                    'serial' => $serial ?? null,
                    'rxPower' => $rxPower ?? null,
                    'txPower' => $txPower ?? null,
                ],
            ];
        }

        return $opticalPowers;
    }

    /**
     * Returns the ONT optical powers
     */
    public function ontOpticalStates(array $interfaces, array $serials): ?array
    {
        $opticalStates = [];

        for ($i = 0; $i < count($interfaces); $i++) {
            $success = false;
            $interface = $interfaces[$i];
            $serial = $serials[$i];

            try {
                $response = $this->connection->exec("LST-ONUSTATE::OLTID={$this->ipOlt},PONID={$interface},ONUIDTYPE=MAC,ONUID={$serial}:CTAG::;");

                if (str_contains($response, 'M  CTAG COMPLD')) {
                    $response = preg_split("/\r\n|\n|\r/", $response);

                    foreach ($response as $key => $column) {
                        if (preg_match('/ONUID/', $column)) {
                            $success = true;
                            $splitted = preg_split('/\\t/', $response[$key + 1]);

                            $adminState = $splitted[1];
                            $oprState = $splitted[2];
                            $auth = $splitted[3];
                            $lastOffTime = $splitted[6];
                        }
                    }
                }

                if (! $success) {
                    $errorInfo = $response;
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            $opticalStates[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'interface' => $interface,
                    'serial' => $serial ?? null,
                    'adminState' => $adminState ?? null,
                    'oprState' => $oprState ?? null,
                    'auth' => $auth ?? null,
                    'lastOffTime' => $lastOffTime ?? null,
                ],
            ];
        }

        return $opticalStates;
    }
}
