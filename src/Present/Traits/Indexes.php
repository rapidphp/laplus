<?php

namespace Rapid\Laplus\Present\Traits;

use Illuminate\Database\Query\Expression;
use Rapid\Laplus\Present\Attributes\Index;

/**
 * @internal
 */
trait Indexes
{

    /**
     * @param $type
     * @param $columns
     * @param $index
     * @param $algorithm
     * @return Index
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null) : Index
    {
        $columns = (array) $columns;

        $index = $index ?: $this->createIndexName($type, $columns);

        return $this->addIndex(new Index($index, $type, $columns, compact('algorithm')));
    }

    /**
     * Create index name with type and columns
     *
     * @param       $type
     * @param array $columns
     * @return string
     */
    protected function createIndexName($type, array $columns) : string
    {
        $table = $this->instance->getTable();

        $index = strtolower($table.'_'.implode('_', $columns).'_'.$type);

        return str_replace(['-', '.'], '_', $index);
    }

    /**
     * Specify a unique index for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $algorithm
     * @return Index
     */
    public function unique($columns, $name = null, $algorithm = null) : Index
    {
        return $this->indexCommand('unique', $columns, $name, $algorithm);
    }

    /**
     * Specify an index for the table.
     *
     * @param string|array $columns
     * @param null         $name
     * @param null         $algorithm
     * @return Index
     */
    public function index($columns, $name = null, $algorithm = null) : Index
    {
        return $this->indexCommand('index', $columns, $name, $algorithm);
    }

    /**
     * Specify a fulltext for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $algorithm
     * @return Index
     */
    public function fullText($columns, $name = null, $algorithm = null) : Index
    {
        return $this->indexCommand('fulltext', $columns, $name, $algorithm);
    }

    /**
     * Specify a spatial index for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @return Index
     */
    public function spatialIndex($columns, $name = null) : Index
    {
        return $this->indexCommand('spatialIndex', $columns, $name);
    }

}