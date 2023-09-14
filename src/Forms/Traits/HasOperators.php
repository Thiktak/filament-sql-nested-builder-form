<?php

namespace Thiktak\FilamentSQLNestedBuilderForm\Forms\Traits;

use Thiktak\FilamentNestedBuilderForm\Forms\Operators\Operator;

trait HasOperators
{
    public array $operators = [];

    public function registerOperator(string $operator): static
    {
        if (is_subclass_of($operator, Operator::class)) {
            $this->operators[$operator::getKey()] = $operator;
        }

        return $this;
    }

    public function getOperators()
    {
        return $this->operators;
    }

    public function getOperator($key)
    {
        return $this->operators[$key] ?? collect();
    }

    public function hasOperator($key)
    {
        return isset($this->operators[$key]);
    }

    public function getOperatorLabels()
    {
        return collect($this->operators)
            ->mapWithKeys(function ($op, $key) {
                return [$key => $op::getLabel()];
            })
            ->toArray();
    }

    public function getOperatorEloquents()
    {
        return collect($this->operators)->pluck('eloquent', 'key')->toArray();
    }
}
