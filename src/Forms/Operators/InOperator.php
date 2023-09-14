<?php

namespace Thiktak\FilamentSQLNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class InOperator extends Operator
{
    public static function getKey(): string
    {
        return 'IN';
    }

    public static function getLabel(): string
    {
        return (string) __('In list (a, b, ...)');
    }

    public static function getHint(): string
    {
        return (string) __('Separate the values with a coma and a space (i.e.: 1, 2)');
    }

    public static function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        $values = array_map('trim', explode(',', $values[0]));

        return $query
            ->whereIn($field, $values);
    }

    public static function scopeToSQL($table, $field, array $values): string
    {
        $values = array_map('trim', explode(',', $values[0]));

        return sprintf('`%1$s`.`%2$s` %3$s (\'%4$s\')', $table, $field, 'IN', implode('\', \'', $values));
    }
}
