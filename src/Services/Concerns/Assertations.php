<?php

namespace PauloHortelan\Onmt\Services\Concerns;

trait Assertations
{
    /**
     * Verify if both arrays have the same lengths
     */
    public function assertSameLength($arrayA, $arrayB): bool
    {
        return count($arrayA) === count($arrayB);
    }
}
