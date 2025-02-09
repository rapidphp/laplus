<?php

namespace Rapid\Laplus\Travel;

use Rapid\Laplus\Present\Generate\SchemaTracker;

class TravelDispatcher
{
    public function dispatch(string $relativePath): void
    {
        if (($schema = app('db.schema')) instanceof SchemaTracker) {
            $schema->dispatchTravel($relativePath);
            return;
        }

        $this->requireFile(base_path($relativePath))->fly();
    }

    protected function requireFile(string $__path): Travel
    {
        return require $__path;
    }
}