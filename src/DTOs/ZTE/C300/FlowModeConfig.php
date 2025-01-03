<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class FlowModeConfig
{
    public int $flowId;

    public string $tagFilter;

    public string $untagFilter;

    public function __construct(
        int $flowId,
        string $tagFilter,
        string $untagFilter,
    ) {
        $this->flowId = $flowId;
        $this->tagFilter = $tagFilter;
        $this->untagFilter = $untagFilter;
    }

    public function buildCommand(): string
    {
        $command = "flow mode {$this->flowId} tag-filter {$this->tagFilter} untag-filter {$this->untagFilter}";

        return $command;
    }
}
