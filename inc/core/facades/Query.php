<?php

namespace Waboot\inc\core\facades;

use function Waboot\inc\core\helpers\Waboot;

class Query
{
    /**
     * @param string $table
     * @return \Illuminate\Database\Query\Builder
     * @throws \Waboot\inc\core\DBException
     */
    static function on(string $table): \Illuminate\Database\Query\Builder
    {
        return Waboot()->DB()->getQueryBuilder()::table($table);
    }
}
