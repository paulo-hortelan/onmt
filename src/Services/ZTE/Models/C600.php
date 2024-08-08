<?php

namespace PauloHortelan\Onmt\Services\ZTE\Models;

class C600 extends C300
{
    /**
     * Returns the ONT's interface
     */
    public function ontsInterface(array $serials): ?array
    {
        $ontsInterface = [];

        foreach ($serials as $serial) {
            $success = false;

            try {
                $response = $this->connection->exec("show gpon onu by sn $serial");

                if (preg_match('/gpon_onu.*/m', $response, $match)) {
                    $success = true;
                    $interface = trim($match[0]);
                }
            } catch (\Exception $e) {
                $errorInfo = $e->getMessage();
            }

            if (! $success) {
                $errorInfo = 'Interface not found on OLT';
            }

            $ontsInterface[] = [
                'success' => $success,
                'errorInfo' => $errorInfo ?? null,
                'result' => [
                    'serial' => $serial,
                    'interface' => $interface ?? null,
                ],
            ];
        }

        return $ontsInterface;
    }
}
