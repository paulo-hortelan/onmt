<?php

namespace PauloHortelan\Onmt\DTOs\Fiberhome\AN551604;

class VeipConfig
{
    public int $serviceId;

    public int $cVlanId;

    public string $serviceModelProfile;

    public string $serviceType;

    public ?int $tvLanId = null;

    public function __construct(
        int $serviceId,
        int $cVlanId,
        string $serviceModelProfile,
        string $serviceType,
        ?int $tvLanId = null,
    ) {
        $this->serviceId = $serviceId;
        $this->cVlanId = $cVlanId;
        $this->serviceModelProfile = $serviceModelProfile;
        $this->serviceType = $serviceType;
        $this->tvLanId = $tvLanId;
    }

    public function buildCommand(): string
    {
        $parameters = [
            'ServiceId' => $this->serviceId ?? null,
            'CVLANID' => $this->cVlanId ?? null,
            'ServiceModelProfile' => $this->serviceModelProfile ?? null,
            'ServiceType' => $this->serviceType ?? null,
            'TVLANID' => $this->tvLanId ?? null,
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
