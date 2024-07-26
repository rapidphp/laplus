<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\DefinedMigrationState;

trait Finds
{

    protected function findColumnOldName(string $tableName, Fluent $column)
    {
        foreach ($column->get('oldNames', []) as $name)
        {
            if (isset($this->definedState->get($tableName)->columns[$name]))
            {
                return $name;
            }
        }

        return null;
    }

    public function findColumnChanges(Fluent $left, Fluent $right)
    {
        $leftAttributes = $left->getAttributes();
        $rightAttributes = $right->getAttributes();

        $diff = array_filter(
            array_keys(
                array_diff_assoc($leftAttributes, $rightAttributes) + array_diff_assoc($rightAttributes, $leftAttributes),
            ),
            fn ($attribute) => match ($attribute)
            {
                'nullable', 'autoIncrement', 'unsigned' => ($leftAttributes[$attribute] ?? false) != ($rightAttributes[$attribute] ?? false),
                'oldNames', 'change', 'after', 'first'  => false, // Ignore this attributes
                default                                 => true,
            },
        );

        if (in_array('type', $diff))
        {
            return ['type'];
        }

        return $diff;
    }

}