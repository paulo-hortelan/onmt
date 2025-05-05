<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureEquipmentOntInterface
{
    public const NO = '__NO__';

    public ?string $plannedUsRate;

    public ?string $subslocid;

    public ?string $fecUp;

    public ?string $swVerPlnd;

    public ?string $swDnloadVersion;

    public ?string $sernum;

    public ?string $opticsHist;

    public ?string $plandCfgfile1;

    public ?string $dnloadCfgfile1;

    public ?string $desc1;

    public function __construct(
        ?string $plannedUsRate = null,
        ?string $subslocid = null,
        ?string $fecUp = null,
        ?string $swVerPlnd = null,
        ?string $swDnloadVersion = null,
        ?string $sernum = null,
        ?string $opticsHist = null,
        ?string $plandCfgfile1 = null,
        ?string $dnloadCfgfile1 = null,
        ?string $desc1 = null,
    ) {
        $this->plannedUsRate = $plannedUsRate;
        $this->subslocid = $subslocid;
        $this->fecUp = $fecUp;
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
        $propertiesMap = [
            'plannedUsRate' => 'planned-us-rate',
            'subslocid' => 'subslocid',
            'fecUp' => 'fec-up',
            'swVerPlnd' => 'sw-ver-pland',
            'opticsHist' => 'optics-hist',
            'plandCfgfile1' => 'pland-cfgfile1',
            'desc1' => 'desc1',
        ];

        foreach ($propertiesMap as $property => $command) {
            if (isset($this->$property)) {
                $commandParts[] = "{$command} {$this->$property}";
            }
        }

        // Handle special cases
        if (isset($this->sernum)) {
            $formattedSerial = substr_replace($this->sernum, ':', 4, 0);
            $commandParts[] = "sernum {$formattedSerial}";
        }

        if (isset($this->swDnloadVersion)) {
            $commandParts[] = $this->swDnloadVersion === self::NO ? 'no sw-dnload-version' : "sw-dnload-version {$this->swDnloadVersion}";
        }

        if (isset($this->dnloadCfgfile1)) {
            $commandParts[] = $this->dnloadCfgfile1 === self::NO ? 'no dnload-cfgfile1' : "dnload-cfgfile1 {$this->dnloadCfgfile1}";
        }

        return implode(' ', $commandParts);
    }
}
