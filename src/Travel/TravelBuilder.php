<?php

namespace Rapid\Laplus\Travel;

use Rapid\Laplus\Travel\Constraints\AddedConstraint;
use Rapid\Laplus\Travel\Constraints\Constraint;
use Rapid\Laplus\Travel\Constraints\RemovedConstraint;
use Rapid\Laplus\Travel\Constraints\RemovingConstraint;

class TravelBuilder
{
    /** @var Constraint[] */
    public array $constraints;

    public function __construct(
        public string $name,
        Constraint    $constraint,
    )
    {
        $this->constraints = [$constraint];
    }

    public function and(Constraint $constraint): static
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    public function andRemoving(string|array $columns): static
    {
        return $this->and(new RemovingConstraint());
    }

    public function andRemoved(string|array $columns): static
    {
        return $this->and(new RemovedConstraint());
    }

    public function andAdded(string|array $columns): static
    {
        return $this->and(new AddedConstraint());
    }
}