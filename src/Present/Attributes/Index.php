<?php

namespace Rapid\Laplus\Present\Attributes;

use Rapid\Laplus\Present\Present;

class Index
{

    public function __construct(
        public string $name,
        public string $type,
        public array $columns,
        public array $indexData = [],
    )
    {
    }

    /**
     * Set the algorithm
     *
     * @param $algorithm
     * @return $this
     */
    public function algorithm($algorithm)
    {
        $this->indexData['algorithm'] = $algorithm;
        return $this;
    }

    /**
     * Generate database structure
     *
     * @param Present $present
     * @return void
     */
    public function generate(Present $present)
    {
        $table = $present->getGeneratingBlueprint();

        $index = $table->{$this->type}($this->columns, $this->name);

        foreach ($this->indexData as $method => $arg)
        {
            $index->{$method}($arg);
        }

        // if ($this->oldNames)
        // {
        //     $column->oldNames($this->oldNames);
        // }
    }

}