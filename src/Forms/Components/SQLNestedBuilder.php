<?php

namespace Thiktak\FilamentSQLNestedBuilderForm\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedBuilder;
use Thiktak\FilamentNestedBuilderForm\Forms\Components\NestedSubBuilder;
use Thiktak\FilamentSQLNestedBuilderForm\Forms\Operators;
use Thiktak\FilamentSQLNestedBuilderForm\Forms\Traits\HasOperators;

class SQLNestedBuilder extends NestedBuilder
{
    use HasOperators;

    public array | Arrayable | string | Closure | null $signOptions = null;

    public Field | Closure | null $fieldComponent = null;

    public static function make(string $name): static
    {
        return parent::make($name)
            ->loadDefinition();
    }

    public function signOptions(array | Arrayable | string | Closure | null $signOptions): static
    {
        $this->signOptions = $signOptions;

        return $this;
    }

    public function getSignOptions(NestedSubBuilder $builder): array
    {
        return (array) $this->evaluate($this->signOptions, [
            'builder' => $builder,
        ]);
    }

    public function fieldComponent(Field | Closure | null $fieldComponent): static
    {
        $this->fieldComponent = $fieldComponent;

        return $this;
    }

    public function getFieldComponent(NestedSubBuilder $builder): mixed
    {
        return $this->evaluate($this->fieldComponent, [
            'builder' => $builder,
        ]);
    }

    public function loadDefinition(): static
    {
        // Registier basic configuration
        $this->registerOperator(Operators\EqualOperator::class);
        $this->registerOperator(Operators\NotEqualOperator::class);
        $this->registerOperator(Operators\InOperator::class);
        $this->registerOperator(Operators\ContainsStringOperator::class);
        $this->registerOperator(Operators\BeginWithOperator::class);
        $this->registerOperator(Operators\EndWithOperator::class);
        $this->registerOperator(Operators\BetweenOperator::class);

        $this->signOptions(fn () => $this->getOperatorLabels());

        $this->fieldComponent(fn () => TextInput::make('field'));

        // Return default configuration
        return $this
            ->nestedConfiguration(
                fn (NestedSubBuilder $builder, NestedBuilder $parent) => $this->defaultNestedConfiguration($builder)
            )
            ->nestedSchema(
                fn (NestedSubBuilder $builder, NestedBuilder $parent) => $this->defaultNestedSchema($builder)
            );
    }

    public function defaultNestedConfiguration(NestedSubBuilder $builder)
    {
        $builder->blockNumbers(false); //$builder->getLevel() != 1);
        $builder->columnSpanFull(); // full width
        $builder->label('Query (WHERE)');
        $builder->hint(function (?array $state) {
            return self::getFullyLinearizedArrayToSQL($state) ?: 'No filter yet';
        });
        $builder->addActionLabel('Add a new WHERE');
        $builder->reorderableWithButtons();
        $builder->collapsible();

        // Callapse only the children RULEs
        $builder->collapsed(function ($item) {
            try {
                return count(collect($item->getState())->get('children') ?: []) <= 0;
            } catch (\Exception $e) {
                return false;
            }
        });

        // Hide "collapse all" and "expand all"
        $builder->collapseAllAction(fn (Action $action) => $action->hidden(true)->visible(false)->extraAttributes(['style' => 'display: none']));
        $builder->expandAllAction(fn (Action $action) => $action->hidden(true)->visible(false)->extraAttributes(['style' => 'display: none']));

        return $builder;
    }

