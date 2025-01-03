<?php

namespace PauloHortelan\Onmt\DTOs\ZTE\C300;

class VlanFilterModeConfig
{
    public int $iphost;

    public string $tagFilter;

    public string $untagFilter;

    public function __construct(
        int $iphost,
        string $tagFilter,
        string $untagFilter,
    ) {
        $this->iphost = $iphost;
        $this->tagFilter = $tagFilter;
        $this->untagFilter = $untagFilter;
    }

    public function buildCommand(): string
    {
        $command = "vlan-filter-mode iphost {$this->iphost} tag-filter {$this->tagFilter} untag-filter {$this->untagFilter}";

        return $command;
    }
}
