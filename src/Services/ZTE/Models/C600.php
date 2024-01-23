<?php

namespace PauloHortelan\OltMonitoring\Services\ZTE\Models;

class C600 extends C300
{
    /**
     * Returns the ONT interface
     */
    public function ontInterface(array $serials): array|string
    {
        $opticalInterface = [];

        foreach ($serials as $serial) {
            $response = $this->connection->exec("show gpon onu by sn $serial");

            if (preg_match('/gpon_onu.*/m', $response, $match)) {
                $opticalInterface[] = (string) $match[0];
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
