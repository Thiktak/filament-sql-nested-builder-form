<?php

namespace Thiktak\FilamentNestedBuilderForm\Forms\Operators;

class BeginWithOperator extends ContainsStringOperator
{
    public static string $before = '';

    public static string $after = '%';

    public static function getKey(): string
    {
        return 'BS';
    }

    public static function getLabel(): string
    {
        return (string) __('Begin With (a%)');
    }
}
