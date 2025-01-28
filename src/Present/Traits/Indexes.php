<?php

namespace Rapid\Laplus\Present\Traits;

use Rapid\Laplus\Present\Attributes\Column;
use Rapid\Laplus\Present\Attributes\Index;

/**
 * @internal
 */
trait Indexes
{

    /**
     * Specify the primary key(s) for the table.
     *
     * @param string|array $columns
     * @param null $name
     * @param null $algorithm
     * @return Index
     */
    public function primary($columns, $name = null, $algorithm = null): Index
    {
        return $this->indexCommand('primary', $columns, $name, $algorithm);
    }

    /**
     * @param $type
     * @param $columns
     * @param $index
     * @param $algorithm
     * @return Index
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null): Index
    {
        $columns = $this->extractColumnNames($columns);

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
    protected function createIndexName($type, array $columns): string
    {
        $table = $this->instance->getTable();

        $index = strtolower($table . '_' . implode('_', $columns) . '_' . $type);

        return str_replace(['-', '.'], '_', $index);
    }

    /**
     * Extract column names
     *
     * @param $columns
     * @return array
     */
    protected function extractColumnNames($columns): array
    {
        if ($columns instanceof Column) {
            return [$columns->name];
        }

        if (is_array($columns)) {
            foreach ($columns as $key => $column) {
                if ($column instanceof Column) {
                    $columns[$key] = $column->name;
                }
            }
        }

        return (array)$columns;
    }

    /**
     * Specify a unique index for the table.
     *
     * @param string|array $columns
     * @param string|null $name
     * @param string|null $algorithm
     * @return Index
     */
    public function unique($columns, $name = null, $algorithm = null): Index
    {
        return $this->indexCommand('unique', $columns, $name, $algorithm);
    }

    /**
     * Specify an index for the table.
     *
     * @param string|array $columns
     * @param null $name
     * @param null $algorithm
     * @return Index
     */
    public function index($columns, $name = null, $algorithm = null): Index
    {
        return $this->indexCommand('index', $columns, $name, $algorithm);
    }

    /**
     * Specify a fulltext for the table.
     *
     * @param string|array $columns
     * @param string|null $name
     * @param string|null $algorithm
     * @return Index
     */
    public function fullText($columns, $name = null, $algorithm = null): Index
    {
        return $this->indexCommand('fulltext', $columns, $name, $algorithm);
    }

    /**
     * Specify a spatial index for the table.
     *
     * @param string|array $columns
     * @param string|null $name
     * @return Index
     */
    public function spatialIndex($columns, $name = null): Index
    {
        return $this->indexCommand('spatialIndex', $columns, $name);
    }

}