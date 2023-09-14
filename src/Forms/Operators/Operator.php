<?php

namespace Thiktak\FilamentSQLNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

abstract class Operator
{
    abstract public static function getKey(): string;

    abstract public static function getLabel(): string;

    abstract public static function scopeToEloquent(Builder $query, $table, $field, array $values): Builder;

    abstract public static function scopeToSQL($table, $field, array $values): string;

    public static function getHint(): string
    {
        return '';
    }
}
