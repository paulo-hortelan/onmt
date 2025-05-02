<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureEquipmentOntInterface
{
    public const NO = '__NO__';

    public ?string $swVerPlnd;

    public ?string $swDnloadVersion;

    public ?string $sernum;

    public ?string $opticsHist;

    public ?string $plandCfgfile1;

    public ?string $dnloadCfgfile1;

    public ?string $desc1;

    public function __construct(
        ?string $swVerPlnd = null,
        ?string $swDnloadVersion = null,
        ?string $sernum = null,
        ?string $opticsHist = null,
        ?string $plandCfgfile1 = null,
        ?string $dnloadCfgfile1 = null,
        ?string $desc1 = null,
    ) {
        $this->swVerPlnd = $swVerPlnd;
        $this->swDnloadVersion = $swDnloadVersion;
        $this->sernum = $sernum;
        $this->opticsHist = $opticsHist;
        $this->plandCfgfile1 = $plandCfgfile1;
        $this->dnloadCfgfile1 = $dnloadCfgfile1;
        $this->desc1 = $desc1;
    }

    public function buildCommand(): string
    {
        $commandParts = [];

        $formattedSerial = $this->sernum ? substr_replace($this->sernum, ':', 4, 0) : null;

        if (isset($this->swVerPlnd)) {
            $commandParts[] = "sw-ver-pland {$this->swVerPlnd}";
        }

        if ($this->swDnloadVersion === self::NO) {
            $commandParts[] = 'no sw-dnload-version';
        } elseif (isset($this->swDnloadVersion)) {
            $commandParts[] = "sw-dnload-version {$this->swVerPlnd}";
        }

        if (isset($formattedSerial)) {
            $commandParts[] = "sernum {$formattedSerial}";
        }

        if (isset($this->opticsHist)) {
            $commandParts[] = "optics-hist {$this->opticsHist}";
        }

        if (isset($this->plandCfgfile1)) {
            $commandParts[] = "pland-cfgfile1 {$this->plandCfgfile1}";
        }

        if ($this->dnloadCfgfile1 === self::NO) {
            $commandParts[] = 'no dnload-cfgfile1';
        } elseif (isset($this->dnloadCfgfile1)) {
            $commandParts[] = "dnload-cfgfile1 {$this->dnloadCfgfile1}";
        }

        if (isset($this->desc1)) {
            $commandParts[] = "desc1 {$this->desc1}";
        }

        return implode(' ', $commandParts);
    }
}
