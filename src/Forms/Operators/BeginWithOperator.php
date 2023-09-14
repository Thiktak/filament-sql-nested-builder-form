<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

use Illuminate\Database\Eloquent\Builder;

class BeginWithOperator extends ContainsStringOperator
{
    static public string $before = '';
    static public string $after  = '%';

    static public function getKey(): string
    {
        return 'BS';
    }

    static public function getLabel(): string
    {
        return (string) __('Begin With (a%)');
    }
}
