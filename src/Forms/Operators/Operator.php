<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

abstract class Operator
{

    abstract static public function getKey(): string;

    abstract static public function getLabel(): string;

    abstract static public function scopeToEloquent(Builder $query, $table, $field, array $values): Builder;

    abstract static public function scopeToSQL($table, $field, array $values): string;

    static public function getHint(): string
    {
        return '';
    }
}
