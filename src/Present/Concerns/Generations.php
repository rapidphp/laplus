<?php

namespace Rapid\Laplus\Present\Concerns;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Rapid\Laplus\Present\Generate\MigrationGenerator;

/**
 * @internal
 */
trait Generations
{

    public MigrationGenerator $generator;
    public string $generatingTable;
    public bool $isGenerating = false;

    public function passGenerator(MigrationGenerator $generator): void
    {
        $this->generator = $generator;

        $this->generate();

        unset($this->generator);
    }

    public function getGeneratingBlueprint(): Blueprint
    {
        return $this->generator->getBlueprintOrCreate($this->generatingTable);
    }

    /**
     * Generate table modifier
     *
     * @param string $table
     * @param Closure $callback
     * @return void
     */
    protected function table(string $table, Closure $callback): void
    {
        // Keep old data
        $oldAttributes = $this->attributes;
        $oldTravels = $this->travels;
        if (isset($this->generatingTable)) $oldTable = $this->generatingTable;

        try {
            $this->attributes = [];
            $this->travels = [];
            $this->generatingTable = $table;
            $this->isGenerating = true;

            $callback();

            foreach ($this->attributes as $attribute) {
                $attribute->generate($this);
            }

            foreach ($this->indexes as $index) {
                $index->generate($this);
            }
        } finally {
            // Revert old data
            $this->attributes = $oldAttributes;
            $this->travels = $oldTravels;
            $this->isGenerating = false;
            if (isset($oldTable)) $this->generatingTable = $oldTable;
            else unset($this->generatingTable);
        }
    }

}