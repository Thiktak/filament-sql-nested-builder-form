<?php

namespace Thiktak\FilamentSQLNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class ContainsStringOperator extends Operator
{
    public static string $before = '%';

    public static string $after = '%';

    public static function getKey(): string
    {
        return 'CS';
    }

    public static function getLabel(): string
    {
        return (string) __('Contains String (%a%)');
    }

    public static function prepareValue($value): string
    {
        return static::$before . str_replace('%', '%%', $value) . static::$after;
    }

    public static function scopeToEloquent(Builder $query, $table, $field, array $values): Builder
    {
        return $query
            ->where($field, 'LIKE', static::prepareValue($values[0]));
    }

    public static function scopeToSQL($table, $field, array $values): string
    {
        return sprintf('`%1$s`.`%2$s` %3$s \'%4$s\'', $table, $field, 'LIKE', static::prepareValue($values[0]));
    }
}
