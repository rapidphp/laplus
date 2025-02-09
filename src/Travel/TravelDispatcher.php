<?php

namespace Rapid\Laplus\Travel;

use Rapid\Laplus\Present\Generate\SchemaTracker;

class TravelDispatcher
{
    public static function dispatchUp(string $relativePath): void
    {
        if (($schema = app('db.schema')) instanceof SchemaTracker) {
            $schema->dispatchTravel($relativePath);
            return;
        }

        self::requireFile(base_path($relativePath))->up();
    }

    public static function dispatchDown(string $relativePath): void
    {
        if (app('db.schema') instanceof SchemaTracker) {
            return;
        }

        self::requireFile(base_path($relativePath))->down();
    }

    protected static function requireFile(string $__path): Travel
    {
        return require $__path;
    }
}
