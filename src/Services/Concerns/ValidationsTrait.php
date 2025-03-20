<?php

namespace PauloHortelan\Onmt\Services\Concerns;

use Exception;

trait ValidationsTrait
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

    /**
     * Validate multiple IP addresses
     *
     * @param  string  ...$ips  One or more IP addresses to validate
     *
     * @throws Exception If any IP is invalid
     */
    public function validateIPs(string ...$ips): void
    {
        foreach ($ips as $ip) {
            if (! $this->isValidIP($ip)) {
                throw new Exception('Provided IP(s) are not valid(s).');
            }
        }
    }

    /**
     * Validate if a model is supported
     *
     * @param  string  $model  The model to validate
     * @param  array  $supportedModels  List of supported models
     *
     * @throws Exception If the model is not supported
     */
    public function validateModel(string $model, array $supportedModels): void
    {
        if (! in_array($model, $supportedModels)) {
            throw new Exception('Provided Model is not supported.');
        }
    }
}
