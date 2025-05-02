<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureQosInterface
{
    public string $qosInterface;

    public ?int $upstreamQueue;

    public ?string $bandwidthProfile;

    public ?int $queue;

    public ?string $shaperProfile;

    public function __construct(
        string $qosInterface,
        ?int $upstreamQueue = null,
        ?string $bandwidthProfile = null,
        ?int $queue = null,
        ?string $shaperProfile = null,
    ) {
        $this->qosInterface = $qosInterface;
        $this->upstreamQueue = $upstreamQueue;
        $this->bandwidthProfile = $bandwidthProfile;
        $this->queue = $queue;
        $this->shaperProfile = $shaperProfile;
    }

    public function buildCommand(): string
    {
        $command = '';

        $parameters = [
            'upstream-queue' => $this->upstreamQueue ?? null,
            'bandwidth-profile' => $this->bandwidthProfile ?? null,
            'queue' => $this->queue ?? null,
            'shaper-profile' => $this->shaperProfile ?? null,
        ];

        foreach ($parameters as $key => $value) {
            if (isset($value)) {
                $command .= "$key $value ";
            }
        }

        $command = rtrim($command, ' ');

        return $command;
    }
}
