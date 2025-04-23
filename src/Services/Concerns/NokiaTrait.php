<?php

namespace PauloHortelan\Onmt\Services\Concerns;

trait NokiaTrait
{
    /**
     * Validates the mode parameter.
     *
     * @throws \Exception
     */
    public function validateMode(string $mode): void
    {
        if (! in_array($mode, ['ENT', 'ED', 'DLT'])) {
            throw new \Exception('Invalid mode. Mode must be either ENT, ED or DLT.');
        }
    }
}