    public function defaultNestedSchema(NestedSubBuilder $builder): array
    {
        return [
            Block::make('group')
                /*->label(function (?array $state) use ($builder) {
                    return trim(sprintf('Group (%s) : %s', $builder->getLevel(), $this->getLinearizedArrayToSQL('group', $state)), ' :');
                })*/
                ->schema([
                    Grid::make(12)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('condition')
                                        ->options([
                                            'AND' => 'AND',
                                            'OR' => 'OR',
                                        ])
                                        ->default('and')
                                        ->required()
                                        ->live()
                                        ->columnSpanFull(),

                                    Checkbox::make('not')
                                        ->label('not')
                                        ->live()
                                        ->columnSpanFull(),
                                ])
                                ->columnSpan(2),

                            $builder->importNestedBlocks('children')
                                ->label(false)
                                ->columnSpan(10),
                        ])
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Block::make('rule')
                ->label(function (?array $state) use ($builder) {
                    return trim(sprintf('Rule (%s) : %s', $builder->getLevel(), $this->getLinearizedArrayToSQL('rule', $state)), ' :');
                })
                ->schema([
                    $this->getFieldComponent($builder)
                        ->required()
                        ->columnSpan(2)
                        ->live(),

                    Select::make('operator')
                        ->options($this->getSignOptions($builder))
                        ->default('=')
                        ->required()
                        ->live(),

                    TextInput::make('value')
                        ->columnSpan(3)
                        ->hintIcon(function ($get) {
                            $sign = $get('operator');
                            if ($this->hasOperator($sign)) {
                                return ! empty($this->getOperator($sign)::getHint()) ? 'heroicon-m-question-mark-circle' : null;
                            }
                        }, function ($get) {
                            $sign = $get('operator');
                            if ($this->hasOperator($sign)) {
                                return $this->getOperator($sign)::getHint();
                            }
                        })
                        ->live(),
                ])
                ->columns(6),
        ];
    }

    public function getMappingRuleOperatorToSQL($operator = null, $table = null, $field = null, $value = null): string
    {
        if ($this->hasOperator($operator)) {
            return $this->getOperator($operator)::scopeToSQL($table, $field, [$value]);
        }

        return '';
    }

    public function getLinearizedArrayToSQL($type, ?array $array)
    {
        $sql = [];
        switch ($type) {

            case 'sql':
                foreach ((array) $array as $child) {
                    $sql[] = $this->getLinearizedArrayToSQL($child['type'], $child['data']);
                }

                return implode(' AND ', $sql);

                break;

            case 'group':

                $not = $array['not'] ?? false;

                if (! isset($array['children'])) {
                    return;
                }

                $children = (array) ($array['children'] ?? []);
                foreach ($children as $key => $child) {
                    $sql[] = $this->getLinearizedArrayToSQL($child['type'], $child['data']);
                }
                $condition = ($array['condition'] ?? '') ?: '??';

                return ($not ? 'NOT ' : '') . '(' . implode(' ' . $condition . ' ', $sql) . ')';

                break;

            case 'rule':
                if (! isset($array['field'])) {
                    return null;
                }

                $child = array_merge(
                    ['field' => '?', 'operator' => '?', 'value' => '?'],
                    (array) $array
                );

                return trim(str_replace('``', '', $this->getMappingRuleOperatorToSQL(
                    table: '',
                    field: $child['field'],
                    operator: $child['operator'],
                    value: $child['value']
                )), ' .');

                break;
        }

        return null;
    }

    public static function getFullyLinearizedArrayToSQL(?array $state): string
    {
        return static::make('')->getLinearizedArrayToSQL('sql', $state);
    }

    public function getLinearizedArrayToEloquent(?array $state, EloquentBuilder $queryBuilder, $operator = 'and'): EloquentBuilder
    {

        foreach ($state as $child) {

            $isNot = $child['data']['not'] ?? false;
            $func = (strtolower($operator) == 'and' ? '' : $operator) . ucfirst('where') . ($isNot ? ucfirst('not') : '');

            switch ($child['type']) {
                case 'group':
                    $children = (array) $child['data']['children'] ?? [];
                    $queryBuilder->$func(
                        fn ($query) => $this
                            ->getLinearizedArrayToEloquent($children, $query, $child['data']['condition'])
                    );

                    break;

                case 'rule':
                    if ($this->hasOperator($child['data']['operator'])) {
                        $queryBuilder->$func(
                            fn ($query) => $this->getOperator($child['data']['operator'])::scopeToEloquent(
                                $query,
                                table: null,
                                field: $child['data']['field'],
                                values: [$child['data']['value']]
                            )
                        );
                    }

                    break;
            }
        }

        return $queryBuilder;
    }

    public static function getFullyLinearizedArrayToEloquent(?array $state, EloquentBuilder $queryBuilder): EloquentBuilder
    {
        return $queryBuilder
            ->where(
                fn ($query) => (static::make(''))->getLinearizedArrayToEloquent($state, $query)
            );
    }
}
