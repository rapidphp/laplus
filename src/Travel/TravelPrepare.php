<?php

namespace Rapid\Laplus\Travel;

use Rapid\Laplus\Travel\Constraints\AddedConstraint;
use Rapid\Laplus\Travel\Constraints\RemovedConstraint;
use Rapid\Laplus\Travel\Constraints\RemovingConstraint;

class TravelPrepare
{
    public function __construct(
        public string $name,
    )
    {
    }

    public function whenRemoving(string|array $columns): TravelBuilder
    {
        return new TravelBuilder($this->name, new RemovingConstraint());
    }

    public function whenRemoved(string|array $columns): TravelBuilder
    {
        return new TravelBuilder($this->name, new RemovedConstraint());
    }

    public function whenAdded(string|array $columns): TravelBuilder
    {
        return new TravelBuilder($this->name, new AddedConstraint());
    }
}