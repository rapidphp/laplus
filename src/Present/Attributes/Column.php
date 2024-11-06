<?php

namespace Rapid\Laplus\Present\Attributes;

use Rapid\Laplus\Present\Present;

class Column extends Attribute
{

    public function __construct(
        string          $name,
        protected       $createUsingMethod,
        protected array $createUsingArgs = [],
    )
    {
        parent::__construct($name);
        $this->fillable = true;
    }


    public function generate(Present $present)
    {
        parent::generate($present);

        $table = $present->getGeneratingBlueprint();

        if (is_string($this->createUsingMethod))
        {
            $column = $table->{$this->createUsingMethod}(...$this->createUsingArgs);
        }
        else
        {
            $column = ($this->createUsingMethod)($table, ...$this->createUsingArgs);
        }

        foreach ($this->columnData as $method => $arg)
        {
            $column->{$method}($arg);
        }

        if (isset($this->columnIndex))
        {
            $column->{$this->columnIndex[0]}($this->columnIndex[1]);
        }

        if ($this->oldNames)
        {
            $column->oldNames($this->oldNames);
        }
    }

    protected array $oldNames = [];

    /**
     * Set old column names.
     *
     * This option helps Laplus to find old column and rename it.
     *
     * @param string $name
     * @param string ...$names
     * @return $this
     */
    public function old(string $name, string ...$names)
    {
        array_push($this->oldNames, $name, ...$names);
        return $this;
    }


    protected array $columnData = [
        'nullable' => false,
    ];

    protected ?array $columnIndex = null;

    /**
     * Allow NULL values to be inserted into the column
     *
     * @param bool $value
     * @return $this
     */
    public function nullable(bool $value = true)
    {
        $this->columnData['nullable'] = $value;

        return $this;
    }

    /**
     * Place the column "after" another column (MySQL)
     *
     * @return $this
     */
    public function after(string $column)
    {
        $this->columnData['after'] = $column;

        return $this;
    }

    /**
     * Place the column "first" in the table (MySQL)
     *
     * @return $this
     */
    public function first()
    {
        $this->columnData['first'] = true;

        return $this;
    }

    /**
     * Used as a modifier for generatedAs() (PostgreSQL)
     *
     * @param bool $value
     * @return $this
     */
    public function always(bool $value = true)
    {
        $this->columnData['always'] = $value;

        return $this;
    }

    /**
     * Set INTEGER columns as auto-increment (primary key)
     *
     * @return $this
     */
    public function autoIncrement()
    {
        $this->columnData['autoIncrement'] = true;

        return $this;
    }

    /**
     * Specify a character set for the column (MySQL)
     *
     * @param string $charset
     * @return $this
     */
    public function charset(string $charset)
    {
        $this->columnData['charset'] = $charset;

        return $this;
    }

    /**
     * Specify a collation for the column
     *
     * @param string $collation
     * @return $this
     */
    public function collation(string $collation)
    {
        $this->columnData['collation'] = $collation;

        return $this;
    }

    /**
     * Add a comment to the column (MySQL/PostgreSQL)
     *
     * @param string $comment
     * @return $this
     */
    public function comment(string $comment)
    {
        $this->columnData['comment'] = $comment;

        return $this;
    }

    /**
     * Specify a "default" value for the column
     *
     * @param $value
     * @return $this
     */
    public function default($value)
    {
        $this->columnData['default'] = $value;

        return $this;
    }

    /**
     * Set the starting value of an auto-incrementing field (MySQL / PostgreSQL)
     *
     * @param int $startingValue
     * @return $this
     */
    public function from(int $startingValue)
    {
        return $this->startingValue($startingValue);
    }

    /**
     * Create a SQL compliant identity column (PostgreSQL)
     *
     * @param string|\Illuminate\Database\Query\Expression|null $expression
     * @return $this
     */
    // public function generatedAs(string|\Illuminate\Database\Query\Expression $expression = null)
    // {
    //     return $this->callBlueprint('generatedAs', $expression);
    // }

    /**
     * Add an index
     *
     * @param bool|string|null $indexName
     * @return $this
     */
    public function index(bool|string $indexName = null)
    {
        $this->columnIndex = ['index', $indexName];

        return $this;
    }

