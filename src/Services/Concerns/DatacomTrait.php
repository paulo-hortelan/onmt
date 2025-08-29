<?php

namespace PauloHortelan\Onmt\Services\Concerns;

trait DatacomTrait
{
    /**
     * Extracts the ONT index from an interface string.
     * Example: From '1/1/1/2' returns '2'
     *
     * @param  string  $interface  The full interface identifier (e.g., '1/1/1/2')
     * @return string The ONT index
     *
     * @throws \Exception When the interface format is invalid
     */
    public function getOntIndexFromInterface(string $interface): string
    {
        if (! preg_match('/^\d+\/\d+\/\d+\/(\d+)$/', $interface, $matches)) {
            throw new \Exception('Invalid interface format. Expected format: shelf/slot/port/ontId');
        }

        return $matches[1];
    }

    /**
     * Extracts the PON interface from an interface string.
     * Example: From '1/1/1/2' returns '1/1/1'
     *
     * @param  string  $interface  The full interface identifier (e.g., '1/1/1/2')
     * @return string The PON interface
     *
     * @throws \Exception When the interface format is invalid
     */
    public function getPonInterfaceFromInterface(string $interface): string
    {
        if (! preg_match('/^(\d+\/\d+\/\d+)\/\d+$/', $interface, $matches)) {
            throw new \Exception('Invalid interface format. Expected format: shelf/slot/port/ontId');
        }

        return $matches[1];
    }

    /**
     * Converts a serial string to a case-insensitive regex pattern.
     * For example, "ABC123" becomes "[Aa][Bb][Cc]123"
     *
     * Only alphabetic characters are transformed, digits and special characters remain unchanged.
     *
     * @param  string  $serial  The serial number to convert
     * @return string The case-insensitive regex pattern
     */
    public function applyIgnoreCase(string $serial): string
    {
        return preg_replace_callback('/[a-zA-Z]/', function ($matches) {
            $char = $matches[0];

            return '['.strtoupper($char).strtolower($char).']';
        }, $serial);
    }
}
