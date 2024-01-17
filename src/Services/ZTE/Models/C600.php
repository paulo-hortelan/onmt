<?php

namespace PauloHortelan\OltMonitoring\Services\ZTE\Models;

class C600 extends C300
{
    /**
     * Returns the ONT interface
     */
    public function ontInterface(string $serial): string
    {
        $response = $this->connection->exec("show gpon onu by sn $serial");

        if (preg_match('/gpon_onu.*/m', $response, $match)) {
            $interface = (string) $match[0];
        } else {
            throw new \Exception('Ont interface not found.');
        }

        return $interface;
    }
}
