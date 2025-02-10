<?php

namespace Rapid\Laplus\Present\Generate\Testing;

use Closure;
use Rapid\Laplus\Present\Generate\MigrationExporter;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\Generate\SchemaTracker;

class GenerateTesting
{
    public function __construct(
        public MigrationGenerator $generator,
        public MigrationExporter  $exporter,
    )
    {
    }

    public function previous(Closure $callback)
    {
        $this->generator->resolveTableFromMigration($callback);

        return $this;
    }

    public function new(Closure|array $callback)
    {
        if (is_array($callback)) {
            $this->generator->pass($callback);
        } else {
            $this->generator->setOutlookState(
                SchemaTracker::track($callback)->state,
            );
        }

        return $this;
    }

    public function newModel(string $table, Closure $callback)
    {
        return $this->new([
            new AnonymousTestingPresentableModel($table, $callback),
        ]);
    }

    public function export(): ExportedTesting
    {
        $this->generator->generate();
        $files = $this->exporter->exportMigrationFiles([$this->generator]);

        return new ExportedTesting(
            $this->generator,
            $this->exporter,
            $files,
        );
    }
}