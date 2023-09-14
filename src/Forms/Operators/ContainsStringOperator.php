<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class ContainsStringOperator extends Operator
{
    static public string $before = '%';
    static public string $after  = '%';

    static public function getKey(): string
    {
        return 'CS';
    }

    static public function getLabel(): string
    {
        return (string) __('Contains String (%a%)');
    }

    static public function prepareValue($value): string
    {
        return static::$before . str_replace('%', '%%', $value) . static::$after;
    }

    static public function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        return $query
            ->where($field, 'LIKE', static::prepareValue($values[0]));
    }

    static public function scopeToSQL($table, $field, array $values): string
    {
        return sprintf('`%1$s`.`%2$s` %3$s \'%4$s\'', $table, $field, 'LIKE', static::prepareValue($values[0]));
    }
}
