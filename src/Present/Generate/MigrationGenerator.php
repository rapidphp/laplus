<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Generate\Structure\ColumnListState;
use Rapid\Laplus\Present\Generate\Structure\DefinedMigrationState;
use Rapid\Laplus\Present\Generate\Structure\IndexListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileState;
use Rapid\Laplus\Present\Generate\Structure\MigrationListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationState;
use Rapid\Laplus\Present\Present;

class MigrationGenerator
{
    use Concerns\MigrationResolves,
        Concerns\MigrationGenerates,
        Concerns\SelectNames,
        Concerns\Finds;

    /**
     * @var bool
     */
    public bool $includeDropTables = true;

    /**
     * @var Blueprint[]
     */
    public array $tables = [];

    public function getTable(string $name)
    {
        return $this->tables[$name] ??= new Blueprint($name);
    }


    public function pass(array $models)
    {
        foreach ($models as $model)
        {
            /** @var Present $present */
            $present = $model::getStaticPresentInstance();

            $present->passGenerator($this);
        }
    }


    protected DefinedMigrationState $definedMigrationState;

}
