<?php

namespace Rapid\Laplus\Present\Attributes;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Validation\Rules\Unique;

class Column extends Attribute
{

    protected array $oldNames = [];
    protected array $columnData = [
        'nullable' => false,
    ];
    protected ?array $columnIndex = null;
    protected ?Closure $rules = null;
    protected ?Closure $dataTypeRules = null;

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

        if (is_string($this->createUsingMethod)) {
            $column = $table->{$this->createUsingMethod}(...$this->createUsingArgs);
        } else {
            $column = ($this->createUsingMethod)($table, ...$this->createUsingArgs);
        }

        foreach ($this->columnData as $method => $arg) {
            $column->{$method}($arg);
        }

        if (isset($this->columnIndex)) {
            $column->{$this->columnIndex[0]}($this->columnIndex[1]);
        }

        if ($this->oldNames) {
            $column->oldNames($this->oldNames);
        }
    }

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
     * Set the starting value of an auto-incrementing field (MySQL / PostgreSQL)
     *
     * @param int $startingValue
     * @return $this
     */
    public function startingValue(int $startingValue)
    {
        $this->columnData['startingValue'] = $startingValue;

        return $this;
    }

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
    public function docblock(GuideScope $scope): array
    {
        $doc = [];

        $typeHint = $scope->typeHint($this->getDocblockTypeHint());
        $doc[] = "@property {$typeHint} \${$this->name}" . (isset($this->docHint) ? ' ' . $this->docHint : '');

        return $doc;
    }

    /**
     * Get docblock type hint
     *
     * @return string
     */
    public function getDocblockTypeHint(): string
    {
        if (isset($this->typeHint)) {
            return $this->typeHint;
        }

        if ($this->castUsing) {
            return $this->getDefaultDocblockTypeHint();
        }

        $prefix = $this->columnData['nullable'] ? 'null|' : '';

        if (is_string($this->cast)) {
            return $prefix . match ($this->cast) {
                    'json' => 'array',
                    'timestamp', 'int' => 'int',
                    'date', 'dateTime', 'datetime', 'time' => '\\' . Carbon::class,
                    'hashed' => 'string',
                    default => match (true) {
                        !class_exists($this->cast) => $this->cast,
                        is_a($this->cast, \BackedEnum::class, true) => $this->cast,
                        default => $this->getDefaultDocblockTypeHint(),
                    }
                };
        }

        if (str_ends_with($this->createUsingMethod, 'Integer')) {
            return $prefix . 'int';
        }

        return $prefix . match (strtolower($this->columnData['type'] ?? '')) {
                'string', 'varchar', 'char' => 'string',
                'json', 'array' => 'array',
                default => match ($this->createUsingMethod) {
                    'string', 'text' => 'string',
                    'int', 'integer', 'decimal', 'float' => 'int',
                    'boolean' => 'bool',
                    default => $this->getDefaultDocblockTypeHint(),
                },
            };
    }

    protected function getDefaultDocblockTypeHint(): string
    {
        return 'mixed';
    }

    /**
     * Set the column rules
     *
     * @param array|Closure $rules
     * @return $this
     */
    public function rules(array|Closure $rules)
    {
        if (is_array($rules)) {
            $rules = static fn() => $rules;
        }

        $this->rules = $rules;
        return $this;
    }

    /**
     * Set the column data type rules
     *
     * @param array|Closure $rules
     * @return $this
     */
    public function dataTypeRules(array|Closure $rules)
    {
        if (is_array($rules)) {
            $rules = static fn() => $rules;
        }

        $this->dataTypeRules = $rules;
        return $this;
    }

    public function getRules(): ?array
    {
        if (!$this->fillable) {
            return null;
        }

        if (isset($this->rules)) {
            return app()->call($this->rules);
        }

        return [
            ...$this->defaultRules(),
            ...isset($this->dataTypeRules) ?
                app()->call($this->dataTypeRules) :
                $this->defaultDataTypeRules(),
        ];
    }

    protected function defaultRules(): array
    {
        return array_filter([
            $this->columnData['nullable'] ? 'nullable' : 'required',
            @$this->columnIndex[0] == 'unique' ? Unique::class : null,
        ]);
    }

    protected function defaultDataTypeRules(): array
    {
        switch ($this->createUsingMethod) {
            case 'string':
                $max = ($this->createUsingArgs[1] ?? 255);
                break;

            case 'integer':
                $max = '2147483647';
                $min = '-2147483648';
                break;

            case 'unsignedInteger':
                $max = '4294967295';
                $min = '0';
                break;

            case 'tinyInteger':
                $max = 127;
                $min = -128;
                break;

            case 'unsignedTinyInteger':
                $max = 255;
                $min = 0;
                break;

            case 'smallInteger':
                $max = 32767;
                $min = -32768;
                break;

            case 'unsignedSmallInteger':
                $max = 65535;
                $min = 0;
                break;

            case 'mediumInteger':
                $max = -8388608;
                $min = 8388607;
                break;

            case 'unsignedMediumInteger':
                $max = 16777215;
                $min = 0;
                break;

            case 'bigInteger':
                $max = '9223372036854775807';
                $min = '-9223372036854775808';
                break;

            case 'unsignedBigInteger':
                $max = '18446744073709551615';
                $min = 0;
                break;
        }

        return array_filter([
            ...match ($this->cast) {
                'string', 'int', 'integer' => [$this->cast],
                'double', 'float' => ['numeric'],
                'boolean', 'bool' => ['boolean'],
                'json', 'array' => ['array'],
                'date' => ['date', 'date_format:Y-m-d'],
                'datetime', 'timestamp' => ['date', 'date_format:Y-m-d H:i:s'],
                default => ['string'],
            },
            ...$this->createUsingMethod == 'decimal' ? [
                'digits:' . $this->createUsingArgs[1] - $this->createUsingArgs[2],
                'decimal:' . $this->createUsingArgs[2],
            ] : [],
            is_string($this->cast) && is_a($this->cast, \BackedEnum::class, true) ?
                Rule::in(Arr::pluck($this->cast::cases(), 'value')) :
                null,
            isset($min) ? 'min:' . $min : null,
            isset($max) ? 'max:' . $max : null,
        ]);
    }
}