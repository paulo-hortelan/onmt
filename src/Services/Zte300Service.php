<?php

namespace PauloHortelan\OltMonitoring\Services;

use PauloHortelan\OltMonitoring\Connections\Telnet;
use PauloHortelan\OltMonitoring\Models\Olt;

class Zte300Service
{
    private Telnet $connection;

    public function connect(Olt $olt, int $timeout = 3, int $streamTimeout = 3) {
        $this->connection = new Telnet($olt->host, 23, $timeout, $streamTimeout);
        $this->connection->stripPromptFromBuffer(true);
        $this->connection->login($olt->username, $olt->password, 'zte300');

        return $this;
    }

    public function ontOpticalPower($interface): float{        
        $response = $this->connection->exec("show pon power attenuation $interface");

        if(preg_match('/down.*Rx:(.*)\(dbm\)/m', $response, $match)){
            $opticalPower = (float) $match[1];
        } else {
            throw new \Exception('Ont optical power not found.');
        }

        return $opticalPower;      
    }

    public function ontInterface($serial): string{        
        $response = $this->connection->exec("show gpon onu by sn $serial");

        if(preg_match('/gpon-onu.*/m', $response, $match)){
            $interface = (string) $match[0];
        } else {
            throw new \Exception('Ont interface not found.');
        }

        return $interface;       
    }
}
