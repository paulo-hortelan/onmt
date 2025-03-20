<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class HguTr069SparamConfig
{
    public mixed $paramName;

    public mixed $paramValue;

    public int $sParamId;

    public function __construct(
        mixed $paramName,
        mixed $paramValue,
        int $sParamId
    ) {
        $this->paramName = $paramName;
        $this->paramValue = $paramValue;
        $this->sParamId = $sParamId;
    }

    public function buildCommand(string $mode): string
    {
        $command = '';

        if ($mode === 'ENT') {
            $command .= "PARAMNAME={$this->paramName},";
        }

        $command .= "PARAMVALUE={$this->paramValue},";
        $command = rtrim($command, ',');

        return $command;
    }

    /**
     * Build the ONT identifier
     *
     * @param  string  $interface  rack/shelf/lt_slot/pon/ont
     * @return string Identifier command
     */
    public function buildIdentifier(string $interface): string
    {
        $formattedInterface = str_replace('/', '-', $interface);

        $command = '';

        $command .= "HGUTR069SPARAM-$formattedInterface-{$this->sParamId}";

        $command = rtrim($command, ',');

        return $command;
    }
}
