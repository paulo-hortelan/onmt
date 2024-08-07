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

    /**
     * Verify if the three arrays have the same lengths
     */
    public function assertSameLengthThree($arrayA, $arrayB, $arrayC): bool
    {
        return count($arrayA) === count($arrayB)
            && count($arrayB) === count($arrayC);
    }

    /**
     * Verify if the four arrays have the same lengths
     */
    public function assertSameLengthFour($arrayA, $arrayB, $arrayC, $arrayD): bool
    {
        return count($arrayA) === count($arrayB)
            && count($arrayB) === count($arrayC)
            && count($arrayC) === count($arrayD);
    }

    /**
     * Verify if the five arrays have the same lengths
     */
    public function assertSameLengthFive($arrayA, $arrayB, $arrayC, $arrayD, $arrayE): bool
    {
        return count($arrayA) === count($arrayB)
            && count($arrayB) === count($arrayC)
            && count($arrayC) === count($arrayD)
            && count($arrayD) === count($arrayE);
    }
}
