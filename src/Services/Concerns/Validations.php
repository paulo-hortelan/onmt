<?php

namespace PauloHortelan\Onmt\Services\Concerns;

trait Validations
{
    /**
     * Verify if string is a valid IP
     */
    public function isValidIP(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        return false;
    }
}
