<?php

namespace Rapid\Laplus\Present\Generate;

use Illuminate\Database\Schema\Blueprint;
use Rapid\Laplus\Present\Generate\Structure\DatabaseState;
use Rapid\Laplus\Present\Present;

class MigrationGenerator
{
    use Concerns\MigrationResolves,
        Concerns\MigrationGenerates,
        Concerns\SelectNames,
        Concerns\Finds;

    /**
     * Include generate drop tables
     *
     * @var bool
     */
    public bool $includeDropTables = true;

    /**
     * List of blueprints
     *
     * @var Blueprint[]
     */
    public array $blueprints = [];

    /**
     * Get a blueprint or create a new
     *
     * @param string $name
     * @return Blueprint
     */
    public function getBlueprintForTable(string $name): Blueprint
    {
        return $this->blueprints[$name] ??= new Blueprint($name);
    }

    /**
     * Pass models
     *
     * @param array $models
     * @return void
     */
    public function pass(array $models): void
    {
        foreach ($models as $model) {
            /** @var Present $present */
            $present = $model::getStaticPresentInstance();

            $present->passGenerator($this);
        }
    }

}
