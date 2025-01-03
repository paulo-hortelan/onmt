<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class GemportConfig
{
    public int $gemportId;

    public ?int $tcontId;

    public ?string $upstreamProfile;

    public ?string $downstreamProfile;

    public function __construct(
        int $gemportId,
        ?int $tcontId = null,
        ?string $upstreamProfile = null,
        ?string $downstreamProfile = null,
    ) {
        $this->gemportId = $gemportId;
        $this->tcontId = $tcontId;
        $this->upstreamProfile = $upstreamProfile;
        $this->downstreamProfile = $downstreamProfile;
    }

    public function buildCommand(): string
    {
        $command = "gemport {$this->gemportId}";

        if (isset($this->tcontId)) {
            $command .= " tcont {$this->tcontId}";

            return $command;
        }

        if (isset($this->downstreamProfile) || isset($this->upstreamProfile)) {
            $command .= ' traffic-limit ';

            if (isset($this->upstreamProfile)) {
                $command .= "upstream {$this->upstreamProfile}";
            }

            if (isset($this->downstreamProfile)) {
                $command .= "downstream {$this->downstreamProfile}";
            }
        }

        return $command;
    }
}
