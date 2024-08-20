<?php

namespace PauloHortelan\Onmt\Services\Concerns;

use Exception;

trait Assertations
{
    /**
     * Verify if both arrays have the same lengths
     */
    public function assertSameLength($arrayOfArrays): bool
    {
        if (empty($arrayOfArrays)) {
            return true;
        }

        for ($i = 0; $i < count($arrayOfArrays) - 1; $i++) {
            if (count($arrayOfArrays[$i]) !== count($arrayOfArrays[$i + 1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates that all required parameters are set and non-empty.
     *
     * @param  array  $parameters  An associative array of parameters to check.
     *
     * @throws Exception If any required parameter is missing or empty.
     */
    public function validateParameters(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
                throw new Exception("Missing or empty parameter: $key.");
            }
        }
    }

    private static function formatArrayToString(array $array): string
    {
        $formatted = array_map(
            function ($key, $value) {
                return "$key=\"$value\"";
            },
            array_keys($array),
            $array
        );

        if (empty($formatted)) {
            return '';
        }

        return implode(',', $formatted);
    }

    public static function formatCommandEntOnt(array $tid, string $aidOnt, array $ctag, array $ontNblk)
    {
        $tidCommand = self::formatArrayToString($tid);
        $ctagCommand = self::formatArrayToString($ctag);
        $ontNblkCommand = self::formatArrayToString($ontNblk);

        return "ENT-ONT:$tidCommand:$aidOnt:$ctagCommand:::$ontNblkCommand";
    }
}
