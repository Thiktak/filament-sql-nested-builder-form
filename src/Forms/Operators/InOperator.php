<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class InOperator extends Operator
{

    static public function getKey(): string
    {
        return 'IN';
    }

    static public function getLabel(): string
    {
        return (string) __('In list (a, b, ...)');
    }

    static public function getHint(): string
    {
        return (string) __('Separate the values with a coma and a space (i.e.: 1, 2)');
    }

    static public function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        $values = array_map('trim', explode(',', $values[0]));
        return $query
            ->whereIn($field, $values);
    }

    static public function scopeToSQL($table, $field, array $values): string
    {
        $values = array_map('trim', explode(',', $values[0]));
        return sprintf('`%1$s`.`%2$s` %3$s (\'%4$s\')', $table, $field, 'IN', implode('\', \'', $values));
    }
}
