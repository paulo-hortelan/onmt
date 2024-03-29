<?php

namespace PauloHortelan\Onmt\Services\Concerns;

use PauloHortelan\Onmt\Models\Olt;

trait Validations
{
    /**
     * Verify if OLT brand is valid with the calling service
     */
    public function oltValid(Olt $olt): bool
    {
        $callingService = get_class($this);
        $exploded = explode('\\', $callingService);
        $brand = $exploded[count($exploded) - 2];

        return $olt->brand === $brand;
    }
}
