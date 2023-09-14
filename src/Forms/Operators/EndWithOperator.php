<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class EndWithOperator extends ContainsStringOperator
{
    static public string $before = '%';
    static public string $after  = '';

    static public function getKey(): string
    {
        return 'ES';
    }

    static public function getLabel(): string
    {
        return (string) __('End With (a%)');
    }
}
