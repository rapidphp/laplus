<?php

namespace Rapid\Laplus\Present\Generate\Testing;

use Closure;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Present\Generate\MigrationExporter;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\Generate\SchemaTracker;
use Rapid\Laplus\Travel\Travel;

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

    public function previousTable(string $table, Closure $callback)
    {
        return $this->previous(function () use ($table, $callback) {
            Schema::create($table, $callback);
        });
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

    public function withTravel(string $name, Travel $travel)
    {
        $this->generator->discoverTravels([
            $name => $travel,
        ]);

        return $this;
    }

    public function export(): ExportedTesting
    {
        $files = $this->exporter->exportMigrationFiles([$this->generator]);

        return new ExportedTesting(
            $this->generator,
            $this->exporter,
            $files,
        );
    }
}