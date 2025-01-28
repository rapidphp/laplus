<?php

namespace Rapid\Laplus\Present\Generate;

use Illuminate\Database\Schema\Blueprint;
use Rapid\Laplus\Present\Generate\Structure\DefinedMigrationState;
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
    public DefinedMigrationState $definedMigrationState;

    public function getTable(string $name)
    {
        return $this->tables[$name] ??= new Blueprint($name);
    }

    public function pass(array $models)
    {
        foreach ($models as $model) {
            /** @var Present $present */
            $present = $model::getStaticPresentInstance();

            $present->passGenerator($this);
        }
    }

}
