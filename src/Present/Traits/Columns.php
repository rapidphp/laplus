<?php

namespace Rapid\Laplus\Present\Traits;

use Closure;
use Rapid\Laplus\Present\Attributes\Column;
use Rapid\Laplus\Present\Attributes\FileColumn;
use Rapid\Laplus\Present\Attributes\SlugColumn;

/**
 * @internal
 */
trait Columns
{

    /**
     * Create new id column
     *
     * @param string $column
     * @return Column
     */
    public function id(string $column = 'id')
    {
        return $this->column($column, 'id')->cast('int')->notFillable();
    }

    /**
     * Create new column
     *
     * @param string $column
     * @param string|Closure $method
     * @param                ...$args
     * @return Column
     */
    public function column(string $column, string|Closure $method, ...$args)
    {
        if (is_string($method)) {
            return $this->attribute(
                new Column($column, $method, [$column, ...$args]),
            );
        }

        return $this->attribute(
            new Column($column, $method, $args),
        );
    }

    public function increments(string $column)
    {
        return $this->column($column, 'increments')->cast('int');
    }

    public function integerIncrements(string $column)
    {
        return $this->column($column, 'integerIncrements')->cast('int');
    }

    public function tinyIncrements(string $column)
    {
        return $this->column($column, 'tinyIncrements')->cast('int');
    }

    public function smallIncrements(string $column)
    {
        return $this->column($column, 'smallIncrements')->cast('int');
    }

    public function mediumIncrements(string $column)
    {
        return $this->column($column, 'mediumIncrements')->cast('int');
    }

    public function bigIncrements(string $column)
    {
        return $this->column($column, 'bigIncrements')->cast('id');
    }

    public function char(string $column, $length = null)
    {
        return $this->column($column, 'char', $length);
    }

    public function tinyText(string $column)
    {
        return $this->column($column, 'tinyText');
    }

    public function text(string $column)
    {
        return $this->column($column, 'text');
    }

    public function mediumText(string $column)
    {
        return $this->column($column, 'mediumText');
    }

    public function longText(string $column)
    {
        return $this->column($column, 'longText');
    }

    public function integer(string $column, bool $autoIncrement = false, bool $unsigned = false)
    {
        return $this->column($column, 'integer', $autoIncrement, $unsigned)->cast('int');
    }

    public function tinyInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
    {
        return $this->column($column, 'tinyInteger', $autoIncrement, $unsigned)->cast('int');
    }

    public function smallInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
    {
        return $this->column($column, 'smallInteger', $autoIncrement, $unsigned)->cast('int');
    }

    public function mediumInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
    {
        return $this->column($column, 'mediumInteger', $autoIncrement, $unsigned)->cast('int');
    }

    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
    {
        return $this->column($column, 'bigInteger', $autoIncrement, $unsigned)->cast('int');
    }

    public function unsignedInteger(string $column, bool $autoIncrement = false)
    {
        return $this->column($column, 'unsignedInteger', $autoIncrement)->cast('int');
    }

    public function unsignedTinyInteger(string $column, bool $autoIncrement = false)
    {
        return $this->column($column, 'unsignedTinyInteger', $autoIncrement)->cast('int');
    }

    public function unsignedSmallInteger(string $column, bool $autoIncrement = false)
    {
        return $this->column($column, 'unsignedSmallInteger', $autoIncrement)->cast('int');
    }

    public function unsignedMediumInteger(string $column, bool $autoIncrement = false)
    {
        return $this->column($column, 'unsignedMediumInteger', $autoIncrement)->cast('int');
    }

    public function unsignedBigInteger(string $column, bool $autoIncrement = false)
    {
        return $this->column($column, 'unsignedBigInteger', $autoIncrement)->cast('int');
    }

    public function float(string $column, int $precision = 53)
    {
        return $this->column($column, 'float', $precision)->cast('double');
    }

    public function double(string $column)
    {
        return $this->column($column, 'double')->cast('double');
    }

    public function decimal(string $column, int $total = 8, int $places = 2)
    {
        return $this->column($column, 'decimal', $total, $places)->cast('double');
    }

    public function boolean(string $column)
    {
        return $this->column($column, 'boolean')->cast('boolean');
    }

