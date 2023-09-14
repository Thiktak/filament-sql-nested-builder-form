<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class EqualOperator extends Operator
{

    static public function getKey(): string
    {
        return 'EQ';
    }

    static public function getLabel(): string
    {
        return (string) __('Equals to');
    }

    static public function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        return $query
            ->where($field, '=', $values[0]);
    }

    static public function scopeToSQL($table, $field, array $values): string
    {
        return sprintf('`%1$s`.`%2$s` %3$s \'%4$s\'', $table, $field, '=', $values[0] ?? '');
    }
}
