<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class BetweenOperator extends Operator
{

    static public function getKey(): string
    {
        return 'BW';
    }

    static public function getLabel(): string
    {
        return (string) __('Between (a, b)');
    }

    static public function getHint(): string
    {
        return (string) __('Separate the two values with a coma and a space (i.e.: 1, 2). More values will be ignored.');
    }

    static public function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        list($a, $b) = explode(', ', trim(($values[0] ?: '??, ??') . ', ??'));
        return $query
            ->whereBetween($field, [$a, $b]);
    }

    static public function scopeToSQL($table, $field, array $values): string
    {
        list($a, $b) = explode(', ', trim(($values[0] ?: '??, ??') . ', ??'));
        return sprintf('`%1$s`.`%2$s` %3$s \'%4$s\' AND \'%5$s\'', $table, $field, 'BETWEEN', $a, $b);
    }
}
