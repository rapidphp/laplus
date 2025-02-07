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

    public function passGenerator(MigrationGenerator $generator): void
    {
        $this->generator = $generator;

        $this->generate();

        unset($this->generator);
    }

    public function getGeneratingBlueprint(): Blueprint
    {
        return $this->generator->getBlueprintForTable($this->generatingTable);
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
        $old_attributes = $this->attributes;
        if (isset($this->generatingTable)) $old_table = $this->generatingTable;
        $this->attributes = [];
        $this->generatingTable = $table;

        $callback();

        foreach ($this->attributes as $attribute) {
            $attribute->generate($this);
        }

        foreach ($this->indexes as $index) {
            $index->generate($this);
        }

        // Revert old data
        $this->attributes = $old_attributes;
        if (isset($old_table)) $this->generatingTable = $old_table;
        else unset($this->generatingTable);
    }

}