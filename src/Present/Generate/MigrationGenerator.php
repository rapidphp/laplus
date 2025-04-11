<?php

namespace Rapid\Laplus\Present\Generate;

use Illuminate\Database\Schema\Blueprint;
use Rapid\Laplus\Present\Generate\Testing\GenerateTesting;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\VersionStabler;

class MigrationGenerator
{
    use Concerns\MigrationResolves,
        Concerns\MigrationGenerates,
        Concerns\TravelDiscovers,
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
    public function getBlueprintOrCreate(string $name): Blueprint
    {
        return $this->blueprints[$name] ??= VersionStabler::newBlueprint($name);
    }

    /**
     * Get a blueprint
     *
     * @param string $name
     * @return ?Blueprint
     */
    public function getBlueprintOrNull(string $name): ?Blueprint
    {
        return @$this->blueprints[$name];
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
            if (is_string($model)) {
                $present = $model::getStaticPresentInstance();
            } else {
                $present = $model->getPresent();
            }

            /** @var Present $present */
            $present->passGenerator($this);
        }
    }

    public static function test(): GenerateTesting
    {
        return new GenerateTesting(
            new MigrationGenerator(),
            new MigrationExporter(),
        );
    }

}
