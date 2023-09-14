<?php

namespace Thiktak\FilamentSQLNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class BetweenOperator extends Operator
{
    public static function getKey(): string
    {
        return 'BW';
    }

    public static function getLabel(): string
    {
        return (string) __('Between (a, b)');
    }

    public static function getHint(): string
    {
        return (string) __('Separate the two values with a coma and a space (i.e.: 1, 2). More values will be ignored.');
    }

    public static function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        [$a, $b] = explode(', ', trim(($values[0] ?: '??, ??') . ', ??'));

        return $query
            ->whereBetween($field, [$a, $b]);
    }

    public static function scopeToSQL($table, $field, array $values): string
    {
        [$a, $b] = explode(', ', trim(($values[0] ?: '??, ??') . ', ??'));

        return sprintf('`%1$s`.`%2$s` %3$s \'%4$s\' AND \'%5$s\'', $table, $field, 'BETWEEN', $a, $b);
    }
}
