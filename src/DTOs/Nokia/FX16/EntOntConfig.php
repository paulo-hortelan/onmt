<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class EntOntConfig
{
    public string $desc1;

    public string $desc2;

    public string $serNum;

    public string $swVerPlnd;

    public ?string $opticsHist;

    public ?string $plndCfgFile1;

    public ?string $dlCfgFile1;

    public ?string $voipAllowed;

    public function __construct(
        string $desc1,
        string $desc2,
        string $serNum,
        string $swVerPlnd,
        ?string $opticsHist = null,
        ?string $plndCfgFile1 = null,
        ?string $dlCfgFile1 = null,
        ?string $voipAllowed = null,
    ) {
        $this->desc1 = $desc1;
        $this->desc2 = $desc2;
        $this->serNum = $serNum;
        $this->swVerPlnd = $swVerPlnd;
        $this->opticsHist = $opticsHist;
        $this->plndCfgFile1 = $plndCfgFile1;
        $this->dlCfgFile1 = $dlCfgFile1;
        $this->voipAllowed = $voipAllowed;
    }

    public function buildCommand(): string
    {
        $parameters = [
            'DESC1' => '"'.$this->desc1.'"' ?? null,
            'DESC2' => '"'.$this->desc2.'"' ?? null,
            'SERNUM' => $this->serNum ?? null,
            'SWVERPLND' => $this->swVerPlnd ?? null,
            'OPTICSHIST' => $this->opticsHist ?? null,
            'PLNDCFGFILE1' => $this->plndCfgFile1 ?? null,
            'DLCFGFILE1' => $this->dlCfgFile1 ?? null,
            'VOIPALLOWED' => $this->voipAllowed ?? null,
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
