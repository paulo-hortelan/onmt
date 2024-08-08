<?php

namespace PauloHortelan\Onmt\Services\Concerns;

trait Assertations
{
    /**
     * Verify if both arrays have the same lengths
     */
    public function assertSameLength($arrayOfArrays): bool
    {
        for ($i = 0; $i < count($arrayOfArrays) - 1; $i++) {
            if (count($arrayOfArrays[$i]) !== count($arrayOfArrays[$i + 1])) {
                return false;
            }
        }

        return true;
    }
}
