<?php

namespace PauloHortelan\Onmt\Services\Nokia\Traits;

trait ValidationTrait
{
    /**
     * Validates the mode parameter.
     *
     * @throws \Exception
     */
    public function validateMode(string $mode): void
    {
        if (! in_array($mode, ['ENT', 'ED'])) {
            throw new \Exception('Invalid mode. Mode must be either ENT or ED.');
        }
    }
}
