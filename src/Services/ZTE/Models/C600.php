<?php

namespace PauloHortelan\Onmt\Services\ZTE\Models;

class C600 extends C300
{
    /**
     * Returns the ONTs interface
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
                $error = $e->getMessage();
            }

            if (! $success) {
                $error = 'Interface not found on OLT';
            }

            $ontsInterface[] = [
                'success' => $success,
                'error' => $error ?? null,
                'result' => [
                    'serial' => $serial,
                    'interface' => $interface ?? null,
                ],
            ];
        }

        return $ontsInterface;
    }
}
