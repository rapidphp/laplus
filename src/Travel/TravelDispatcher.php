<?php

namespace Rapid\Laplus\Travel;

use Rapid\Laplus\Present\Generate\SchemaTracker;

class TravelDispatcher
{
    public function dispatchUp(string $relativePath): void
    {
        if (($schema = app('db.schema')) instanceof SchemaTracker) {
            $schema->dispatchTravel($relativePath);
            return;
        }

        $this->requireFile(base_path($relativePath))->up();
    }

    public function dispatchDown(string $relativePath): void
    {
        if (app('db.schema') instanceof SchemaTracker) {
            return;
        }

        $this->requireFile(base_path($relativePath))->down();
    }

    protected function requireFile(string $__path): Travel
    {
        return require $__path;
    }
}