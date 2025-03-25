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
}
