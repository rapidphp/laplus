<?php

namespace Rapid\Laplus;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Facades\DB;

final class VersionStabler
{
    public static function newBlueprint(string $table, ?\Closure $callback = null): Blueprint
    {
        // On laravel version 12.x
        if ((new \ReflectionClass(Blueprint::class))->getConstructor()->getParameters()[0]->name === 'connection') {
            $connection = DB::connection();

            if ($connection->getSchemaGrammar() === null) {
                $connection->setSchemaGrammar(new SQLiteGrammar($connection));
            }

            return new Blueprint(DB::connection(), $table, $callback);
        }

        // On laravel version 11.x
        return new Blueprint($table, $callback);
    }
}