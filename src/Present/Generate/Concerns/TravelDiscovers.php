<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Rapid\Laplus\Travel\Travel;

trait TravelDiscovers
{
    /**
     * The discovered travels
     *
     * @var Travel[]
     */
    public array $discoveredTravels = [];

    public function discoverTravels(array $travels): void
    {
        $this->discoveredTravels = array_replace($this->discoveredTravels, $travels);
    }

}