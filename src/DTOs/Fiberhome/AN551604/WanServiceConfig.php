<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN551604;

class WanServiceConfig
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
        $command = '';

        if (isset($this->status)) {
            $command .= 'STATUS='.$this->status.',';
        }
        if (isset($this->mode)) {
            $command .= 'MODE='.$this->mode.',';
        }
        if (isset($this->connType)) {
            $command .= 'CONNTYPE='.$this->connType.',';
        }
        if (isset($this->vlan)) {
            $command .= 'VLAN='.$this->vlan.',';
        }
        if (isset($this->cos)) {
            $command .= 'COS='.$this->cos.',';
        }
        if (isset($this->nats)) {
            $command .= 'NAT='.$this->nat.',';
        }
        if (isset($this->ipMode)) {
            $command .= 'IPMODE='.$this->ipMode.',';
        }
        if (isset($this->pppoeProxy)) {
            $command .= 'PPPOEPROXY='.$this->pppoeProxy.',';
        }
        if (isset($this->pppoeUser)) {
            $command .= 'PPPOEUSER='.$this->pppoeUser.',';
        }
        if (isset($this->pppoePasswd)) {
            $command .= 'PPPOEPASSWD='.$this->pppoePasswd.',';
        }
        if (isset($this->pppoeName)) {
            $command .= 'PPPOENAME='.$this->pppoeName.',';
        }
        if (isset($this->pppoeMode)) {
            $command .= 'PPPOEMODE='.$this->pppoeMode.',';
        }
        if (isset($this->uPort)) {
            $command .= 'UPORT='.$this->uPort.',';
        }
        if (isset($this->ssdId)) {
            $command .= 'SSID='.$this->ssdId.',';
        }

        $command = rtrim($command, ',');

        return $command;
    }
}
