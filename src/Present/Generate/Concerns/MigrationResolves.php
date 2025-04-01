<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Closure;
use Rapid\Laplus\Present\Generate\SchemaTracker;
use Rapid\Laplus\Present\Generate\Structure\DatabaseState;

trait MigrationResolves
{

    /**
     * The resolved database state
     *
     * @var DatabaseState
     */
    public DatabaseState $resolvedState;

    /**
     * The resolved travel names
     *
     * @var string[]
     */
    public array $resolvedTravels;

    /**
     * Resolve current table status from migrations
     *
     * @param Closure $callback
     * @return void
     */
    public function resolveTableFromMigration(Closure $callback): void
    {
        $this->resolvedState ??= new DatabaseState();

        $schema = SchemaTracker::track($callback, $this->resolvedState);

        $this->resolvedTravels = $schema->travels;
    }
}