    /**
     * Specify that the column should be invisible to "SELECT *" (MySQL)
     *
     * @return $this
     */
    public function invisible()
    {
        $this->columnData['invisible'] = true;

        return $this;
    }

    /**
     * Mark the computed generated column as persistent (SQL Server)
     *
     * @return $this
     */
    public function persisted()
    {
        $this->columnData['persisted'] = true;

        return $this;
    }

    /**
     * Add a primary index
     *
     * @param bool $value
     * @return $this
     */
    public function primary(bool $value = true)
    {
        $this->columnIndex = ['primary', $value];

        return $this;
    }

    /**
     * Add a fulltext index
     *
     * @param bool|string|null $indexName
     * @return $this
     */
    public function fulltext(bool|string $indexName = null)
    {
        $this->columnIndex = ['fullText', $indexName];

        return $this;
    }

    /**
     * Add a spatial index
     *
     * @param bool|string|null $indexName
     * @return $this
     */
    public function spatialIndex(bool|string $indexName = null)
    {
        $this->columnIndex = ['spatialIndex', $indexName];

        return $this;
    }

    /**
     * Set the starting value of an auto-incrementing field (MySQL/PostgreSQL)
     *
     * @param int $startingValue
     * @return $this
     */
    public function startingValue(int $startingValue)
    {
        return $this->from($startingValue);
    }

    /**
     * Create a stored generated column (MySQL/PostgreSQL/SQLite)
     *
     * @param string $expression
     * @return $this
     */
    public function storedAs(string $expression)
    {
        $this->columnData['storedAs'] = $expression;

        return $this;
    }

    /**
     * Specify a type for the column
     *
     * @return $this
     */
    public function type(string $type)
    {
        $this->columnData['type'] = $type;

        return $this;
    }

    /**
     * Add a unique index
     *
     * @param bool|string|null $indexName
     * @return $this
     */
    public function unique(bool|string $indexName = null)
    {
        $this->columnIndex = ['unique', $indexName];

        return $this;
    }

    /**
     * Set the INTEGER column as UNSIGNED (MySQL)
     *
     * @return $this
     */
    public function unsigned()
    {
        $this->columnData['unsigned'] = true;

        return $this;
    }

    /**
     * Set the TIMESTAMP column to use CURRENT_TIMESTAMP as default value
     *
     * @return $this
     */
    public function useCurrent()
    {
        $this->columnData['useCurrent'] = true;

        return $this;
    }

    /**
     * Set the TIMESTAMP column to use CURRENT_TIMESTAMP when updating (MySQL)
     *
     * @return $this
     */
    public function useCurrentOnUpdate()
    {
        $this->columnData['useCurrentOnUpdate'] = true;

        return $this;
    }

    /**
     * Create a virtual generated column (MySQL/PostgreSQL/SQLite)
     *
     * @return $this
     */
    public function virtualAs(string $expression)
    {
        $this->columnData['virtualAs'] = $expression;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function docblock() : array
    {
        $doc = [];

        $doc[] = "@property {$this->getDocblockTypeHint()} \${$this->name}" . (isset($this->docHint) ? ' ' . $this->docHint : '');

        return $doc;
    }

    /**
     * Get docblock type hint
     *
     * @return string
     */
    public function getDocblockTypeHint() : string
    {
        if (isset($this->typeHint))
        {
            return $this->typeHint;
        }

        if ($this->castUsing)
        {
            return 'mixed';
        }

        if (is_string($this->cast))
        {
            return match ($this->cast)
            {
                'json'  => 'array',
                default => class_exists($this->cast) ? 'mixed' : $this->cast,
            };
        }

        if (str_ends_with($this->createUsingMethod, 'Integer'))
        {
            return 'int';
        }

        return match (strtolower($this->columnData['type'] ?? 'mixed'))
        {
            'string', 'varchar', 'char' => 'string',
            'json', 'array'             => 'array',
            default                     => match ($this->createUsingMethod)
            {
                'string', 'text'                     => 'string',
                'int', 'integer', 'decimal', 'float' => 'int',
                default                              => 'mixed',
            },
        };
    }

}