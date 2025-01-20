<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN5516_04;

class WanConfig
{
    public int $status;

    public int $mode;

    public int $connType;

    public int $vlan;

    public int $cos;

    public int $qos;

    public int $nat;

    public int $ipMode;

    public int $pppoeProxy;

    public string $pppoeUser;

    public string $pppoePasswd;

    public string $pppoeName;

    public int $pppoeMode;

    public ?int $uPort;

    public ?string $ssdId;

    public function __construct(
        int $status,
        int $mode,
        int $connType,
        int $vlan,
        int $cos,
        int $qos,
        int $nat,
        int $ipMode,
        int $pppoeProxy,
        string $pppoeUser,
        string $pppoePasswd,
        string $pppoeName,
        int $pppoeMode,
        ?int $uPort,
        ?string $ssdId
    ) {
        $this->status = $status;
        $this->mode = $mode;
        $this->connType = $connType;
        $this->vlan = $vlan;
        $this->cos = $cos;
        $this->qos = $qos;
        $this->nat = $nat;
        $this->ipMode = $ipMode;
        $this->pppoeProxy = $pppoeProxy;
        $this->pppoeUser = $pppoeUser;
        $this->pppoePasswd = $pppoePasswd;
        $this->pppoeName = $pppoeName;
        $this->pppoeMode = $pppoeMode;
        $this->uPort = $uPort;
        $this->ssdId = $ssdId;
    }

    public function buildCommand(): string
    {
        $parameters = [
            'STATUS' => $this->status ?? null,
            'MODE' => $this->mode ?? null,
            'CONNTYPE' => $this->connType ?? null,
            'VLAN' => $this->vlan ?? null,
            'COS' => $this->cos ?? null,
            'QOS' => $this->qos ?? null,
            'NAT' => $this->nat ?? null,
            'IPMODE' => $this->ipMode ?? null,
            'PPPOEPROXY' => $this->pppoeProxy ?? null,
            'PPPOEUSER' => $this->pppoeUser ?? null,
            'PPPOEPASSWD' => $this->pppoePasswd ?? null,
            'PPPOENAME' => $this->pppoeName ?? null,
            'PPPOEMODE' => $this->pppoeMode ?? null,
            'UPORT' => $this->uPort ?? null,
            'SSID' => $this->ssdId ?? null,
        ];

        $command = '';

        foreach ($parameters as $key => $value) {
            if (isset($value)) {
                $command .= "$key=$value,";
            }
        }

        $command = rtrim($command, ',');

        return $command;
    }
}
