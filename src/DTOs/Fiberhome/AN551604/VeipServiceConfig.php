<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN551604;

class VeipServiceConfig
{
    public int $serviceId;

    public int $cVlanId;

    public string $serviceModelProfile;

    public string $serviceType;

    public function __construct(
        int $serviceId,
        int $cVlanId,
        string $serviceModelProfile,
        string $serviceType,
    ) {
        $this->serviceId = $serviceId;
        $this->cVlanId = $cVlanId;
        $this->serviceModelProfile = $serviceModelProfile;
        $this->serviceType = $serviceType;
    }

    public function buildCommand(): string
    {
        $command = '';

        if (! empty($this->serviceId)) {
            $command .= 'ServiceId='.$this->serviceId.',';
        }
        if (! empty($this->cVlanId)) {
            $command .= 'CVLANID='.$this->cVlanId.',';
        }
        if (! empty($this->serviceModelProfile)) {
            $command .= 'ServiceModelProfile='.$this->serviceModelProfile.',';
        }
        if (! empty($this->serviceType)) {
            $command .= 'ServiceType='.$this->serviceType.',';
        }

        $command = rtrim($command, ',');

        return $command;
    }
}
