<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

class EndWithOperator extends ContainsStringOperator
{
    public static string $before = '%';

    public static string $after = '';

    public static function getKey(): string
    {
        return 'ES';
    }

    public static function getLabel(): string
    {
        return (string) __('End With (a%)');
    }
}
