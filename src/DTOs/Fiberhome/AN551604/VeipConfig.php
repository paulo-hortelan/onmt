<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN5516_04;

class VeipConfig
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
        $parameters = [
            'ServiceId' => $this->serviceId ?? null,
            'CVLANID' => $this->cVlanId ?? null,
            'ServiceModelProfile' => $this->serviceModelProfile ?? null,
            'ServiceType' => $this->serviceType ?? null,
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