    public function enum(string $column, array|string $allowed)
    {
        if (is_string($allowed)) {
            if (!is_a($allowed, \BackedEnum::class, true)) {
                throw new \TypeError("Expected BackedEnum, given [{$allowed}]");
            }

            return $this
                ->column($column, 'enum', array_map(fn($case) => $case->value, $allowed::cases()))
                ->cast($allowed);
        }

        return $this->column($column, 'enum', $allowed);
    }

    public function set(string $column, array $allowed)
    {
        return $this->column($column, 'set', $allowed);
    }

    public function json(string $column)
    {
        return $this->column($column, 'json')->cast('json');
    }

    public function jsonb(string $column)
    {
        return $this->column($column, 'jsonb')->cast('json');
    }

    public function jsonArray(string $column)
    {
        return $this->column($column, 'json')->cast('array');
    }

    public function date(string $column)
    {
        return $this->column($column, 'date')->cast('date');
    }

    public function dateTimeTz(string $column, int $precision = 0)
    {
        return $this->column($column, 'dateTimeTz', $precision)->cast('datetime');
    }

    public function time(string $column, int $precision = 0)
    {
        return $this->column($column, 'time', $precision)->cast('datetime');
    }

    public function timeTz(string $column, int $precision = 0)
    {
        return $this->column($column, 'timeTz', $precision)->cast('datetime');
    }

    public function timestamps(int $precision = 0)
    {
        $this->timestamp('created_at', $precision)->nullable()->notFillable();
        $this->timestamp('updated_at', $precision)->nullable()->notFillable();
    }

    public function timestamp(string $column, int $precision = 0)
    {
        return $this->column($column, 'timestamp', $precision)->cast('datetime');
    }

    public function timestampsTz(int $precision = 0)
    {
        $this->timestampTz('created_at', $precision)->nullable();

        $this->timestampTz('updated_at', $precision)->nullable();
    }

    public function timestampTz(string $column, int $precision = 0)
    {
        return $this->column($column, 'timestampTz', $precision)->cast('datetime');
    }

    public function datetimes(int $precision = 0)
    {
        $this->datetime('created_at', $precision)->nullable();

        $this->datetime('updated_at', $precision)->nullable();
    }

    public function dateTime(string $column, int $precision = 0)
    {
        return $this->column($column, 'dateTime', $precision)->cast('datetime');
    }

    public function softDeletes(string $column = 'deleted_at', int $precision = 0)
    {
        return $this->timestamp($column, $precision)->nullable();
    }

    public function softDeletesTz(string $column = 'deleted_at', int $precision = 0)
    {
        return $this->timestampTz($column, $precision)->nullable();
    }

    public function softDeletesDatetime(string $column = 'deleted_at', int $precision = 0)
    {
        return $this->datetime($column, $precision)->nullable();
    }

    public function year(string $column)
    {
        return $this->column($column, 'year')->cast('int');
    }

    public function binary(string $column, $length = null, bool $fixed = false)
    {
        return $this->column($column, 'binary', $length, $fixed);
    }

    public function uuid(string $column = 'uuid')
    {
        return $this->column($column, 'uuid');
    }

    public function ulid(string $column = 'ulid', $length = 26)
    {
        return $this->column($column, 'ulid', $length);
    }

    public function ipAddress($column = 'ip_address')
    {
        return $this->column($column, 'ipAddress');
    }

    public function macAddress(string $column = 'mac_address')
    {
        return $this->column($column, 'macAddress');
    }

    public function geometry(string $column, $subtype = null, $srid = 0)
    {
        return $this->column($column, 'geometry', $subtype, $srid);
    }

    public function geography(string $column, $subtype = null, $srid = 4326)
    {
        return $this->column($column, 'geography', $subtype, $srid);
    }

    public function computed(string $column, $expression)
    {
        return $this->column($column, 'computed', $expression);
    }

    public function rememberToken()
    {
        return $this->string('remember_token', 100)->nullable()->hidden();
    }

    public function string(string $column, $length = null)
    {
        return $this->column($column, 'string', $length);
    }

    public function password(string $column = 'password')
    {
        return $this->string($column)->cast('hashed')->hidden();
    }


    /**
     * Create new file column
     *
     * @param string $column
     * @return FileColumn
     */
    public function file(string $column)
    {
        return $this->attribute(new FileColumn($column));
    }

    /**
     * Create new slug column
     *
     * @param string $column
     * @param string $use
     * @return SlugColumn
     */
    public function slug(string $column, string $use = 'title')
    {
        return $this->attribute(new SlugColumn($column, 'text', [$column]))->use($use);
    }

}