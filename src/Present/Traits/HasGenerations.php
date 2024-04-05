<?php

namespace Rapid\Laplus\Present\Traits;

use Closure;
use Rapid\Laplus\Present\Generate\MigrationGenerator;

trait HasGenerations
{

    public MigrationGenerator $generator;

    public function passGenerator(MigrationGenerator $generator)
    {
        $this->generator = $generator;

        $this->generate();

        unset($this->generator);
    }


    public string $generatingTable;

    /**
     * Generate table modifier
     *
     * @param string  $table
     * @param Closure $callback
     * @return void
     */
    protected function table(string $table, Closure $callback)
    {
        // Keep old data
        $old_attributes = $this->attributes;
        if (isset($this->generatingTable)) $old_table = $this->generatingTable;
        $this->attributes = [];
        $this->generatingTable = $table;

        $callback();

        foreach ($this->attributes as $attribute)
        {
            $attribute->generate($this);
        }

        // Revert old data
        $this->attributes = $old_attributes;
        if (isset($old_table)) $this->generatingTable = $old_table;
        else unset($this->generatingTable);
    }

    public function getGeneratingBlueprint()
    {
        return $this->generator->getTable($this->generatingTable);
    }

}