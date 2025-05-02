<?php

namespace PauloHortelan\Onmt\DTOs\Nokia\FX16;

class ConfigureEquipmentOntSlot
{
    public string $ontSlot;

    public string $plannedCardType;

    public int $plndnumdataports;

    public int $plndnumvoiceports;

    public string $adminState;

    public function __construct(
        string $ontSlot,
        string $plannedCardType,
        int $plndnumdataports,
        int $plndnumvoiceports,
        string $adminState,
    ) {
        $this->ontSlot = $ontSlot;
        $this->plannedCardType = $plannedCardType;
        $this->plndnumdataports = $plndnumdataports;
        $this->plndnumvoiceports = $plndnumvoiceports;
        $this->adminState = $adminState;
    }

    public function buildCommand(): string
    {
        $command = '';

        $parameters = [
            'planned-card-type' => $this->plannedCardType ?? null,
            'plndnumdataports' => $this->plndnumdataports ?? null,
            'plndnumvoiceports' => $this->plndnumvoiceports ?? null,
            'admin-state' => $this->adminState ?? null,
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
