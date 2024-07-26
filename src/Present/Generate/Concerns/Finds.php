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

        $diff = [];
        foreach (array_unique([...array_keys($leftAttributes), ...array_keys($rightAttributes)]) as $attribute)
        {
            switch ($attribute)
            {
                case 'nullable':
                case 'autoIncrement':
                case 'unsigned':
                    if (($leftAttributes[$attribute] ?? false) == ($rightAttributes[$attribute] ?? false))
                    {
                        continue 2;
                    }
                    break;

                case 'unique':
                case 'oldNames':
                case 'change':
                case 'after':
                case 'first':
                    continue 2;

                default:
                    if (@$leftAttributes[$attribute] === @$rightAttributes[$attribute])
                    {
                        continue 2;
                    }
                    break;
            }

            if ($attribute == 'type')
            {
                return ['type'];
            }

            $diff[] = $attribute;
        }

        return $diff;
    }

}
